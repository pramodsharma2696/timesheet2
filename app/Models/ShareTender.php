<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShareTender extends Model
{
    use HasFactory;
    protected $fillable = [
        'email',
        'access_code',
        'recipient_first_name',
        'recipient_last_name',
        'project_id',
        'in_app_financial_bid'


    ];
}
