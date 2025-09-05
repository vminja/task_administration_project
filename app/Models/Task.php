<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class Task extends Model
{
    protected $fillable = ['id', 'title', 'description', 'manager_id', 'group_id', 'deadline', 'priority', 'status', 'created_at', 'updated_at'];

     public function returnAllTasks($request)
    {
        $start = isset($request['start']) ? $request['start'] : 0;
        $length = isset($request['length']) ? $request['length'] : 0;
        $sort = 'tasks.title';
        $sorting = 'asc';
        $search = isset($request['search']['value']) ? $request['search']['value'] : 0;

        if (isset($request['order'][0]['column'])) {
            switch ($request['order'][0]['column']) {
                case '0':
                    $sort = 'tasks.title';
                    break;
                case '1':
                    $sort = 'tasks.description';
                    break;
            }
        }

        if (isset($request['order'][0]['dir'])) {
            $sorting = $request['order'][0]['dir'];
        }

        //setovanje datuma u pravi format za trazenje u bazi
        if (isset($request['filters']['date_from'])) {
            $fromDate = $request['filters']['date_from'];
            if($fromDate !== null){
                $carbonDate = Carbon::parse($fromDate);
                $fromDate = $carbonDate->format("Y-m-d H:i:s");
            }
        }

        if (isset($request['filters']['date_to'])) {
            $toDate = $request['filters']['date_to'];
            if($toDate !== null){
                $carbonDate = Carbon::parse($toDate);
                $toDate = $carbonDate->format("Y-m-d H:i:s");
            }
        }

        $query = DB::table('tasks')->
            select(
                'tasks.id',
                'tasks.title as task_title',
                'tasks.description as task_description',
                'tasks.deadline as task_deadline',
                'tasks.priority as task_priority',
                'tasks.status as task_status',
                'task_groups.name as group_name',
                DB::raw('GROUP_CONCAT(users.full_name SEPARATOR ", ") as executor_names'),
                DB::raw('manager.full_name as manager_name')
            )
            ->leftJoin('task_groups', 'task_groups.id', '=', 'tasks.group_id')
            ->leftJoin('task_executors', 'task_executors.task_id', '=', 'tasks.id')
            ->leftJoin('users', 'users.id', '=', 'task_executors.user_id')
            ->leftJoin('users as manager', 'manager.id', '=', 'tasks.manager_id')
            ->groupBy(
                'tasks.id',
                'tasks.title',
                'tasks.description',
                'tasks.deadline',
                'tasks.priority',
                'tasks.status',
                'task_groups.name',
                'manager.full_name'
            );

        // dd($query->get());
        $query->orderBy($sort, $sorting);

        if (!empty($search)) {
            $query = $query->havingRaw("task_title LIKE ? OR executor_names LIKE ?", ["%{$search}%", "%{$search}%"]);
        }

        if (!empty($fromDate) && !empty($toDate)) {
            $query = $query->where('tasks.deadline', '>=', $fromDate)->where('tasks.deadline', '<', $toDate);
        } else if (!empty($fromDate) && empty($toDate)) {
            $query = $query->where('tasks.deadline', '>=', $fromDate);
        } else if (empty($fromDate) && !empty($toDate)) {
            $query = $query->where('tasks.deadline', '<', $toDate);
        }

        if (!empty($request['filters']['priority'])) {
            $query->where('tasks.priority', $request['filters']['priority']);
        }

        if (!empty($request['filters']['executor_id'])) {
            $query->whereIn('tasks.id', function ($subquery) use ($request) {
                $subquery->select('task_id')
                    ->from('task_executors')
                    ->where('user_id', $request['filters']['executor_id']);
            });
        }

        if (!empty($request['filters']['title'])) {
            $query->where('tasks.title', 'like', '%' . $request['filters']['title'] . '%');
        }

        $recordsFiltered = $query->count();
        $recordsTotal = $query->offset($start)->limit($length)->get();
        $data = $query->get();
   
        
        return [
            'recordsFiltered' => $recordsFiltered,
            'recordsTotal' => $recordsTotal,
            'data' => $data,
        ];
    }

    public function returnAllTasksExec($request)
    {
        // dd($request);
        $start = isset($request['start']) ? $request['start'] : 0;
        $length = isset($request['length']) ? $request['length'] : 0;
        $sort = 'tasks.title';
        $sorting = 'asc';
        $search = isset($request['search']['value']) ? $request['search']['value'] : 0;

        if (isset($request['order'][0]['column'])) {
            switch ($request['order'][0]['column']) {
                case '0':
                    $sort = 'tasks.title';
                    break;
                case '1':
                    $sort = 'tasks.description';
                    break;
            }
        }

        if (isset($request['order'][0]['dir'])) {
            $sorting = $request['order'][0]['dir'];
        }

        //setovanje datuma u pravi format za trazenje u bazi
        if (isset($request['filters']['deadline'])) {
            $deadlineDate = $request['filters']['deadline'];
            if($deadlineDate !== null){
                $carbonDate = Carbon::parse($deadlineDate);
                $deadlineDate = $carbonDate->format("Y-m-d H:i:s");
            }
        }

        $query = DB::table('tasks')
            ->select(
                'tasks.id',
                'tasks.title as task_title',
                'tasks.description as task_description',
                'tasks.deadline as task_deadline',
                'tasks.priority as task_priority',
                'task_groups.name as group_name',
                DB::raw('GROUP_CONCAT(users.full_name SEPARATOR ", ") as executor_names'),
                DB::raw('manager.full_name as manager_name')
            )
            ->leftJoin('task_groups', 'task_groups.id', '=', 'tasks.group_id')
            ->leftJoin('task_executors', 'task_executors.task_id', '=', 'tasks.id')
            ->leftJoin('users', 'users.id', '=', 'task_executors.user_id')
            ->leftJoin('users as manager', 'manager.id', '=', 'tasks.manager_id')
            ->whereIn('tasks.id', function($q) {
                $q->select('task_id')
                ->from('task_executors')
                ->where('user_id', Auth::id());
            })
            ->groupBy(
                'tasks.id',
                'tasks.title',
                'tasks.description',
                'tasks.deadline',
                'tasks.priority',
                'task_groups.name',
                'manager.full_name'
            );

        // dd($query->get());
        $query->orderBy($sort, $sorting);

        if (!empty($search)) {
            $query = $query->havingRaw("task_title LIKE ? OR executor_names LIKE ?", ["%{$search}%", "%{$search}%"]);
        }

        if (!empty($deadlineDate)) {
            $query = $query->where('tasks.deadline', '=', $deadlineDate);
        }

        if (!empty($request['filters']['executor_id'])) {
            $query->whereIn('tasks.id', function ($subquery) use ($request) {
                $subquery->select('task_id')
                    ->from('task_executors')
                    ->where('user_id', $request['filters']['executor_id']);
            });
        }

        if (!empty($request['filters']['manager_id'])) {
            $query->where('tasks.manager_id', $request['filters']['manager_id']);
        }

        $recordsFiltered = $query->count();
        $recordsTotal = $query->offset($start)->limit($length)->get();
        $data = $query->get();
   
        
        return [
            'recordsFiltered' => $recordsFiltered,
            'recordsTotal' => $recordsTotal,
            'data' => $data,
        ];
    }

    public function group()
    {
        return $this->belongsTo(TaskGroup::class, 'group_id');
    }

    public function task_executors()
    {
        return $this->hasMany(TaskExecutor::class, 'task_id')->with('user');
    }


    
}
