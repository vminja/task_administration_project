<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username', 'password', 'full_name', 'phone', 'email', 'birth_date', 'type', 'is_active',  'activation_token', 'activation_expires_at'
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
       protected $hidden = ['password', 'remember_token'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function returnAllUsers($request)
    {
        $start = isset($request['start']) ? $request['start'] : 0;
        $length = isset($request['length']) ? $request['length'] : 0;
        $sort = 'users.email';
        $sorting = 'asc';
        $search = isset($request['search']['value']) ? $request['search']['value'] : 0;

        if (isset($request['order'][0]['column'])) {
            switch ($request['order'][0]['column']) {
                case '0':
                    $sort = 'users.full_name';
                    break;
                case '1':
                    $sort = 'users.email';
                    break;
                case '2':
                    $sort = 'users.username';
                    break;
            }
        }

        if (isset($request['order'][0]['dir'])) {
            $sorting = $request['order'][0]['dir'];
        }

        $query = DB::table('users')->select('*');
        $query->orderBy($sort, $sorting);

        if (!empty($search)) {
            $query = $query->whereRaw("full_name LIKE '%{$search}%' OR email LIKE '%{$search}%' OR username LIKE '%{$search}%' ");
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
