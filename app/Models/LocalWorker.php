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
    public function timesheet()
    {
        return $this->belongsTo(Timesheet::class, 'timesheet_id', 'id');
    }
}
