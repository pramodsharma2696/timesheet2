<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreateTender extends Model
{
    use HasFactory;
    protected $fillable = [

        'project_id',

        'project_name',
        'validity_start',
        'validity_end',
        'company',
        'project_title',
        'location',
        'customer',
        'prepared_by',
        'date',
        'conditions',
        'boq',
        'documents',
        'documents_id',
        'logo',
        'tender_status'

    ];
}
