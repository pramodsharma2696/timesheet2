<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Statistics extends Model
{
    use HasFactory;
    protected $fillable = [
        'project_id',
        'invitations',
        'submissions',
        'documents',
        'in_reviews',
        'accepted',
        'rejected',
        'unread',
    ];
}
