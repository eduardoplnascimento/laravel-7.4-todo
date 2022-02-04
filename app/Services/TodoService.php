<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Repositories\TodoRepository;

class TodoService
{
    /**
     * @var TodoRepository
     */
    protected $repository;

    public function __construct(TodoRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  array  $attributtes
     * @return array
     */
    public function store(array $attributtes): array
    {
        DB::beginTransaction();
        try {
            $todo = $this->repository->create($attributtes);
        } catch (\Throwable $th) {
            DB::rollback();
            logger()->error($th);
            return [
                'success' => false,
                'message' => 'Erro ao criar TODO'
            ];
        }

        DB::commit();
        return [
            'success' => true,
            'message' => 'TODO criado com sucesso',
            'data' => $todo
        ];
    }

    /**
     * Complete the specified resource in storage.
     *
     * @param  int  $id
     * @param  int  $userId
     * @return array
     */
    public function complete(int $id, int $userId)
    {
        DB::beginTransaction();
        try {
            $todo = $this->repository->find($id);

            // Verificar se TODO é do usuário
            if ($todo->user_id !== $userId) {
                DB::rollback();
                return [
                    'success' => false,
                    'message' => 'Erro ao encontrar TODO'
                ];
            }

            $this->repository->update(['is_complete' => true], $id);
        } catch (\Throwable $th) {
            DB::rollback();
            logger()->error($th);
            return [
                'success' => false,
                'message' => 'Erro ao completar TODO'
            ];
        }

        DB::commit();
        return [
            'success' => true,
            'message' => 'TODO completado com sucesso',
            'data' => $todo->fresh()
        ];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @param  int  $userId
     * @return array
     */
    public function destroy(int $id, int $userId)
    {
        DB::beginTransaction();
        try {
            $todo = $this->repository->find($id);

            // Verificar se TODO é do usuário
            if ($todo->user_id !== $userId) {
                DB::rollback();
                return [
                    'success' => false,
                    'message' => 'Erro ao encontrar TODO'
                ];
            }

            $this->repository->delete($id);
        } catch (\Throwable $th) {
            DB::rollback();
            logger()->error($th);
            return [
                'success' => false,
                'message' => 'Erro ao deletar TODO'
            ];
        }

        DB::commit();
        return [
            'success' => true,
            'message' => 'TODO deletado com sucesso'
        ];
    }
}
