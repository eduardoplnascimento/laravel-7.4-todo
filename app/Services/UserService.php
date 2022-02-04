<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;

class UserService
{
    /**
     * @var UserRepository
     */
    protected $repository;

    /**
     * UserService constructor.
     *
     * @param UserRepository $repository
     */
    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Autenticação de um usuário
     *
     * @param string $email
     * @param string $password
     * @param bool $remember
     *
     * @return array
     */
    public function signin(string $email, string $password, bool $remember)
    {
        try {
            if (Auth::attempt(['email' => $email, 'password' => $password], $remember)) {
                return [
                    'success' => true,
                    'message' => 'Usuário autenticado com sucesso'
                ];
            }
        } catch (\Throwable $th) {
            logger()->error($th);
            return [
                'success' => false,
                'message' => 'Erro ao autenticar usuário'
            ];
        }

        return [
            'success' => false,
            'message' => 'Credenciais não encontradas'
        ];
    }

    /**
     * Criação e autenticação de um usuário
     *
     * @param string $name
     * @param string $email
     * @param string $password
     *
     * @return array
     */
    public function signup(string $name, string $email, string $password)
    {
        DB::beginTransaction();
        try {
            $user = $this->repository->create([
                'name' => $name,
                'email' => $email,
                'password' => $password
            ]);

            Auth::login($user);
        } catch (\Throwable $th) {
            DB::rollback();
            logger()->error($th);
            return [
                'success' => false,
                'message' => 'Erro ao criar usuário'
            ];
        }

        DB::commit();
        return [
            'success' => true,
            'message' => 'Usuário criado e autenticado com sucesso'
        ];
    }
}
