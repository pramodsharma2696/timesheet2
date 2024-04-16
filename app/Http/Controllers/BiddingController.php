<?php

namespace App\Http\Controllers;

use App\Models\Bidding;
use Illuminate\Http\Request;
use App\Http\Requests\BiddingRequest;
use App\Http\Resources\BiddingTenderResource;
use App\Mail\BiddingTender;
use App\Models\CreateTender;
use App\Models\ShareTender;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;




class BiddingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tenders = Bidding::all();
        return BiddingTenderResource::collection($tenders);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BiddingRequest $request)
    {

        try {
            $validatedData = $request->validated();
            $shared_tender_details = ShareTender::where('id', $request->input('share_id'))->first();
            $project_id = $validatedData['project_id'];

            $project_detail = CreateTender::where('project_id', $project_id)->first();
            $project_name = $project_detail->project_name;
            $validatedData['project_name'] = $project_name;
            $validatedData['tender_status'] = $request->input('tender_status') ?? 'in-review';

            $rapisurv_bid_number = $request->input('rapisurv_bid_number');
            $validatedData['rapisurv_bid_number'] =  $rapisurv_bid_number;
            if ($shared_tender_details->in_app_financial_bid == true) {
                $request->validate([
                    'rapisurv_bid_number' => 'required|string',
                ]);
            }
            $uploadedDocuments = [];

            if ($request->hasFile('sign')) {
                $logoFile = $request->file('sign');
                $logoOriginalName = $logoFile->getClientOriginalName();
                $logoPath = $logoFile->storeAs('sign', $logoOriginalName); // Update the storage path as needed

                $validatedData['sign'] = $logoOriginalName;
            }

            // Process documents upload
            if ($request->hasFile('documents')) {
                $counter = 2; // Set initial counter value for regular documents
                foreach ($request->file('documents') as $document) {
                    $documentOriginalName = $document->getClientOriginalName();

                    $documentPath = $document->storeAs('bidding', $documentOriginalName); // Update the storage path as needed

                    $fullDocumentPath = storage_path('app/' . $documentPath);
                    $uploadedDocuments[] = [
                        'documents' => $documentOriginalName,
                        'document_path' => $fullDocumentPath,

                        'documents_id' => $counter++,
                    ];
                }
            }

            // Sort uploadedDocuments based on the desired order (e.g., tenders2.pdf first)
            usort($uploadedDocuments, function ($a, $b) {
                return strcasecmp($a['documents'], 'tenders2.pdf') <=> strcasecmp($b['documents'], 'tenders2.pdf');
            });

            $pdf = PDF::loadView('biddingpdf', [
                'validatedData' => $validatedData,
                'uploadedDocuments' => $uploadedDocuments,
            ]);

            $generatedPdfName = 'Main Document From bidding.pdf';

            $documentPathBidding = "bidding/{$generatedPdfName}";

            if (Storage::disk('public')->exists($generatedPdfName)) {
                $documentPathbidding = Storage::delete("bidding/{$generatedPdfName}", $pdf->output());
            }


            $documentContent = $pdf->output();
            Storage::put($documentPathBidding, $documentContent);   // Include the PDF information in the documents array
            $documentInfo = [
                'documents' => $generatedPdfName,
                'document_path' => storage_path("app/{$documentPathBidding}"),

                'documents_id' => 1, // Set the documents_id to 1 for the generated PDF
            ];
            array_unshift($uploadedDocuments, $documentInfo); // Add the generated PDF at the beginning

            $validatedData['documents'] = json_encode($uploadedDocuments);
            $tenderCreate = Bidding::create($validatedData);
            $recipientEmail = $request->input('email') ?? '';
            if ($tenderCreate) {
                // Handle the case where the tender creation failed
                Mail::to($recipientEmail)->send(new BiddingTender($validatedData));
            }
            return response()->json(['message' => 'Bidding created successfully', 'data' => $tenderCreate], 201);
        } catch (\Exception $e) {
            // Handle exceptions here
            return response()->json(['message' => 'Error creating biddings: ' . $e->getMessage()], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $tender = Bidding::findOrFail($id);
            return new BiddingTenderResource($tender);
        } catch (ModelNotFoundException $exception) {
            return response()->json(['message' => 'Tender not found', $exception], 404);
        }
    }
    /**
     * Show the form for editing the specified resource.
     */

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bidding $bidding)
    {
        $biddingDetail = Bidding::find($bidding->id);
        $bidding->update([
            'tender_status' => $request->input('tender_status') ?? $bidding->tender_status,
            'rapisurv_bid_number' => $request->input('rapisurv_bid_number') ?? $bidding->rapisurv_bid_number,
        ]);

        $uploadedDocuments = [];
        if ($request->hasFile('sign')) {
            $logoFile = $request->file('sign');
            $logoOriginalName = $logoFile->getClientOriginalName();
            $logoPath = $logoFile->storeAs('sign', $logoOriginalName); // Update the storage path as needed

            $validatedData['sign'] = $logoOriginalName;
            $bidding->update(['sign' => $logoOriginalName]);
        }
        // Process documents upload
        if ($request->hasFile('documents')) {
            $counter = 2; // Set initial counter value for regular documents
            foreach ($request->file('documents') as $document) {
                $documentOriginalName = $document->getClientOriginalName();
                $documentPath = $document->storeAs('bidding', $documentOriginalName); // Update the storage path as needed
                // Store document information with document_id
                $uploadedDocuments[] = [
                    'documents' => $documentOriginalName,
                    'documents_id' => $counter++,
                ];
            }

            $pdf = PDF::loadView('biddingpdf', [
                'validatedData' => $biddingDetail,
                'uploadedDocuments' => $uploadedDocuments,
            ]);

            $generatedPdfName = 'Main Document From Compiler.pdf';
            $documentPath = "documents/{$generatedPdfName}";

            // Directly overwrite the existing file or create a new one
            $documentPath = Storage::put($documentPath, $pdf->output());

            // Include the PDF information in the documents array
            $documentInfo = [
                'documents' => $generatedPdfName,
                'documents_id' => 1, // Set the documents_id to 1 for the generated PDF
            ];

            array_unshift($uploadedDocuments, $documentInfo); // Add the generated PDF at the beginning
            $documents = empty($uploadedDocuments) ? json_encode($bidding->documents) : json_encode($uploadedDocuments);

            $bidding->update(['documents' => $documents]);
        }

        return response()->json(['message' => 'Bidding updated successfully', 'data' => $bidding], 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $Bidding = Bidding::find($id);

        // Check if the tender exists
        if (!$Bidding) {
            return response()->json(['message' => 'Bidding not found'], 404);
        }
        $Bidding->delete();

        return response()->json(['message' => 'Bidding deleted successfully']);
    }
}
