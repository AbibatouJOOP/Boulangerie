<?php

namespace App\services;

use App\Models\User;
use Illuminate\Http\Request;

class UserService
{

    public function index()
    {
        $users = User::all();
        return $users;
    }
    public function store(array $request)
    {
        //Metier
        $user = User::create($request);
        return $user;
    }


    public function show($id)
    {
        //User::find($id);
        $user = User::findOrFail($id);
        return $user;
    }


    public function update(array $request, $id)
    {
        $user = User::findOrFail($id);
        $user->update($request);
        return $user;
    }


    public function destroy($id)
    {
        User::destroy($id);
    }

}
