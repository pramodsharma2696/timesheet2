<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\BiddingController;
use App\Http\Controllers\TimeSheetController;
use App\Http\Controllers\ShareTenderController;
use App\Http\Controllers\CreateTenderController;
use App\Http\Controllers\TenderAccessController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(
    [
        'namespace'  => 'App\Http\Controllers',
    ],
    function () {

        Route::post('login', 'AuthController@login')->name('user.login');
        Route::get("micro-status", fn () => response()->json(["active"]));

        Route::middleware(['cors','auth:sanctum'])
            ->group(function () {
                Route::apiResource("invoices", "InvoiceController");
                Route::apiResource("tenders", "CreateTenderController");
                Route::apiResource("board", "BoardController");
                Route::post('/tenders/update/{tender}', [CreateTenderController::class, 'update']);

                Route::post('/boards/statistics/{id}', [BoardController::class, 'statisticsCount']);
                Route::get('/boards/showboard/{id}', [BoardController::class, 'showboard']);

                Route::apiResource("bidding", "BiddingController");
                Route::post('/share_tender', [ShareTenderController::class, 'index']);
                Route::post('/access_tender', [TenderAccessController::class, 'access_tender']);
                Route::post('/bidding/update/{bidding}', [BiddingController::class, 'update']);

                // Timesheet Route
                Route::post('/create-timesheet',[TimeSheetController::class, 'createTimeSheet']);
                Route::get('/timesheet-details/{id}',[TimeSheetController::class, 'showTimeSheet'])->where('id', '[0-9]+');
                Route::get('/all-timesheet',[TimeSheetController::class, 'showAllTimeSheet']);
                Route::post('/update-timesheet',[TimeSheetController::class, 'updateTimeSheet']);
                Route::delete('/timesheet-delete/{id}',[TimeSheetController::class, 'deleteTimeSheet'])->where('id', '[0-9]+');
                Route::get('/generate-timesheet-id',[TimeSheetController::class, 'generateTimeSheetId']);
                // Route::get('/generate-qr/{projectid}',[TimeSheetController::class, 'generateQR'])->where('id', '[0-9]+');
                Route::get('/refresh-qr/{projectid}',[TimeSheetController::class, 'RefreshQR'])->where('id', '[0-9]+');
                Route::post('/add-local-worker',[TimeSheetController::class, 'addLocalWorker']);
                Route::post('/invite-worker',[TimeSheetController::class, 'InviteWorker']);
                Route::post('/update-worker',[TimeSheetController::class, 'UpdateWorker']);
                Route::get('/all-worker',[TimeSheetController::class, 'showWorkers']);            
            });
    }
);
