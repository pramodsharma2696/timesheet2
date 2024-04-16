<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Helper\ModelUpdateHelper;
use App\Models\Invoice;
use App\Transformers\GenericTransformer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * @group Invoice controller
 *
 */
class InvoiceController extends Controller
{
    protected $modulesName = 'invoice';
    use ModelUpdateHelper;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter = $request->validate([
            'items' => ['integer', 'gt:0'],
            'order' => [Rule::in(["asc", "desc"])],
            'sort' => [Rule::in(["created_at", "updated_at", "id"])],
            'search' => ['string'],
            "invoice_type"=>['string']
        ]);
        $user = $request->user();
        $team = $user->currentTeam;

        $invoices = Invoice::select("invoices.*")->where('invoice_users.team_id', $team->id)->where('invoice_users.user_id', $user->id)->where(function ($query) use ($filter) {

            if (isset($filter['search'])) {
                $query->where('invoices.id', 'LIKE', '%' . $filter['search'] . '%')->orWhere('meta', 'LIKE', '%' . $filter['search'] . '%');
            }

            if (isset($filter['invoice_type'])) {
                $query->whereJsonContains('meta->invoice_type', $filter['invoice_type']);
            }
        })
            ->join("invoice_users", "invoice_users.invoice_id", "=", "invoices.id")
            ->orderBy($filter['sort'] ?? 'id', $filter['order'] ?? 'desc')
            ->paginate($filter['items'] ?? 10);

        return responder()->success($invoices, new GenericTransformer(true));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $request->validate([
            "purchase_id"=>['exists:purchases,id','required'],
            "invoice_type"=>["string","required"]
        ]);
        //check you have global permission to create
        $permissions = $user->getAllPermissions()->filter(function ($item) {
            return Str::startsWith($item['name'], "$this->modulesName.");
        })->pluck("name")->all();
        $team = $user->currentTeam;
        $input = $request->input();
        $input['user_id'] = $user->id;
        $input['team_id'] = $team->id;
        $invoice =  $this->saveModel(new Invoice($input));
        //update this invoice users
        $invoice->users()->syncWithoutDetaching([$user->id => ['permission' => json_encode($permissions), "team_id" => $team->id]]);

        return responder()->success($invoice, new GenericTransformer(true));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $invoice)
    {
        $user = $request->user();

        $team = $user->currentTeam;

        $invoices = Invoice::where("invoices.id", $invoice)
        ->select("invoices.*","invoice_users.permission")
        ->where('invoice_users.team_id', $team->id)->where('invoice_users.user_id', $user->id)
            ->join("invoice_users", "invoice_users.invoice_id", "=", "invoices.id")

            ->first();

        return responder()->success($invoices, new GenericTransformer(true));
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $invoice)
    {
        $user = $request->user();
        $input = $request->except(["project_list_id","purchase_id","invoice_type"]);


        $team = $user->currentTeam;

        $invoices = Invoice::where("invoices.id", $invoice)->where('invoice_users.team_id', $team->id)
        ->select("invoices.*")
        ->where('invoice_users.user_id', $user->id)
            ->join("invoice_users", "invoice_users.invoice_id", "=", "invoices.id")
            ->first();

        if (isset($invoices)) {
            if ($this->updateModel($input, $invoices)) {
                return responder()->success(['message' => 'invoice has been updated']);
            }
        }
        throw  ValidationException::withMessages([$invoice => "Failed to update invoices"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $invoice)
    {
        $user = $request->user();

        $team = $user->currentTeam;

        $invoices = invoice::where("invoices.id", $invoice)->where('invoice_users.team_id', $team->id)
        ->select("invoices.*")
        ->where('invoice_users.user_id', $user->id)
            ->join("invoice_users", "invoice_users.invoice_id", "=", "invoices.id")
            ->first();

        if (isset($invoices)) {
            if ($invoices->delete()) {
                return responder()->success(['message' => 'invoice has been deleted']);
            }
        }
        throw  ValidationException::withMessages([$invoice => "Failed to delete invoices"]);

    }
}
