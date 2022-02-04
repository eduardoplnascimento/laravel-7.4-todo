@extends('layouts.app')

@section('title', '| Login')

@section('content')
    <div class="min-vh-100 d-flex justify-content-center align-items-center">
        <form class="mw-100" action="{{ route('signin') }}" method="post" style="width: 400px;">
            @csrf

            <h1 class="mb-5 text-secondary text-center">Login</h1>

            <div class="mb-3">
                <input class="form-control" type="email" name="email" placeholder="E-mail" required>
            </div>

            <div class="mb-3">
                <input class="form-control" type="password" name="password" placeholder="Senha" required>
            </div>
            
            <div class="mb-3 form-check">
                <input name="remember" type="checkbox" class="form-check-input" id="remember">
                <label class="form-check-label" for="remember">Lembrar</label>
            </div>

            <div class="d-grid gap-2">
                <button class="btn btn-outline-dark" type="submit">Entrar</button>
                <a class="link-secondary" href="{{ route('register') }}">Registrar</a>
            </div>
        </form>
    </div>
@endsection
