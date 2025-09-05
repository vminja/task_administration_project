<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class UserController extends Controller
{
    public function getUsers(Request $request)
    {
        try {
            $users = User::all();
            return response()->json($users, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Greška prilikom dobijanja podataka o korisnicima.'], 500);
        }
    }

    public function returnAllUsers(Request $request)
    {
        try{
            // dd($request->all());
            $table = new User;
            $sqlData = $table->returnAllUsers($request);

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

    public function getTypeUser(Request $request, $id){
        try{
            $userData = User::findOrFail($id);
            // dd($userData);

            $users = [
                'id' => $userData->id,
                'type' => $userData->type
            ];
            
            return response()->json($users, 200);

        } catch(Exception $e){
            // dd($e->getMessage(), $e->getLine());
            return response()->json(['error' => 'Korisnik nije pronađen.'], 404);
        }
    }

    public  function saveUserType(Request $request){
        // dd($request->all(),$request->userType['id']);
        try{
            $user = User::findOrFail($request->userType['id']);
            $user->type = $request->userType['type'];
            $user->updated_at = Carbon::now();
            $user->save();

            return response()->json(['message' => 'Tip korisnika je uspešno promenjen.'], 200);

        } catch(Exception $e){
            return response()->json(['error' => 'Greška prilikom promene tipa korisnika.'], 404);
        }
    }
}
