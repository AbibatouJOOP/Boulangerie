<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\services\UserService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected $userService;
    public function __construct()
    {
        $this->userService=new UserService();
    }
    public function index()
    {
        $users=$this->userService->index();
        return response()->json($users,200);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomComplet' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:5',
            'role' => 'in:ADMIN,EMPLOYE,CLIENT'
        ]);
        $user = User::create([
            'nomComplet' => $validated['nomComplet'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => strtoupper($request->role ?? 'CLIENT')
        ]);
     return response()->json($user,201,[], JSON_UNESCAPED_UNICODE);
    }
    // Enregistrement
    public function register(Request $request)
    {
        $validated = $request->validate([
            'nomComplet' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:5',
            'role' => 'in:ADMIN,EMPLOYE,CLIENT'
        ]);
        $user = User::create([
            'nomComplet' => $validated['nomComplet'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => strtoupper($request->role ?? 'CLIENT')
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
// Connexion
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        $user = User::where('email', $request->email)->first();
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les informations sont incorrectes.']
            ]);
        }
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
// Déconnexion
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json([
            'message' => 'Déconnecté avec succès'
        ]);
    }
    public function show(string $id)
    {
        $user = $this->userService->show($id);
        return response()->json($user,200,[], JSON_UNESCAPED_UNICODE);
    }
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'nomComplet' => 'required|string|max:255',
            'email' => 'sometimes|email|unique:users,email,'.$id,
            'password' => 'required|string|min:5',
            'role' => 'in:ADMIN,EMPLOYE,CLIENT'
        ]);
        $user= $this->userService->update($validated, $id);

        return response()->json([
            "message" => "user mise à jour",
            "user" => $user
        ],status: 201);
    }
     public function destroy(string $id)
    {
        $this->userService->destroy($id);
        return response()->json("",204);
    }
}
