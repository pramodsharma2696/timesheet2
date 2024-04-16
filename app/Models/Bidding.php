<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bidding extends Model
{
    use HasFactory;
    protected $fillable = [
        'project_name',
        'project_id',
        'first_name',
        'last_name',
        'email',
        'sign',
        'tender_status',
        'rapisurv_bid_number',
        'company',
        'documents',

        'document_path',

    ];
}
