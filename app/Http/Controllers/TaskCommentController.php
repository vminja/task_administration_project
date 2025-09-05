<?php

namespace App\Http\Controllers;

use App\Models\TaskComment;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Auth;

class TaskCommentController extends Controller
{
    public function getCommentTask(Request $request, $id){
        try{
            $taskComments = TaskComment::with('user')->where('task_id', $id)->get();
            
            $comments = $taskComments->map(function ($comment) {
                return [
                    'user_full_name' => $comment->user->full_name ?? 'Nepoznat korisnik',
                    'comment' => $comment->comment,
                    'id' => $comment->id,
                ];
            });
            
            return response()->json($comments, 200);

        } catch(Exception $e){
            // dd($e->getMessage(), $e->getLine());
            return response()->json(['error' => 'Komentari za zadatak nisu pronađeni.'], 404);
        }
    }

    public function saveComments(Request $request){

        // dd($request->all());
        try {
            $taskComments = $request->input('taskComments', []);
            $task_id = $request->input('task_id');
            
            //uzimamo sve postojeće ID-jeve komentara
            $existingIds = collect($taskComments)
                ->whereNotNull('id')
                ->pluck('id')
                ->toArray();

            // dd($existingIds);
            
            //Brišemo komentare koji imaju isti task_id ali nisu u $existingIds
            //to znači da su oni obrisani na frontu 
            TaskComment::where('task_id', $task_id)
                ->whereNotIn('id', $existingIds)
                ->delete();

            //Dodajemo nove komentare u bazu
            foreach ($taskComments as $commentData) {
                if (empty($commentData['id'])) {
                    TaskComment::create([
                        'task_id' => $task_id,
                        'user_id' => Auth::id(),
                        'comment' => $commentData['comment'],
                        'created_at' => now(),
                    ]);
                }
            }
        
           return response()->json(['message' => 'Komentari su uspešno sačuvani.'], 200);

        } catch (Exception $e) {
            return response()->json(['error' => 'Greška prilikom brisanja komentara za zadatak.'], 500);
        }
    }

    public function getCommentTaskForExecutor(Request $request, $id){
        try{
            $taskComments = TaskComment::with('user')->where('task_id', $id)->where('user_id', Auth::id())->get();
            
            $comments = $taskComments->map(function ($comment) {
                return [
                    'user_full_name' => $comment->user->full_name ?? 'Nepoznat korisnik',
                    'comment' => $comment->comment,
                    'id' => $comment->id,
                ];
            });
            
            return response()->json($comments, 200);

        } catch(Exception $e){
            // dd($e->getMessage(), $e->getLine());
            return response()->json(['error' => 'Komentari za zadatak nisu pronađeni.'], 404);
        }
    }

    public function saveCommentsForExecutor(Request $request){
        try {
            $taskComments = $request->input('taskComments', []);
            $task_id = $request->input('task_id');
            
            //uzimamo sve postojeće ID-jeve komentara
            $existingIds = collect($taskComments)
                ->whereNotNull('id')
                ->pluck('id')
                ->toArray();

            //Brišemo komentare koji imaju isti task_id ali nisu u $existingIds
            //to znači da su oni obrisani na frontu 
            TaskComment::where('task_id', $task_id)
                ->where('user_id', Auth::id())
                ->whereNotIn('id', $existingIds)
                ->delete();

            //Dodajemo nove komentare u bazu
            foreach ($taskComments as $commentData) {
                if (empty($commentData['id'])) {
                    TaskComment::create([
                        'task_id' => $task_id,
                        'user_id' => Auth::id(),
                        'comment' => $commentData['comment'],
                        'created_at' => now(),
                    ]);
                }
            }
        
           return response()->json(['message' => 'Komentari su uspešno sačuvani.'], 200);

        } catch (Exception $e) {
            return response()->json(['error' => 'Greška prilikom brisanja komentara za zadatak.'], 500);
        }
    }
}
