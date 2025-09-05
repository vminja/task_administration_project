<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TaskGroup;
use Illuminate\Support\Facades\Auth;
use Exception;

class TaskGroupController extends Controller
{
    public function returnAllGroups(Request $request)
    {
       try{
            $table = new TaskGroup;
            $sqlData = $table->returnAllGroups($request);

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

    public function saveGroup(Request $request)
    {
        // dd($request->all());
    
        $group = new TaskGroup();
        $group->name = $request->input('name');
        $group->description = $request->input('description');
        $group->created_by = Auth::id();  
        $group->save();

        return response()->json(['message' => 'Grupa uspešno sačuvana.']);

    }

    public function getGroup($id)
    {
        try {
            $group = TaskGroup::findOrFail($id);

            $groupInfo = [
                'id' => $group->id,
                'name' => $group->name,
                'description' => $group->description
            ];

            return response()->json($groupInfo, 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Grupa nije pronađena.'], 404);
        }
    }

    public function editGroup(Request $request)
    {
        // dd($request->all());
        try {
            $group = TaskGroup::findOrFail($request->input('id'));
            $group->name = $request->input('name');
            $group->description = $request->input('description');
            $group->save();

            return response()->json(['message' => 'Grupa uspešno izmenjena.'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Greška prilikom izmene grupe.'], 500);
        }
    }

    public function deleteGroup(Request $request)
    {
        // dd($request->all());
        try {
            $group = TaskGroup::findOrFail($request->input('id'));
            $group->delete();

            return response()->json(['message' => 'Grupa uspešno obrisana.'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Greška prilikom brisanja grupe.'], 500);
        }
    }

    public function getGroups()
    {
        try {
            $groups = TaskGroup::all();
            return response()->json($groups, 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Greška prilikom dobijanja grupa.'], 500);
        }
    }
}
