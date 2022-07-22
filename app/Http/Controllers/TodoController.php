<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use App\Services\TodoService;
use App\Repositories\TodoRepository;
use App\Http\Requests\StoreTodoRequest;
use Illuminate\Http\Request;

class TodoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = auth()->user();

        $todos = Todo::where('user_id', $user->id)->get();

        return view('dashboard', compact('user', 'todos'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $user = auth()->user();

            $attributes = $request->only([
                'title',
                'description',
                'color'
            ]);

            $attributes['user_id'] = $user->id;

            $todo = Todo::create($attributes);
        } catch (\Throwable $th) {
            logger()->error($th);
            return redirect('/todos/create')->with('error', 'Erro ao criar TODO');
        }

        return redirect('/dashboard')->with('success', 'TODO criado com sucesso');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function edit(Todo $todo)
    {
        $user = auth()->user();
        if ($todo->user_id !== $user->id) {
            abort(404);
        }

        return view('todos.edit', compact('user', 'todo'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Request  $request
     * @param  \App\Models\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Todo $todo)
    {
        try {
            $user = auth()->user();

            // Verificar se TODO é do usuário
            if ($todo->user_id !== $user->id) {
                return response('', 403);
            }
    
            $attributes = $request->only([
                'title',
                'description',
                'color'
            ]);

            $todo->update($attributes);
        } catch (\Throwable $th) {
            logger()->error($th);
            return redirect('/todos/edit/' . $todo->id)->with('error', 'Erro ao editar TODO');
        }

        return redirect('/dashboard')->with('success', 'TODO editado com sucesso');
    }

    /**
     * Complete the specified resource in storage.
     *
     * @param  \App\Models\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function complete(Todo $todo)
    {
        try {
            $user = auth()->user();

            // Verificar se TODO é do usuário
            if ($todo->user_id !== $user->id) {
                return response('', 403);
            }

            $todo->update(['is_complete' => true]);
        } catch (\Throwable $th) {
            logger()->error($th);
            return redirect('/dashboard')->with('error', 'Erro ao completar TODO');
        }

        return redirect('/dashboard')->with('success', 'TODO completado com sucesso');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Todo $todo)
    {
        try {
            $user = auth()->user();

            // Verificar se TODO é do usuário
            if ($todo->user_id !== $user->id) {
                return response('', 403);
            }

            $todo->delete();
        } catch (\Throwable $th) {
            logger()->error($th);
            return redirect('/dashboard')->with('error', 'Erro ao deletar TODO');
        }

        return redirect('/dashboard')->with('success', 'TODO deletado com sucesso');
    }
}
