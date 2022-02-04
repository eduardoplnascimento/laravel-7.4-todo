@extends('layouts.app')

@section('title', '| Dashboard')

@section('content')
    <a href="{{ route('logout') }}" class="position-absolute top-0 end-0 link-secondary p-3">
        <i class="bi bi-box-arrow-right fs-3"></i>
    </a>
    <div class="min-vh-100 d-flex justify-content-center align-items-center">
        <div class="shadow-lg p-3 bg-dark text-white rounded p-5" style="width: 850px; min-height: 300px;">
            <h1>Taefras</h1>
            <small class="text-info">{{ $todos->count() }} ativas</small>
            <form action="/todos" method="post" class="mt-3">
                @csrf
                <div class="input-group">
                    <div class="input-group-text bg-white p-0">
                        <input type="color" class="form-control form-control-color border-0" name="color" title="Escolha uma cor">
                    </div>
                    <input type="text" class="form-control" name="title" placeholder="O que fazer?" required>
                    <button type="submit" class="btn btn-secondary">
                        <i class="bi bi-plus fs-4"></i>
                    </button>
                </div>
            </form>
            <hr>
            <ul class="list-group list-group-flush">
                @foreach ($todos as $todo)
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent text-white">
                        <div class="d-flex align-items-center">
                            <span class="badge rounded-pill me-2" style="background: {{ $todo->color ?? '#FFFFFF' }}">&nbsp;</span>
                            @if ($todo->is_complete)
                                <del>{{ $todo->title }}</del>
                            @else
                                {{ $todo->title }}
                            @endif
                        </div>
                        <div class="d-flex align-items-center">
                            <a href="/todos/{{ $todo->id }}/edit" class="text-light">
                                <i class="bi bi-pencil fs-4"></i>
                            </a>
                            @if (!$todo->is_complete)
                                <a href="/todos/{{ $todo->id }}/complete" class="text-light ms-2">
                                    <i class="bi bi-check2-square fs-4"></i>
                                </a>
                            @endif
                            <form action="/todos" method="post" class="ms-2">
                                @csrf
                                @method('delete')
                                <button type="submit" style="all: unset; cursor: pointer;">
                                    <i class="bi bi-x-octagon fs-4"></i>
                                </button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endsection
