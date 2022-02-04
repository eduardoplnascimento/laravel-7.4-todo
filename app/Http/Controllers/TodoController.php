<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use App\Services\TodoService;
use App\Repositories\TodoRepository;
use App\Http\Requests\StoreTodoRequest;

class TodoController extends Controller
{
    /**
     * @var TodoRepository
     */
    protected $repository;

    /**
     * @var TodoService
     */
    protected $service;

    public function __construct(TodoRepository $repository, TodoService $service)
    {
        $this->repository = $repository;
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = auth()->user();

        $todos = $this->repository->findWhere(['user_id' => $user->id]);

        return view('dashboard', compact('user', 'todos'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreTodoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTodoRequest $request)
    {
        $user = auth()->user();

        $attributes = $request->only([
            'title',
            'description',
            'color'
        ]);

        $attributes['user_id'] = $user->id;

        $response = $this->service->store($attributes);
        if (!$response['success']) {
            return redirect('/todos/create')->with('error', $response['message']);
        }

        return redirect('/dashboard')->with('success', $response['message']);
    }

    /**
     * Complete the specified resource in storage.
     *
     * @param  \App\Models\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function complete(Todo $todo)
    {
        $user = auth()->user();

        $response = $this->service->complete($todo->id, $user->id);
        
        return redirect('/dashboard')->with(
            $response['success'] ? 'success' : 'error',
            $response['message']
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Todo $todo)
    {
        $response = $this->service->destroy($todo->id, $user->id);

        return redirect('/dashboard')->with(
            $response['success'] ? 'success' : 'error',
            $response['message']
        );
    }
}
