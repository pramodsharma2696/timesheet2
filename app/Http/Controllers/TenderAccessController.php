<?php

namespace App\Http\Controllers;

use App\Models\CreateTender;
use App\Models\ShareTender;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TenderAccessController extends Controller
{
    public function access_tender(Request $request)
    {
        // Validate the login request
        $request->validate([
            'email' => 'required|email',
            'access_code' => 'required',
        ]);

        // Attempt to authenticate the user
        $user = ShareTender::where('email', $request->input('email'))
            ->where('access_code', $request->input('access_code'))
            ->first();

        if ($user) {
            $project_id = $user->project_id;
            // Check if the user has a valid project_id
            if (!$project_id) {
                return response()->json(['message' => 'Invalid project ID.'], 401);
            }
            // Retrieve tender details
            $tender_detail = CreateTender::find($project_id);

            // Check if the tender details are found
            if (!$tender_detail) {
                return response()->json(['message' => 'Tender details not found.'], 404);
            }
            $validity = $tender_detail->validity_end;
            $status = $tender_detail->tender_status;

            if ($validity >= Carbon::now()->toDateString()) {
                // Valid access

                $user['tender_status']=$status;
                $user['validity']=$validity;

                unset($user['created_at']);
                unset($user['updated_at']);

                return response()->json(['message' => 'Tender access success', 'data' => $user], 200);
            } else {
                // Access expired
                // Set tender_status to "closed"
                $tender_detail->tender_status = 'closed';
                $tender_detail->save();

                return response()->json(['message' => 'Access has expired.'], 401);
            }
            // Authentication failed

        }else {
            // Authentication failed
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }
    }
}
