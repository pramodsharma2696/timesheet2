<?php

namespace App\Http\Controllers;
use Inertia\Inertia;
use App\Models\ShareTender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailInvitation;
use App\Models\CreateTender;
use Carbon\Carbon;
class ShareTenderController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'recipient_first_name' => 'required|string',
            'recipient_last_name' => 'required|string',
            'email' => 'required|email',

            'project_id' => 'required|',
        ]);
        // Fetch data from the database
        $tender = CreateTender::where('project_id', $request->input('project_id')) ->first();;
        if($tender){

        if ($tender->validity_end >= Carbon::now()->toDateString()) {
            $validityStartDate =  $tender->validity_start;
            $validityEndDate =  $tender->validity_end;

            $accessCode = hash('sha256', $request->input('project_name') . time());
            $documentPaths = json_decode($tender->documents);
            $project_name = $tender->project_name;

            $content = [
                'recipient_name' => $request->input('recipient_first_name') . ' ' . $request->input('recipient_last_name'),

                'project_id' => $tender->project_id,

                'project_name' => $project_name,
                'endDate' => $validityEndDate,
                'startDate' => $validityStartDate,
                'accessCode' => $accessCode,
                'in_app_financial_bid'=> $request->input('in_app_financial_bid'),
                'documents' =>  $documentPaths,
            ];
            $email = $request->input('email') ;
            try {
                Mail::to($email)->send(new EmailInvitation($content));
                // Email sent successfully, store in database
                ShareTender::create([
                    'email' =>  $email,
                    'access_code' => $content['accessCode'],
                    'recipient_first_name' => $request->input('recipient_first_name'),
                    'recipient_last_name' =>  $request->input('recipient_last_name'),

                    'project_id' => $tender->project_id,

                    'in_app_financial_bid' => $request->input('in_app_financial_bid'),
                ]);
            } catch (\Exception $e) {
                echo "Failed to send the email. Error: " . $e->getMessage();
            }

            return response()->json(['message' => 'Invitation sent successfully', 'data' => $content]);
        } else {
            // Handle the case where no data is found in the database
            return response()->json(['message' => 'Tender has been expired'], 404);
        }
    }else{
        return response()->json(['message' => 'No Tender found '], 404);
    }
    }

    public function showEmailInvitation()
    {
        return Inertia::render('EmailInvitation');
    }
}
