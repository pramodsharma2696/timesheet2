<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimeSheet extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $guarded = [];


    public function project(){
        return $this->belongsTo(ProjectList::class,'project_id','id');
    }
}
