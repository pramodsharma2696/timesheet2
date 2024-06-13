<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function worker()
    {
        return $this->belongsTo(LocalWorker::class, 'worker_id', 'id');
    }
}
