# WallyAPI

WallyAPI é um framework minimalista para APIs RESTful em PHP, com foco em simplicidade, produtividade e clareza. Ele oferece roteamento flexível, sistema de middlewares, injeção de dados, autenticação JWT, manipulação de senhas e um Model robusto para acesso ao banco de dados.

---

## Estrutura do Projeto

```
www/
├── app/
│   ├── Console/         # Comandos de console (ex: make)
│   ├── Controllers/     # Controladores da aplicação
│   ├── Core/            # Núcleo do framework (Router, Model, JWT, Password, etc)
│   ├── Middleware/      # Middlewares personalizados
│   ├── Models/          # Modelos de dados
│   ├── stubs/           # Templates para geração automática
│   └── config/          # Configurações (ex: database.php)
├── example_database/    # Exemplos de banco de dados
├── routes/
│   └── api.php          # Definição das rotas da API
├── vendor/              # Dependências do Composer
├── .htaccess            # Configuração do Apache
├── composer.json        # Dependências do projeto
├── composer.lock        # Lockfile do Composer
├── ErrorHandler.php     # Manipulador global de erros
├── index.php            # Ponto de entrada da aplicação
├── make.php             # Script para geração de arquivos
├── README.md            # Documentação do projeto
├── docker-compose.yml   # Configuração Docker Compose
└── Dockerfile           # Dockerfile para build da aplicação
```

---

## Inicialização e Configuração

O ponto de entrada é o `index.php`, que:
- Carrega autoload do Composer
- Configura tratamento global de erros
- Configura CORS (origens, métodos, headers)
- Carrega as rotas definidas em `routes/api.php`

---

## Roteamento

O roteamento é feito em `routes/api.php` usando a classe `Router`.

### Exemplo de definição de rotas:
```php
$router->get('/users', 'ExampleUserController@index');
$router->get('/users/{user_id}', 'ExampleUserController@show');
$router->post('/users', 'ExampleUserController@create');
$router->put('/users/{user_id}', 'ExampleUserController@update');
$router->delete('/users/{user_id}', 'ExampleUserController@delete', ['ExampleAuthMiddleware@handle'], ['permissions' => 'users.delete']);
```

- O terceiro argumento é um array de middlewares para a rota.
- O quarto argumento é um array de dados injetados, acessível no controller.

### Middlewares globais
```php
$router->addMiddleware('/*', 'ExampleMiddleware@logs');
```

---

## Controllers

Controllers ficam em `app/Controllers/` e estendem `App\Core\Controller`.

### Parâmetros dos métodos dos controllers
Cada método recebe:
- `$data`: Todos os dados da requisição (query params, formData, arquivos, payload JSON, e parâmetros de rota como `{user_id}`)
- `$middlewareData`: Dados retornados pelos middlewares aplicados à rota
- `$injectedData`: Dados definidos na rota (ex: permissões, flags)

**Exemplo real:**
```php
public function delete($data, $middlewareData, $injectedData) {
    $authUser = $middlewareData['user'];
    if(!in_array($injectedData['permissions'], $authUser['permissions'])) {
        return $this->error([
            'message' => 'You are not allowed to delete this user'
        ], 403);
    }
    // ... resto do código
}
```

### Métodos utilitários do Controller
- `response($data, $statusCode = 200)`: Retorna resposta JSON de sucesso
- `error($message, $statusCode = 400, $data = [])`: Retorna resposta JSON de erro

---

## Models

Os Models estendem `App\Core\Model` e encapsulam a lógica de acesso ao banco.

**Exemplo de Model:**
```php
class ExampleUser extends Model {
    public function __construct() {
        parent::__construct(
            'example_users',
            ['user_email', 'user_password', 'user_name'],
            'user_id',
            true // Chave primária é UUID
        );
    }
}
```

### Métodos principais do Model
- `find($terms, $params, $columns)`: Busca registros com condições
- `findById($id, $columns)`: Busca por ID
- `fetch($all = false, $readOnly = false)`: Retorna os resultados
- `save()`: Cria ou atualiza o registro
- `destroy()`: Remove o registro
- `fail()`: Retorna o erro da última operação

