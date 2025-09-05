<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\TaskExecutor;
use App\Models\Task;
use App\Models\TaskComment;
use Carbon\Carbon;
use App\Models\TaskFile;
use App\Models\TaskGroup;
use Exception;

class TaskController extends Controller
{
    public function saveTask(Request $request)
    {
        // dd*($request->all());
        $title = $request->input('title');
        $description = $request->input('description');
        $deadline = $request->input('deadline');
        $priority = $request->input('priority');
        $manager_id = Auth::user()->id;
        $executors = $request->input('executors', []);
        $groupId = $request->input('group_id');

        $taskSave = new Task();
        $taskSave->title = $title;
        $taskSave->description = $description;
        $taskSave->manager_id = $manager_id;
        $taskSave->group_id = $groupId;
        $taskSave->deadline = $deadline;
        $taskSave->priority = $priority;
        $taskSave->created_at = Carbon::now();
        $taskSave->updated_at = Carbon::now();
        $taskSave->save();

        foreach ($executors as $executorId) {
            $exec = new TaskExecutor();
            $exec->task_id = $taskSave->id;
            $exec->user_id = $executorId;
            $exec->is_completed = false;
            $exec->assigned_at = Carbon::now();
            $exec->save();
        }

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                //čuvanje fajla u public folderu 
                $path = $file->store('attachments', 'public');

                $taksFile = new TaskFile();
                $taksFile->task_id = $taskSave->id;
                $taksFile->file_path = $path;
                $taksFile->file_name = $file->getClientOriginalName();
                $taksFile->uploaded_at = Carbon::now();
                $taksFile->save();
            }
        }

        return response()->json(['message' => 'Zadatak je uspešno sačuvan.'], 200);
    }

    public function returnAllTasks(Request $request)
    {
        try{
            // dd($request->all());
            $table = new Task;
            $sqlData = $table->returnAllTasks($request);

            $data['draw'] = $request->input('draw');
            $data['recordsFiltered'] = $sqlData['recordsFiltered'];
            $data['recordsTotal'] = count($sqlData['recordsTotal']);
            $data['data'] = $sqlData['data'];

            return json_encode($data, 200);

        } catch (Exception $e){
            dd($e->getMessage(), $e->getLine());
            return response()->json(['error' => 'Greška prilikom učitavanja tabele!'], 500);
        }
    }

    public function deleteTask(Request $request)
    {
        try {
            // dd($request->all());
            $taskId = $request->input('id');
            $task = Task::findOrFail($taskId);
            // Brisanje svih izvršilaca zadatka
            TaskExecutor::where('task_id', $taskId)->delete();
            // Brisanje svih fajlova vezanih za zadatak
            TaskFile::where('task_id', $taskId)->delete();
            // Brisanje komentara
            TaskComment::where('task_id', $taskId)->delete();

            // Brisanje zadatka
            $task->delete();

            return response()->json(['message' => 'Zadatak uspešno obrisan.'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Greška prilikom brisanja zadatka.'], 500);
        }
    }

    public function getTask($id)
    {
        try {
            $task = Task::findOrFail($id);

            $taskInfo = [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'manager_id' => $task->manager_id,
                'deadline' => Carbon::parse($task->deadline)->format('Y-m-d'),
                'priority' => $task->priority,
                'status' => $task->status,
                'group_id' => $task->group_id
            ];

            // dohvatanje izvršilaca
            $executors = TaskExecutor::where('task_id', $taskInfo['id'])->with('user')->get();
            $taskInfo['executors'] = $executors->map(function ($executor) {
                return [
                    'executor_id' => $executor->user->id,
                    'full_name' => $executor->user->full_name,
                ];
            })->toArray();

            // dohvatanje grupe
            $groupInfo = TaskGroup::find($task->group_id);
            if ($groupInfo) {
                $taskInfo['group_name'] = $groupInfo->name;
            }

            // dohvatanje fajlova
            $files = TaskFile::where('task_id', $taskInfo['id'])->get();
            $taskInfo['files'] = $files->map(function ($file) {
                return [
                    'file_id' => $file->id,
                    'file_name' => $file->file_name,
                    'file_path' => asset('storage/' . $file->file_path),
                ];
            })->toArray();

            // dd($taskInfo);
            return response()->json($taskInfo, 200);
        } catch (Exception $e) {
            dd($e->getMessage(), $e->getLine());
            return response()->json(['error' => 'Zadatak nije pronađen.'], 404);
        }
    }

    public function editTask(Request $request)
    {
        try {
            // dd($request->all());
            $task = Task::findOrFail($request->input('id'));
            $task->title = $request->input('title');
            $task->description = $request->input('description');
            $task->manager_id = Auth::id();
            $task->deadline = $request->input('deadline');
            $task->priority = $request->input('priority');
            $task->group_id = $request->input('group_id');
            $task->updated_at = Carbon::now();
            $task->save();

            TaskExecutor::where('task_id', $task->id)->delete();
            foreach ($request->input('executors', []) as $executorId) {
                TaskExecutor::create([
                    'task_id' => $task->id,
                    'user_id' => $executorId,
                    'is_completed' => false,
                    'assigned_at' => Carbon::now(),
                ]);
            }

            //brišemo postojeće zadatke
            $existingFileIds = $request->input('files', []);
                TaskFile::where('task_id', $task->id)
                    ->whereNotIn('id', $existingFileIds)
                    ->delete();

            //dodajemo nove zadatke
            if ($request->hasFile('files_new')) {
                foreach ($request->file('files_new') as $file) {
                    $path = $file->store('attachments', 'public');

                    TaskFile::create([
                        'task_id' => $task->id,
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'uploaded_at' => Carbon::now(),
                    ]);
                }
            }
            
            return response()->json(['message' => 'Zadatak uspešno izmenjen.'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Greška prilikom izmene zadatka.'], 500);
        }
    }

    public function getStatusTask(Request $request, $id){
        try{
            $task = Task::findOrFail($id);
            $statusInfo = [
                'id' => $task->id,
                'status' => $task->status
            ];
            
            return response()->json($statusInfo, 200);

        } catch(Exception $e){
            return response()->json(['error' => 'Zadatak nije pronađen.'], 404);
        }
    }

    public function saveStatus(Request $request){
        try{
            $task = Task::findOrFail($request->taskStatus["id"]);
            $task->status = $request->taskStatus["status"];
            $task->save();

            return response()->json(['message' => 'Status uspešno promenjen.'], 200);
        } catch(Exception $e){
            return response()->json(['error' => 'Došlo je do greške prilikom promene statusa.'], 404);
        }
    }

    public function returnAllTasksExec(Request $request){
        try{
            // dd($request->all());
            $table = new Task;
            $sqlData = $table->returnAllTasksExec($request);

            $data['draw'] = $request->input('draw');
            $data['recordsFiltered'] = $sqlData['recordsFiltered'];
            $data['recordsTotal'] = count($sqlData['recordsTotal']);
            $data['data'] = $sqlData['data'];

            return json_encode($data, 200);

        } catch (Exception $e){
            // dd($e->getMessage(), $e->getLine());
            return response()->json(['error' => 'Greška prilikom učitavanja tabele!'], 500);
        }
    }

    
}
