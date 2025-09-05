<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class TaskGroup extends Model
{
    protected $fillable = ['id', 'name', 'description', 'created_by', 'created_at', 'updated_at'];

    public function returnAllGroups($request)
    {
        $start = isset($request['start']) ? $request['start'] : 0;
        $length = isset($request['length']) ? $request['length'] : 0;
        $sort = 'task_groups.name';
        $sorting = 'asc';
        $search = isset($request['search']['value']) ? $request['search']['value'] : 0;

        if (isset($request['order'][0]['column'])) {
            switch ($request['order'][0]['column']) {
                case '0':
                    $sort = 'task_groups.name';
                    break;
                case '1':
                    $sort = 'task_groups.description';
                    break;
            }
        }

        if (isset($request['order'][0]['dir'])) {
            $sorting = $request['order'][0]['dir'];
        }


        $query = DB::table('task_groups')
            ->select(
                'task_groups.id',
                'task_groups.name',
                'task_groups.description',
                DB::raw('users.full_name as created_by')
            )
            ->leftJoin('users', 'users.id', '=', 'task_groups.created_by');

        $query->orderBy($sort, $sorting);

        if (!empty($search)) {
            $query = $query->whereRaw("name LIKE '%{$search}%' OR description LIKE '%{$search}%' ");
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
}
