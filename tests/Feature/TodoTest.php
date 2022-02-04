<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Todo;
use App\Models\User;
use Faker\Generator;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TodoTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    /**
     * Fornecer dados prontos para os testes
     * 
     * @param int $numUsers
     * @param int $numTodos
     * 
     * @return array
     */
    public function dataProvider(int $numUsers = 1, int $numTodos = 1) {
        $faker = app(Generator::class);

        $users = [];
        $todos = [];
        for ($i = 0; $i < $numUsers; $i++) {
            // Criar usuário
            $user = User::factory()->create();

            $todos[$user->id] = [];

            // Criar todos
            for ($j = 0; $j < $numTodos; $j++) {
                $todos[$user->id][] = Todo::factory()->create(['user_id' => $user->id]);
            }

            $users[] = $user;
        }

        return compact('users', 'todos');
    }

    /**
     * Teste não deve entrar na página dashboard sem autenticação
     *
     * @return void
     */
    public function testShouldNotOpenDashboardWhenUserUnauthorized()
    {
        // Acessar a rota /dashboard
        $response = $this->get('/dashboard');

        // Verificar se usuário foi redirecionado para o login
        $response->assertRedirect('/');
    }

    /**
     * Teste usuários não deve ver todos de outros usuários
     *
     * @return void
     */
    public function testUserShouldNotSeeTodosFromOtherUsers()
    {
        // Criar dados falsos
        $data = $this->dataProvider(2);
        
        // Pegar dados do primeiro usuário
        $user = $data['users'][0];
        $todo = $data['todos'][$user->id][0];
        $this->actingAs($user);

        // Pegar dados do segundo usuário
        $user2 = $data['users'][1];
        $todo2 = $data['todos'][$user2->id][0];

        // Acessar a rota /dashboard
        $response = $this->get('/dashboard');

        // Verificar se aparece o título do TODO do usuário
        $response->assertSee($todo->title);

        // Verificar se não aparece o título do TODO do outro usuário
        $response->assertDontSee($todo2->title);
    }

    /**
     * Teste deve abrir dashboard e ver a palavra 'Tarefas'
     *
     * @return void
     */
    public function testOpenDashboardAndSeeTarefas()
    {
        // Criar dados falsos
        $data = $this->dataProvider(1, 3);
        
        // Pegar dados do primeiro usuário
        $user = $data['users'][0];
        $this->actingAs($user);

        // Acessar a rota /dashboard
        $response = $this->get('/dashboard');

        // Verificar se aparece a frase 'Tarefas'
        $response->assertSee('Tarefas');
    }

    /**
     * Teste deve abrir dashboard e ver quantas tarefas estão ativas
     *
     * @return void
     */
    public function testOpenDashboardAndSeeActiveTodos()
    {
        // Criar dados falsos
        $data = $this->dataProvider(1, 3);
        
        // Pegar dados do primeiro usuário
        $user = $data['users'][0];
        $this->actingAs($user);

        // Completar primeiro todo
        $todo = $data['todos'][$user->id][0];
        $todo->is_complete = true;
        $todo->save();

        // Acessar a rota /dashboard
        $response = $this->get('/dashboard');

        // Verificar se aparece a frase '2 ativas'
        $response->assertSee('2 ativas');
    }

    /**
     * Teste deve armazenar um TODO com todos os campos corretos
     *
     * @return void
     */
    public function testShouldStoreTodo()
    {
        // Criar dados falsos
        $data = $this->dataProvider(1, 0);

        // Pegar dados do usuário
        $user = $data['users'][0];
        $this->actingAs($user);

        // Criar dados para a requisição
        $input = [
            'title' => $this->faker->sentence(2),
            'color' => $this->faker->hexColor()
        ];

        // Acessar rota de criação de TODOs
        $this->post('/todos', $input);

        // Verificar se TODO foi criado no banco de dados
        $this->assertDatabaseHas('todos', [
            'title' => $input['title'],
            'color' => $input['color'],
            'user_id' => $user->id
        ]);
    }

    /**
     * Teste não deve armazenar um TODO sem o título
     *
     * @return void
     */
    public function testShouldNotStoreTodoWithoutTitle()
    {
        // Criar dados falsos
        $data = $this->dataProvider(1, 0);

        // Pegar dados do usuário
        $user = $data['users'][0];
        $this->actingAs($user);

        // Criar dados para a requisição
        $input = [
            'color' => $this->faker->hexColor()
        ];

        // Acessar rota de criação de TODOs
        $response = $this->post('/todos', $input);

        // Verificar frase de erro
        $response->assertSessionHasErrors(['title' => 'Um título é obrigatório']);

        // Verificar se todo não foi criado no banco de dados
        $this->assertDatabaseMissing('todos', [
            'color' => $input['color'],
            'user_id' => $user->id
        ]);
    }

    /**
     * Teste deve completar um TODO
     *
     * @return void
     */
    public function testShouldCompleteTodo()
    {
        // Criar dados falsos
        $data = $this->dataProvider();

        // Pegar dados do usuário
        $user = $data['users'][0];
        $todo = $data['todos'][$user->id][0];
        $this->actingAs($user);

        // Acessar rota completar TODOs
        $this->get('/todos/' . $todo->id . '/complete');

        // Verificar se TODO foi completado no banco de dados
        $this->assertDatabaseHas('todos', [
            'id' => $todo->id,
            'is_complete' => true
        ]);
    }

    /**
     * Teste deve remover um TODO
     *
     * @return void
     */
    public function testShouldDeleteTodo()
    {
        // Criar dados falsos
        $data = $this->dataProvider();

        // Pegar dados do usuário
        $user = $data['users'][0];
        $todo = $data['todos'][$user->id][0];
        $this->actingAs($user);

        // Acessar rota completar TODOs
        $this->delete('/todos/' . $todo->id);

        // Verificar se TODO foi removido no banco de dados
        $this->assertDatabaseMissing('todos', [
            'id' => $todo->id
        ]);
    }

    /**
     * Teste deve acessar página de edição
     *
     * @return void
     */
    public function testShouldOpenEditPage()
    {
        // Criar dados falsos
        $data = $this->dataProvider(2);
        
        // Pegar dados do usuário
        $user = $data['users'][0];
        $todo = $data['todos'][$user->id][0];
        $this->actingAs($user);

        // Acessar a rota /todos/{todo}/edit
        $response = $this->get('/todos/' . $todo->id . '/edit');

        // Verificar se aparece a frase 'Editar Tarefa'
        $response->assertSee('Editar Tarefa');

        // Verificar se aparece o título do TODO a ser editado
        $response->assertSee($todo->title);
    }

    /**
     * Teste usuários não deve ver página de edição para TODOs de outros usuários
     *
     * @return void
     */
    public function testUserShouldNotEditTodosFromOtherUsers()
    {
        // Criar dados falsos
        $data = $this->dataProvider(2);
        
        // Pegar dados do primeiro usuário
        $user = $data['users'][0];
        $this->actingAs($user);

        // Pegar dados do segundo usuário
        $user2 = $data['users'][1];
        $todo2 = $data['todos'][$user2->id][0];

        // Acessar a rota /todos/{todo}/edit
        $response = $this->get('/todos/' . $todo2->id . '/edit');

        // Verificar se página é 404
        $response->assertNotFound();
    }

    /**
     * Teste deve editar um TODO com todos os campos corretos
     *
     * @return void
     */
    public function testShouldUpdateTodo()
    {
        // Criar dados falsos
        $data = $this->dataProvider();

        // Pegar dados do usuário
        $user = $data['users'][0];
        $todo = $data['todos'][$user->id][0];
        $this->actingAs($user);

        // Criar dados para a requisição
        $input = [
            'title' => $this->faker->sentence(2),
            'color' => $this->faker->hexColor()
        ];

        // Acessar rota de edição de TODOs
        $this->put('/todos/' . $todo->id, $input);

        // Verificar se TODO foi editado no banco de dados
        $this->assertDatabaseHas('todos', [
            'id' => $todo->id,
            'title' => $input['title'],
            'color' => $input['color']
        ]);
    }

    /**
     * Teste não deve editar um TODO quando o usuário não está autorizado
     *
     * @return void
     */
    public function testShouldNotUpdateTodoWhenUserUnauthorized()
    {
        // Criar dados falsos
        $data = $this->dataProvider(2);

        // Pegar dados do primeiro usuário
        $user = $data['users'][0];
        $todo = $data['todos'][$user->id][0];
        
        // Pegar dados do segundo usuário
        $user2 = $data['users'][1];
        $this->actingAs($user2);

        // Criar dados para a requisição
        $input = [
            'title' => $this->faker->sentence(2),
            'color' => $this->faker->hexColor()
        ];

        // Acessar rota de edição de TODOs
        $response = $this->put('/todos/' . $todo->id, $input);

        // Verificar se requisição foi proíbida
        $response->assertForbidden();

        // Verificar se todo não foi editado
        $this->assertDatabaseMissing('todos', [
            'id' => $todo->id,
            'title' => $input['title'],
            'color' => $input['color']
        ]);
    }
}
