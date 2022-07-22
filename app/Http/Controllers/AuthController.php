<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Mostra página de autenticação de usuário
     *
     * @return view
     */
    public function login()
    {
        return view('login');
    }

    /**
     * Mostra página de registro de usuário
     *
     * @return view
     */
    public function register()
    {
        return view('register');
    }

    /**
     * Executa a autenticação do usuário
     *
     * @param Request $request
     *
     * @return redirect
     */
    public function signin(Request $request)
    {
        try {
            if (Auth::attempt($request->only('email', 'password'), $request->remember)) {
                return redirect('/dashboard');
            }
        } catch (\Throwable $th) {
            logger()->error($th);
            return back()->with('error', 'Erro ao autenticar usuário');
        }

        return back()->with('error', 'Credenciais não encontradas');
    }

    /**
     * Executa o registro do usuário
     *
     * @param Request $request
     *
     * @return redirect
     */
    public function signup(Request $request)
    {
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password)
            ]);

            Auth::login($user);
        } catch (\Throwable $th) {
            logger()->error($th);
            return back()->with('error', 'Erro ao criar usuário');
        }

        return redirect('/dashboard');
    }

    /**
     * Executa o logout do usuário
     *
     * @param Request $request
     *
     * @return redirect
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
