<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ARStatusCodes extends Model
{
    use HasFactory ,SoftDeletes;
    protected $fillable = ['project_id','sub_project_id','status_code','status','added_by'];
}
