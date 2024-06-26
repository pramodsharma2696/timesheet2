<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UniversalWorker extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function pendingInvitations()
    {
        return $this->hasOne(PendingInvitation::class, 'worker_id', 'worker_id');
    }
}
