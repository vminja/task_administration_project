<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ActivationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\TaskGroupController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TaskCommentController;
use App\Http\Controllers\TaskExecutorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 

//view za registraciju
Route::get('/register', function (Request $request) {
    // dd($request->all());
    return view('registerForm');
});

Route::post('/subminRegister', [RegisterController::class, 'register']);

//view za login
Route::get('/login', function (Request $request) {
    // dd($request->all());
    return view('loginForm');
});

Route::get('/', function (Request $request) {
    // dd($request->all());
    return view('loginForm');
});

Route::post('/submitLogin', [LoginController::class, 'login']);

//aktivacija naloga
Route::get('/activate/{token}', [ActivationController::class, 'activate']);

//ruta za ponovno slanje link za aktivaciju naloga
Route::post('/resendActivationLink', [LoginController::class, 'resendActivationLink']);

//ruta za slanje linka za reset lozinke
Route::post('/sendResetLink', [LoginController::class, 'sendResetLink']);

//reset link 
Route::get('/resetLink/{token}', [PasswordResetLinkController::class, 'resetPasswordLink']);

//ruta za promenu lozinke
Route::post('/submitNewPassword', [PasswordResetLinkController::class, 'submitNewPassword']);


//grupni middleware za autentifikaciju UserAuthCheckMiddleware
Route::middleware(['user.auth'])->group(function () {
    //view za administraciju zadataka
    Route::get('/taskAdministration', function (Request $request) {
        if(Auth::user()->type === "admin" || Auth::user()->type === "manager"){
            return view('taskAdministration');
        }
        else {
            return view('executorTasks');

        }
    });

    Route::get('/returnAllGroups', [TaskGroupController::class, 'returnAllGroups']);

    //kreiranje nove grupe
    Route::post('/saveGroup', [TaskGroupController::class, 'saveGroup']);

    //ruta za dobijanje grupe po ID
    Route::get('/getGroup/{id}', [TaskGroupController::class, 'getGroup']);

    //ruta za izmenu grupe
    Route::post('/editGroup', [TaskGroupController::class, 'editGroup']);

    //ruta za brisanje grupe
    Route::post('/deleteGroup', [TaskGroupController::class, 'deleteGroup']);

    //dohvatanje korisnika
    Route::get('/getUsers', [UserController::class, 'getUsers']);

    //dohvatanje grupa
    Route::get('/getGroups', [TaskGroupController::class, 'getGroups']);

    //kreiranje zadatka
    Route::post('/saveTask', [TaskController::class, 'saveTask']);

    //dohvatanje svih zadataka
    Route::get('/returnAllTasks', [TaskController::class, 'returnAllTasks']);

    //brisanje zadatka
    Route::post('/deleteTask', [TaskController::class, 'deleteTask']);

    //dohvatanje zadatka za edit
    Route::get('/getTask/{id}', [TaskController::class, 'getTask']);

    //izmena zadatka
    Route::post('/editTask', [TaskController::class, 'editTask']);

    //dohvatanje statusa zadatka
    Route::get('/getStatusTask/{id}', [TaskController::class, 'getStatusTask']);

    //promena statusa zadatka 
    Route::post('/saveStatus', [TaskController::class, 'saveStatus']);

    //dohvatanje komentara zadatka
    Route::get('/getCommentTask/{id}', [TaskCommentController::class, 'getCommentTask']);

    //cuvanje komentara
    Route::post('/saveComments', [TaskCommentController::class, 'saveComments']);

    //ruta za izvršioce datatable
    Route::get('/returnAllTasksExec', [TaskController::class, 'returnAllTasksExec']);

    //ruta za status completed za korisnika 
    Route::get('/getCompletedStatusTask/{id}', [TaskExecutorController::class, 'getCompletedStatusTask']);

    //čuvanje promene statusa za izvršioca 
    Route::post('/saveCompletedStatus', [TaskExecutorController::class, 'saveCompletedStatus']);

    //dohvatanje komentara zadatka za izvršioca
    Route::get('/getCommentTaskForExecutor/{id}', [TaskCommentController::class, 'getCommentTaskForExecutor']);

    //cuvanje komentara za izvršioca
    Route::post('/saveCommentsForExecutor', [TaskCommentController::class, 'saveCommentsForExecutor']);

    //dohvatanje svih korisnika
    Route::get('/returnAllUsers', [UserController::class, 'returnAllUsers']);

    //dohvatanje tipa korisnika
    Route::get('/getTypeUser/{id}', [UserController::class, 'getTypeUser']);

    //ryta za čuvanje promene tipa korisnika
    Route::post('/saveUserType', [UserController::class, 'saveUserType']);


});

