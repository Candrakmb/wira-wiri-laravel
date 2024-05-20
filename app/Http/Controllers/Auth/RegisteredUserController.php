<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str; // Tambahkan ini
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'no_whatsapp' => ['required', 'regex:/^(?:\+62|0)\d{9,12}$/'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = new User();
        $user->id = (string) Str::uuid(); // Menggunakan UUID sebagai ID
        $user->name = $request->name;
        $user->no_whatsapp = $request->no_whatsapp;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);

        $user->save();

        $user->assignRole('user');

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('dashboard-user');
    }
}
