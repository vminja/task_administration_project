<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskExecutor extends Model
{
    protected $fillable = ['id', 'task_id', 'user_id', 'is_completed', 'assigned_at', 'completed_at'];

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
