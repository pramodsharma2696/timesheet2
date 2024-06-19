<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocalWorker extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function attendance()
    {
        return $this->hasOne(Attendance::class, 'worker_id', 'id');
    }
}
