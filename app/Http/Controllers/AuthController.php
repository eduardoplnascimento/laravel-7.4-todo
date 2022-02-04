<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * @var UserService
     */
    protected $service;

    /**
     * UserController constructor.
     *
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

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
        $response = $this->userService->signin(
            $request->email,
            $request->password,
            $request->remember ?: false
        );

        if (!$response['success']) {
            return back()->withError($response['message']);
        }

        return redirect('/dashboard');
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
        $response = $this->userService->signup(
            $request->name,
            $request->email,
            bcrypt($request->password)
        );

        if (!$response['success']) {
            return back()->withError($response['message']);
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
