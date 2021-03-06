<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use \App\Models\Image;
use \App\Models\User;

class UsersController extends Controller
{
    public function index()
    {
        return User::all();
    }

    public function show(User $user)
    {
        return $user;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|min:3',
            'email' => 'required|unique:users',
            'phone' => 'required|size:14',
            'address' => 'required:min:9',
            'zip' => 'required|size:5',
        ]);

        DB::beginTransaction();

        try {
            $user = User::create($request->all());

            if($request->hasFile('images'))
            {
                foreach ($request->file('images') as $image) {
                    $name = $image->getClientOriginalName();
                    $path = public_path() . '/uploads/images/' . $user->id . "/";
                    $image->move($path, $name);
                    Image::create([
                        'user_id' => $user->id,
                        'value' => $path . $name,
                    ]);
                }
            }
            
            DB::commit();

            return response()->json($user, 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(null, 500);
        }
    }

    public function update(Request $request, User $user)
    {
        $user->update($request->all());

        return response()->json($user, 200);
    }

    public function delete(User $user)
    {
        $user->delete();

        return response()->json(null, 204);
    }
}
