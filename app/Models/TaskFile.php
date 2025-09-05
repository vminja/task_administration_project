<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskFile extends Model
{
    protected $fillable = ['id', 'task_id', 'file_path', 'file_name', 'uploaded_at'];

    public $timestamps = false;

}