**Exemplo de uso no Controller:**
```php
public function create($data, $middlewareData) {
    $user = new ExampleUser;
    $user->user_email = $data['user_email'];
    $user->user_password = Password::hash($data['user_password']);
    $user->user_name = $data['user_name'];
    if($user->save()) {
        return $this->response(['user' => $user->data], 201);
    }
    return $this->error(['message' => 'Erro', 'error' => $user->fail()], 400);
}
```

---

## Middlewares

Middlewares ficam em `app/Middleware/` e podem ser aplicados globalmente ou por rota.

- Recebem `$data` e `$injectData` como parâmetros.
- Podem retornar um array de dados que será passado para o controller em `$middlewareData`.
- Se o middleware encerrar a execução (ex: `exit`), a requisição é bloqueada.

**Exemplo de Middleware:**
```php
class ExampleAuthMiddleware {
    public function handle($data, $injectData) {
        $token = ... // extrai do header
        $payload = JWT::verify($token);
        if (!$payload) {
            http_response_code(401);
            echo json_encode(['message' => 'Token inválido']);
            exit;
        }
        $user = (new ExampleUser)->findById($payload['user_id']);
        return ['user' => $user->data];
    }
}
```

---

## Dados da Requisição ($data)

O array `$data` contém:
- Parâmetros de rota (ex: `{user_id}`)
- Query params
- Dados do corpo (JSON, formData)
- Arquivos enviados (`$data['files']`)

**Exemplo:**
```php
$user_id = $data['user_id'];
$email = $data['user_email'];
$arquivo = $data['files']['avatar'];
```

---

## JWT (Autenticação)

**Gerar token:**
```php
$token = JWT::generate(['user_id' => $user->user_id], 3600);
```

**Verificar token:**
```php
$payload = JWT::verify($token);
if (!$payload) { /* inválido */ }
```

---

## Password (Senhas)

**Gerar hash:**
```php
$hash = Password::hash($senha);
```

**Verificar senha:**
```php
if (Password::verify($senha, $hash)) { /* ok */ }
```

---

## Exemplo de fluxo completo de rota protegida

```php
$router->delete(
    '/users/{user_id}',
    'ExampleUserController@delete',
    ['ExampleAuthMiddleware@handle'],
    ['permissions' => 'users.delete']
);
```
No controller:
```php
public function delete($data, $middlewareData, $injectedData) {
    $authUser = $middlewareData['user'];
    if(!in_array($injectedData['permissions'], $authUser['permissions'])) {
        return $this->error(['message' => 'You are not allowed'], 403);
    }
    $user = (new ExampleUser)->findById($data['user_id']);
    if(!$user) return $this->error(['message' => 'Not found'], 404);
    if($user->destroy()) {
        return $this->response(['message' => 'User deleted']);
    }
    return $this->error(['message' => 'Error', 'error' => $user->fail()], 400);
}
```

---

## Resumo

- **Router**: Define rotas, middlewares e dados injetados.
- **Controllers**: Recebem `$data`, `$middlewareData`, `$injectedData`.
- **Middlewares**: Podem bloquear ou injetar dados para o controller.
- **Models**: CRUD e queries com métodos simples.
- **JWT e Password**: Segurança pronta para uso.
- **$data**: Sempre contém tudo da requisição, inclusive arquivos.

---

## Instruções para Rodar 

## Instalar com NPX 
```shell 
npx create-wallyapi project-name
- instruções no console...
```

## Instalar com Git Clone 

```shell
git clone https://github.com/luizservelo/wallyapi project-name
cd project-name 
docker compose -f 'docker-compose.yml' up -d --build 
cd www && composer install
```

### Localhost 

```shell 
cd project-name 
docker compose -f 'docker-compose.yml' up -d --build 
cd www && composer install
```

WallyAPI: Simples, direto e eficiente para APIs RESTful em PHP.
