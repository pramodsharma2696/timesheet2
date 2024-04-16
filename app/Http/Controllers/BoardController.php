<?php

namespace App\Http\Controllers;

use App\Models\Bidding;
use App\Models\Board;
use App\Models\CreateTender;
use App\Models\ShareTender;
use Illuminate\Http\Request;
use App\Models\Statistics;
use Illuminate\Support\Facades\Validator;

use Illuminate\Database\Eloquent\ModelNotFoundException; // Import the exception class

class BoardController extends Controller
{

    /**
     * Store a newly created resource in storage.
     */

    public function show($id)
    {

        try {
            $board_details = Board::where('project_id', $id)->get();
            // Check if the board_details collection is empty
            if ($board_details->isEmpty()) {
                return response()->json(['message' => 'Board Details not found'], 404);
            }
            foreach ($board_details as $board_detail) {

                $board_detail['members'] = json_decode($board_detail['members'], true);
            }
            return response()->json(['board_details' => $board_details], 200);
        } catch (ModelNotFoundException $exception) {
            return response()->json(['message' => 'Board Details not found', $exception], 404);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required',
            'company_name' => 'required',
            'note' => 'required|string',
            'bidder_name' => 'required|string',
        ]);

        $projectId = $request->input('project_id');
        $companyName = $request->input('company_name');

        // Check if the company exists for the given project
        $project_detail = Bidding::where('project_id', $projectId)
                                 ->where('company', $companyName)
                                 ->first();

        if (!$project_detail) {
            return response()->json(['message' => 'Company not found for the project.'], 404);
        }

        // You can proceed to store the note for the selected company
        $validator = Validator::make($request->all(), [
            'note' => 'required|string',
            'bidder_name' => 'required|string',

        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation error', 'errors' => $validator->errors()], 400);
        }

        $note = new Board();
        $note->companyName = $companyName;
        $note->project_id = $projectId;
        $note->bidder_name = $request->input('bidder_name');
        $note->note = $request->input('note');
        $note->members = json_encode($request->input('members'));
        $note->save();

        return response()->json(['message' => 'Note stored successfully for the selected company.',$note]);
    }




    /**
     * Display the specified resource.
     */


    public function showboard($id)
    {
        $project_detail = Bidding::where('project_id', $id)->get();
        if ($project_detail->isNotEmpty()) {

            $responseData = [];
            foreach ($project_detail as $data) {
                $responseData[] = [
                    'Document Name' =>json_decode( $data['documents']),
                    'Submitted by' => $data['first_name'] . ' ' . $data['last_name'],
                    'Email' => $data['email'],
                    'Company' => $data['company'],
                    'Bid id' => $data['id'],
                    'received at' => $data['created_at'],
                    'status' => $data['tender_status']
                ];
            }
            return response()->json($responseData);
        } else {
            // Project not found
            return response()->json(['message' => 'Project not found.'], 404);
        }
    }



    /**
     * Show the form for editing the specified resource.
     */
    public function statisticsCount($id)
    {
        $project_id = $id; // Assuming $id holds the ID of the project
        $project_detail = Bidding::where('project_id', $id)->get();
        $submission_count = Bidding::where('project_id', $id)->count();
        $invitations_count = ShareTender::where('project_id', $project_id)->count();

        // Check if statistics entry exists for the given project ID
        $statistics = Statistics::where('project_id', $project_id)->first();

        if ($project_detail->isNotEmpty()) {
            $openCount = 0;
            $inReviewCount = 0;
            $totalDocuments = 0;
            $acceptedCount = 0;
            $rejectedCount = 0;

            foreach ($project_detail as $data) {
                if ($data['tender_status'] === 'open') {
                    $openCount++;
                } elseif ($data['tender_status'] === 'in-review') {
                    $inReviewCount++;
                } elseif ($data['tender_status'] === 'accepted') {
                    $acceptedCount++;
                } elseif ($data['tender_status'] === 'rejected') {
                    $rejectedCount++;
                }
                $documents = json_decode($data['documents'], true); // Decode the JSON string into an array
                $totalDocuments += count($documents);
            }

            // Update or create the statistics entry
            if ($statistics) {
                $statistics->update([
                    'invitations' => $invitations_count,
                    'submissions' => $submission_count,
                    'documents' => $totalDocuments,
                    'in_reviews' => $inReviewCount,
                    'accepted' => $acceptedCount,
                    'rejected' => $rejectedCount,
                ]);
            } else {
                Statistics::create([
                    'project_id' => $project_id,
                    'invitations' => $invitations_count,
                    'submissions' => $submission_count,
                    'documents' => $totalDocuments,
                    'in_reviews' => $inReviewCount,
                    'accepted' => $acceptedCount,
                    'rejected' => $rejectedCount,
                ]);
            }

            // Return the JSON response
            return response()->json([
                'invitations_count' => $invitations_count,
                'submission_count' => $submission_count,
                'open_count' => $openCount,
                'in_reviews' => $inReviewCount,
                'accepted' => $acceptedCount,
                'rejected' => $rejectedCount,
                'total_documents' => $totalDocuments,
            ]);
        } else {
            // Project not found
            return response()->json(['message' => 'Project not found.'], 404);
        }
    }

}
