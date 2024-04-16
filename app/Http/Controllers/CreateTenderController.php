<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTenderRequest;
use App\Models\CreateTender;
use Illuminate\Http\Request;
use App\Http\Resources\TenderResource;
use Illuminate\Database\Eloquent\ModelNotFoundException; // Import the exception class
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class CreateTenderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tenders = CreateTender::all();
        return TenderResource::collection($tenders);
    }
    public function store(CreateTenderRequest $request)
    {
        $validatedData = $request->validated();

        // Process logo upload
        if ($request->hasFile('logo')) {
            $logoFile = $request->file('logo');
            $logoOriginalName = $logoFile->getClientOriginalName();
            $logoPath = $logoFile->storeAs('logos', $logoOriginalName);
            $validatedData['logo'] = $logoOriginalName;
        }

        $uploadedDocuments = [];

        // Process documents upload
        if ($request->hasFile('documents')) {
            $counter = 2; // Set initial counter value for regular documents
            foreach ($request->file('documents') as $document) {
                $documentOriginalName = $document->getClientOriginalName();
                $documentPath = $document->storeAs('documents', $documentOriginalName); // Update the storage path as needed
                // Store document information with document_id
                $uploadedDocuments[] = [
                    'documents' => $documentOriginalName,
                    'documents_id' => $counter++,
                ];
            }
        }

        // Sort uploadedDocuments based on the desired order (e.g., tenders2.pdf first)
        usort($uploadedDocuments, function ($a, $b) {
            return strcasecmp($a['documents'], 'tenders2.pdf') <=> strcasecmp($b['documents'], 'tenders2.pdf');
        });

        $pdf = PDF::loadView('myPDF', $validatedData);
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

        $validatedData['documents'] = json_encode($uploadedDocuments);

        $tenderCreate = CreateTender::create($validatedData);

        return response()->json(['message' => 'Tender created successfully', 'data' => $tenderCreate], 201);
    }


    public function show($id)
    {

        try {
            $tender = CreateTender::findOrFail($id);
            return new TenderResource($tender);
        } catch (ModelNotFoundException $exception) {
            return response()->json(['message' => 'Tender not found'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CreateTenderRequest $request, $id)
    {
        try {
            $tender = CreateTender::findOrFail($id);

            $validatedData = $request->validated();

            // Handle logo_path file upload
            if ($request->hasFile('logo')) {
                $logoFile = $request->file('logo');
                $logoOriginalName = $logoFile->getClientOriginalName();
                $logoPath = $logoFile->storeAs('logos', $logoOriginalName); // Update the storage path as needed
                $validatedData['logo_pat'] = $logoPath;
                $validatedData['logo'] = $logoOriginalName;
            }


            $uploadedDocuments = [];

            // Process documents upload
            if ($request->hasFile('documents')) {
                $counter = 2; // Set initial counter value for regular documents
                foreach ($request->file('documents') as $document) {
                    $documentOriginalName = $document->getClientOriginalName();
                    $documentPath = $document->storeAs('documents', $documentOriginalName); // Update the storage path as needed
                    // Store document information with document_id
                    $uploadedDocuments[] = [
                        'documents' => $documentOriginalName,
                        'documents_id' => $counter++,
                    ];
                }
            }

            // Sort uploadedDocuments based on the desired order (e.g., tenders2.pdf first)
            usort($uploadedDocuments, function ($a, $b) {
                return strcasecmp($a['documents'], 'tenders2.pdf') <=> strcasecmp($b['documents'], 'tenders2.pdf');
            });

            $pdf = PDF::loadView('myPDF', $validatedData);
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

            $validatedData['documents'] = json_encode($uploadedDocuments);
            // Update the tender with the new data
            $tender->update($validatedData);

            return response()->json(['message' => 'Tender updated successfully', 'data' => $tender]);
        } catch (ModelNotFoundException $exception) {
            // Handle the case where the tender is not found
            return response()->json(['message' => 'Tender not found'], 404);
        } catch (\Exception $exception) {
            // Log the exception for debugging purposes
            Log::error('Error updating tender: ' . $exception->getMessage());

            // Return a generic error response
            return response()->json(['message' => 'An unexpected error occurred'], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request,  $id)
    {
        // Retrieve the CreateTender model instance based on the ID
        $createTender = CreateTender::find($id);

        // Check if the tender exists
        if (!$createTender) {
            return response()->json(['message' => 'Tender not found'], 404);
        }
        $createTender->delete();

        return response()->json(['message' => 'Tender deleted successfully']);
    }
}
