<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;
use App\Models\TaskExecutor;

class TaskExecutorController extends Controller
{
    public function getCompletedStatusTask(Request $request, $id){
        try{
            $task = TaskExecutor::where('task_id', $id)->first();
            // dd($task);
            $statusInfo = [
                'id' => $task->id,
                'status' => $task->is_completed
            ];
            
            return response()->json($statusInfo, 200);

        } catch(Exception $e){
            // dd($e->getMessage(), $e->getLine());
            return response()->json(['error' => 'Zadatak nije pronađen.'], 404);
        }
    }
    public function saveCompletedStatus(Request $request){
        try{
            // dd($request->all());
            $task = TaskExecutor::findOrFail($request->taskStatus["id"]);
            $task->is_completed = $request->taskStatus["status"];
            $task->save();

            return response()->json(['message' => 'Status uspešno promenjen.'], 200);
        } catch(Exception $e){
            return response()->json(['error' => 'Došlo je do greške prilikom promene statusa.'], 404);
        }
    }
}
