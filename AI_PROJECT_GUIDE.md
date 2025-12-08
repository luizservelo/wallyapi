# 🤖 Guia Estrutural do WallyAPI para IAs

> **Documento de referência técnica completa para assistentes de IA trabalharem com o projeto WallyAPI**
>
> **Versão:** 1.0  
> **Última atualização:** Dezembro 2025

---

## 📋 Índice

1. [Visão Geral do Projeto](#-visão-geral-do-projeto)
2. [Arquitetura do Sistema](#-arquitetura-do-sistema)
3. [Estrutura de Diretórios](#-estrutura-de-diretórios)
4. [Componentes Core](#-componentes-core)
5. [Fluxo de Requisição](#-fluxo-de-requisição)
6. [Sistema de Roteamento](#-sistema-de-roteamento)
7. [Controllers](#-controllers)
8. [Models](#-models)
9. [Middlewares](#-middlewares)
10. [Autenticação e Segurança](#-autenticação-e-segurança)
11. [Banco de Dados](#-banco-de-dados)
12. [CLI - Geração de Código](#-cli---geração-de-código)
13. [Ambiente Docker](#-ambiente-docker)
14. [Convenções e Padrões](#-convenções-e-padrões)
15. [Tratamento de Erros](#-tratamento-de-erros)
16. [Exemplos Práticos](#-exemplos-práticos)

---

## 🎯 Visão Geral do Projeto

### Identidade do Projeto
- **Nome:** WallyAPI
- **Tipo:** Framework PHP minimalista para APIs RESTful
- **Versão PHP:** 8.2+
- **Paradigma:** MVC (Model-View-Controller) sem camada de View
- **Propósito:** Criar APIs RESTful de forma simples, rápida e produtiva

### Filosofia
- **Minimalismo:** Apenas o essencial, sem dependências desnecessárias
- **Clareza:** Código limpo, legível e autodocumentado
- **Produtividade:** CLI para gerar código automaticamente
- **Segurança:** JWT, Password hashing com ARGON2ID/BCRYPT
- **Flexibilidade:** Sistema de middlewares e injeção de dados

### Stack Tecnológica
- **Backend:** PHP 8.2 com Apache
- **Banco de Dados:** MariaDB (compatível com MySQL)
- **Containerização:** Docker + Docker Compose
- **Gerenciamento de Dependências:** Composer
- **Administração DB:** PHPMyAdmin

---

## 🏗️ Arquitetura do Sistema

### Padrão Arquitetural

```
┌─────────────────────────────────────────────────────────────┐
│                      REQUEST (HTTP)                          │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│                    index.php (Entry Point)                   │
│  • Autoload (Composer)                                       │
│  • ErrorHandler (Global)                                     │
│  • CORS Configuration                                        │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│                    routes/api.php                            │
│  • Define todas as rotas                                     │
│  • Associa rotas a Controllers                               │
│  • Define Middlewares globais e por rota                     │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│                 Router::dispatch()                           │
│  1. Match route path e HTTP method                           │
│  2. Extrai parâmetros de rota ({id}, {slug}, etc)           │
│  3. Coleta dados da requisição (GET, POST, JSON, FILES)     │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│              Middlewares (Global + Route)                    │
│  • Executa na ordem: Global → Route Specific                 │
│  • Pode retornar dados para $middlewareData                  │
│  • Pode bloquear requisição (exit)                           │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│                    Controller                                │
│  • Recebe $data, $middlewareData, $injectedData             │
│  • Executa lógica de negócio                                 │
│  • Interage com Models                                       │
│  • Retorna response() ou error()                             │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│                       Model (opcional)                        │
│  • Executa queries no banco via PDO                          │
│  • CRUD operations (save, destroy, find, findById)          │
│  • Retorna dados ou false em caso de erro                    │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│                    RESPONSE (JSON)                           │
│  • HTTP Status Code                                          │
│  • Content-Type: application/json                            │
│  • Body: {"status": "success|error", ...}                   │
└─────────────────────────────────────────────────────────────┘
```

---

## 📁 Estrutura de Diretórios

```
wallyapi/
│
├── datadir/                    # Dados do MariaDB (volume Docker)
│   └── database/               # Database criado automaticamente
│
├── www/                        # Raiz da aplicação
│   │
│   ├── app/                    # Código da aplicação
│   │   │
│   │   ├── Console/            # Comandos CLI
│   │   │   └── Commands/
│   │   │       └── Make.php    # Comando para gerar código
│   │   │
│   │   ├── Controllers/        # Controllers da API
│   │   │   ├── ExampleController.php
│   │   │   ├── ExampleUserController.php
│   │   │   └── ExampleAuthController.php
│   │   │
│   │   ├── Core/               # Núcleo do framework
│   │   │   ├── Connect.php     # Singleton para conexão PDO
│   │   │   ├── Controller.php  # Base Controller (abstract)
│   │   │   ├── CORS.php        # Gerenciamento de CORS
│   │   │   ├── JWT.php         # Autenticação JWT
│   │   │   ├── Model.php       # Base Model (abstract)
│   │   │   ├── Password.php    # Hash e verificação de senhas
│   │   │   └── Router.php      # Sistema de roteamento
│   │   │
│   │   ├── Middleware/         # Middlewares customizados
│   │   │   ├── ExampleMiddleware.php
│   │   │   └── ExampleAuthMiddleware.php
│   │   │
│   │   ├── Models/             # Models do banco de dados
│   │   │   └── ExampleUser.php
│   │   │
│   │   └── stubs/              # Templates para CLI
│   │       ├── controller.stub
│   │       ├── middleware.stub
│   │       └── model.stub
│   │
│   ├── config/                 # Configurações
│   │   └── database.php        # Config do banco de dados
│   │
│   ├── example_database/       # Scripts SQL de exemplo
│   │   └── example.sql
│   │
│   ├── routes/                 # Definição de rotas
│   │   └── api.php             # Arquivo principal de rotas
│   │
│   ├── vendor/                 # Dependências do Composer
│   │
│   ├── .htaccess               # Rewrite rules do Apache
│   ├── composer.json           # Definição de dependências
│   ├── composer.lock           # Lock de versões
│   ├── ErrorHandler.php        # Handler global de erros
│   ├── index.php               # Entry point da aplicação
│   └── make.php                # CLI para geração de código
│
├── docker-compose.yml          # Orquestração dos containers
├── Dockerfile                  # Build da imagem PHP
└── README.md                   # Documentação do usuário
```

### Propósito de Cada Diretório

| Diretório | Propósito | Modificável? |
|-----------|-----------|--------------|
| `app/Console/` | Comandos CLI customizados | ✅ Sim |
| `app/Controllers/` | Lógica de controle das rotas | ✅ Sim |
| `app/Core/` | Núcleo do framework | ⚠️ Cuidado |
| `app/Middleware/` | Middlewares da aplicação | ✅ Sim |
| `app/Models/` | Entidades do banco de dados | ✅ Sim |
| `app/stubs/` | Templates para geração de código | ✅ Sim |
| `config/` | Arquivos de configuração | ✅ Sim |
| `routes/` | Definição de rotas da API | ✅ Sim |
| `vendor/` | Dependências do Composer | ❌ Não |

---

## ⚙️ Componentes Core

### 1. Connect.php - Gerenciador de Conexão

**Padrão:** Singleton  
**Responsabilidade:** Fornecer conexão PDO única e reutilizável

```php
// Uso
$db = Connect::getInstance()->getConnection();
```

**Características:**
- Singleton pattern (apenas uma instância)
- Lazy loading (conecta apenas quando necessário)
- Configurações PDO otimizadas:
  - `PDO::ERRMODE_EXCEPTION`: Lança exceções em erros
  - `PDO::FETCH_OBJ`: Retorna objetos por padrão
  - `PDO::EMULATE_PREPARES = false`: Prepared statements nativos

**Configuração:**
- Lê de `config/database.php`
- Suporta apenas MySQL/MariaDB no momento

### 2. Controller.php - Base Controller

**Padrão:** Abstract Class  
**Responsabilidade:** Base para todos os controllers

**Propriedades:**
```php
protected PDO $db;              // Conexão com banco
```

**Métodos Principais:**

#### `response(array $data = [], int $statusCode = 200): never`
Retorna resposta JSON de sucesso e encerra execução.

```php
return $this->response([
    'message' => 'Success',
    'data' => $result
], 201);
```

**Output:**
```json
{
    "status": "success",
    "message": "Success",
    "data": {...}
}
```

#### `error(array $data = [], int $statusCode = 400): never`
Retorna resposta JSON de erro e encerra execução.

```php
return $this->error([
    'message' => 'Validation failed',
    'errors' => $validationErrors
], 422);
```

**Output:**
```json
{
    "status": "error",
    "message": "Validation failed",
    "errors": [...]
}
```

#### `getRequestData(): array`
Coleta e unifica todos os dados da requisição.

**Fontes de dados (ordem de prioridade):**
1. `$_POST` (form-data, x-www-form-urlencoded)
2. `php://input` (JSON body)
3. Query string (`$_GET`)
4. `$_FILES` (uploads)

```php
$data = $this->getRequestData();
// Contém: POST + JSON + Query + Files + Route Params
```

**Tratamento de arquivos:**
- Arquivo único: `$data['files']['avatar']`
- Múltiplos arquivos: `$data['files']['photos'][0]`, `[1]`, etc.

### 3. Router.php - Sistema de Roteamento

**Responsabilidade:** Gerenciar rotas, middlewares e dispatch

**Métodos de Rota:**
```php
$router->get($path, $handler, $middlewares, $injectedData);
$router->post($path, $handler, $middlewares, $injectedData);
$router->put($path, $handler, $middlewares, $injectedData);
$router->delete($path, $handler, $middlewares, $injectedData);
```

**Parâmetros:**
- `$path`: String com a rota (suporta `{param}`)
- `$handler`: `'Controller@method'`
- `$middlewares`: Array de middlewares `['Middleware@method']`
- `$injectedData`: Array de dados extras `['key' => 'value']`

**Middlewares Globais:**
```php
$router->addMiddleware('/*', 'LogMiddleware@log');
$router->addMiddleware('/admin/*', 'AdminMiddleware@check');
```

**Matching de Path:**
- `/users` → Match exato
- `/users/{id}` → Captura `$data['id']`
- `/posts/{slug}/comments/{comment_id}` → Múltiplos parâmetros

**Processo de Dispatch:**
1. Match HTTP method + path
2. Extrai parâmetros de rota
3. Coleta dados da requisição
4. Executa middlewares globais
5. Executa middlewares da rota
6. Instancia controller
7. Chama método do controller
8. Retorna resposta

### 4. Model.php - Base Model

**Padrão:** Active Record  
**Responsabilidade:** Abstração de acesso ao banco

**Propriedades:**
```php
protected string $entity;        // Nome da tabela
protected string $primary;       // Chave primária (padrão: 'id')
protected array $required;       // Campos obrigatórios
protected bool $isUuid;          // Se PK é UUID
public object $data;             // Dados do registro
```

**Construtor:**
```php
parent::__construct(
    'table_name',           // Nome da tabela
    ['field1', 'field2'],   // Campos obrigatórios
    'primary_key',          // Nome da PK (padrão: 'id')
    false                   // true se PK for UUID
);
```

**Métodos de Query:**

#### `find(?string $terms, ?string $params, string $columns = "*"): static`
Constrói query de busca.

```php
$user = (new User)
    ->find('email = :email AND active = :active', 'email=john@example.com&active=1')
    ->fetch();
```

#### `findById($id, string $columns = "*"): ?static`
Busca por ID.

```php
$user = (new User)->findById(1, 'id, name, email');
```

#### `fetch(bool $all = false, bool $readOnly = false): array|static|null`
Executa query e retorna resultado.

```php
// Retorna um objeto Model
$user = (new User)->find('id = :id', 'id=1')->fetch();

// Retorna array de objetos Model
$users = (new User)->find()->fetch(true);

// Retorna array associativo simples
$users = (new User)->find()->fetch(true, true);
```

#### `save(): bool`
Cria (INSERT) ou atualiza (UPDATE) registro.

```php
$user = new User;
$user->name = 'John';
$user->email = 'john@example.com';
$user->save(); // INSERT

$user->name = 'Jane';
$user->save(); // UPDATE
```

#### `destroy(): bool`
Deleta registro atual.

```php
$user = (new User)->findById(1);
$user->destroy();
```

**Query Builders:**
```php
(new User)
    ->find('status = :status', 'status=active')
    ->order('created_at DESC')
    ->limit(10)
    ->offset(20)
    ->fetch(true);
```

**Tratamento de Erros:**
```php
if (!$user->save()) {
    $error = $user->fail(); // PDOException
    echo $error->getMessage();
}
```

### 5. JWT.php - Autenticação JWT

**Algoritmo:** HS256 (HMAC SHA-256)  
**Secret:** Definido em `JWT::$secret`

#### `generate(array $payload, int $expiration = 3600): string`
Gera token JWT.

```php
$token = JWT::generate([
    'user_id' => 123,
    'role' => 'admin'
], 86400); // 24 horas
```

**Token gerado:**
```
eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjoxMjMsInJvbGUiOiJhZG1pbiIsImlhdCI6MTYzODM2MDAwMCwiZXhwIjoxNjM4NDQ2NDAwfQ.signature
```

#### `verify(string $token): array|false`
Verifica e decodifica token.

```php
$payload = JWT::verify($token);
if ($payload) {
    $userId = $payload['user_id'];
} else {
    // Token inválido ou expirado
}
```

**Validações:**
- Estrutura do token (3 partes)
- Assinatura (HMAC)
- Expiração (`exp`)

#### `decode(string $token): array|false`
Decodifica sem verificar assinatura (apenas valida expiração).

**⚠️ IMPORTANTE:** Altere a secret key em produção!
```php
private static $secret = 'sua-chave-secreta-aqui';
```

### 6. Password.php - Hash de Senhas

**Algoritmos:** ARGON2ID (preferencial) ou BCRYPT (fallback)

#### `hash(string $password): string`
Gera hash seguro.

```php
$hash = Password::hash('senha123');
// $argon2id$v=19$m=4096,t=4,p=3$...
```

**Configurações ARGON2ID:**
- Memory Cost: 4096 KB (4 MB)
- Time Cost: 4 iterações
- Threads: 3

#### `verify(string $password, string $hash): bool`
Verifica senha contra hash.

```php
if (Password::verify('senha123', $hash)) {
    // Senha correta
}
```

#### `needsRehash(string $hash): bool`
Verifica se hash precisa ser atualizado.

```php
if (Password::verify($password, $hash) && Password::needsRehash($hash)) {
    $newHash = Password::hash($password);
    // Atualizar no banco
}
```

### 7. CORS.php - Cross-Origin Resource Sharing

**Padrão:** Static Class  
**Responsabilidade:** Gerenciar headers CORS

**Configuração padrão:**
```php
CORS::setAllowedOrigins(['*']);
CORS::setAllowedMethods(['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS']);
CORS::setAllowedHeaders(['Content-Type', 'Authorization']);
CORS::setMaxAge(3600);
CORS::handle();
```

**Headers enviados:**
```
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization
Access-Control-Allow-Credentials: true
Access-Control-Max-Age: 3600
```

**Tratamento de OPTIONS:**
- Requisições OPTIONS retornam 204 (No Content) automaticamente

---

## 🔄 Fluxo de Requisição

### Exemplo Completo: POST /auth/login

**1. Requisição HTTP:**
```http
POST /auth/login HTTP/1.1
Host: localhost
Content-Type: application/json

{
  "user_email": "john@example.com",
  "user_password": "senha123"
}
```

**2. index.php:**
- Carrega autoload
- Registra ErrorHandler
- Configura CORS
- Inclui `routes/api.php`

**3. routes/api.php:**
```php
$router->post('/auth/login', 'ExampleAuthController@login');
```

**4. Router::dispatch():**
- Match: `POST` + `/auth/login` ✅
- Coleta `$data`:
  ```php
  [
      'user_email' => 'john@example.com',
      'user_password' => 'senha123'
  ]
  ```
- Middlewares: nenhum
- Chama: `ExampleAuthController->login($data, [], [])`

**5. Controller:**
```php
public function login($data, $middlewareData) {
    // Validações
    if(empty($data['user_email'])) {
        return $this->error(['message' => 'Email is required'], 400);
    }
    
    // Busca usuário
    $user = (new ExampleUser)
        ->find('user_email = :email', 'email=' . $data['user_email'])
        ->fetch();
    
    if(!$user) {
        return $this->error(['message' => 'User not found'], 404);
    }
    
    // Verifica senha
    if(!Password::verify($data['user_password'], $user->user_password)) {
        return $this->error(['message' => 'Invalid password'], 401);
    }
    
    // Gera token
    $token = JWT::generate(['user_id' => $user->user_id], 60 * 60 * 24 * 30);
    
    // Retorna sucesso
    return $this->response(['token' => $token]);
}
```

**6. Resposta HTTP:**
```http
HTTP/1.1 200 OK
Content-Type: application/json; charset=utf-8

{
    "status": "success",
    "token": "eyJhbGc..."
}
```

---

## 🛣️ Sistema de Roteamento

### Definição de Rotas

**Arquivo:** `routes/api.php`

```php
<?php

use App\Core\Router;

$router = new Router();

// Rotas públicas
$router->get('/', 'ExampleController@index');
$router->post('/auth/login', 'AuthController@login');
$router->post('/users', 'UserController@create');

// Rotas protegidas
$router->get('/profile', 'UserController@profile', [
    'AuthMiddleware@handle'
]);

// Rotas com parâmetros
$router->get('/users/{id}', 'UserController@show');
$router->put('/users/{id}', 'UserController@update');
$router->delete('/users/{id}', 'UserController@delete');

// Rotas com múltiplos parâmetros
$router->get('/posts/{post_id}/comments/{comment_id}', 'CommentController@show');

// Rotas com middlewares e dados injetados
$router->delete('/users/{id}', 'UserController@delete', 
    ['AuthMiddleware@handle'], 
    ['permission' => 'users.delete']
);

// Middlewares globais
$router->addMiddleware('/*', 'LogMiddleware@log');
$router->addMiddleware('/admin/*', 'AdminMiddleware@check');

$router->dispatch();
```

### Parâmetros de Rota

**Sintaxe:** `{nome_parametro}`

```php
$router->get('/users/{user_id}', 'UserController@show');
$router->get('/categories/{slug}/products/{product_id}', 'ProductController@show');
```

**No controller:**
```php
public function show($data, $middlewareData) {
    $userId = $data['user_id'];
    $slug = $data['slug'];
    $productId = $data['product_id'];
}
```

### Middlewares

**Global (aplica a todas as rotas que fazem match):**
```php
$router->addMiddleware('/*', 'Middleware@method');          // Todas
$router->addMiddleware('/api/*', 'Middleware@method');      // /api/*
$router->addMiddleware('/admin/*', 'Middleware@method');    // /admin/*
```

**Por rota:**
```php
$router->get('/protected', 'Controller@method', [
    'AuthMiddleware@handle',
    'RateLimitMiddleware@check'
]);
```

**Ordem de execução:**
1. Middlewares globais (ordem de definição)
2. Middlewares da rota (ordem no array)

### Dados Injetados

```php
$router->delete('/users/{id}', 'UserController@delete', 
    ['AuthMiddleware@handle'], 
    ['permission' => 'users.delete', 'log_action' => true]
);
```

**No controller:**
```php
public function delete($data, $middlewareData, $injectedData) {
    $permission = $injectedData['permission'];        // 'users.delete'
    $logAction = $injectedData['log_action'];         // true
}
```

---

## 🎮 Controllers

### Estrutura de um Controller

**Local:** `app/Controllers/`  
**Namespace:** `App\Controllers`  
**Extends:** `App\Core\Controller`

```php
<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Lista todos os usuários
     *
     * GET /users
     */
    public function index($data, $middlewareData)
    {
        $users = (new User)
            ->find(null, null, 'id, name, email')
            ->fetch(true, true);
        
        return $this->response(['users' => $users]);
    }
    
    /**
     * Exibe um usuário específico
     *
     * GET /users/{id}
     */
    public function show($data, $middlewareData)
    {
        $user = (new User)->findById($data['id'], 'id, name, email');
        
        if (!$user) {
            return $this->error(['message' => 'User not found'], 404);
        }
        
        return $this->response(['user' => $user->data]);
    }
    
    /**
     * Cria novo usuário
     *
     * POST /users
     */
    public function create($data, $middlewareData)
    {
        // Validação
        if (empty($data['name']) || empty($data['email'])) {
            return $this->error(['message' => 'Name and email are required'], 422);
        }
        
        // Criação
        $user = new User;
        $user->name = $data['name'];
        $user->email = $data['email'];
        
        if ($user->save()) {
            return $this->response(['user' => $user->data], 201);
        }
        
        return $this->error([
            'message' => 'Failed to create user',
            'error' => $user->fail()->getMessage()
        ], 500);
    }
    
    /**
     * Atualiza usuário
     *
     * PUT /users/{id}
     */
    public function update($data, $middlewareData)
    {
        $user = (new User)->findById($data['id']);
        
        if (!$user) {
            return $this->error(['message' => 'User not found'], 404);
        }
        
        $user->name = $data['name'] ?? $user->name;
        $user->email = $data['email'] ?? $user->email;
        
        if ($user->save()) {
            return $this->response(['user' => $user->data]);
        }
        
        return $this->error(['message' => 'Failed to update user'], 500);
    }
    
    /**
     * Deleta usuário
     *
     * DELETE /users/{id}
     */
    public function delete($data, $middlewareData, $injectedData)
    {
        // Verifica permissão (de middleware)
        if (!in_array($injectedData['permission'], $middlewareData['user']['permissions'])) {
            return $this->error(['message' => 'Unauthorized'], 403);
        }
        
        $user = (new User)->findById($data['id']);
        
        if (!$user) {
            return $this->error(['message' => 'User not found'], 404);
        }
        
        if ($user->destroy()) {
            return $this->response(['message' => 'User deleted successfully']);
        }
        
        return $this->error(['message' => 'Failed to delete user'], 500);
    }
}
```

### Parâmetros dos Métodos

**Todos os métodos de controller recebem 3 parâmetros:**

#### 1. `$data` (array)
Contém **TODOS** os dados da requisição:
- Parâmetros de rota (`{id}`, `{slug}`)
- Query string (`?page=1&limit=10`)
- POST data (form-data, x-www-form-urlencoded)
- JSON body
- Arquivos (`$data['files']`)

```php
// GET /users/123?include=posts&limit=5
// Body: {"filter": "active"}
$data = [
    'id' => '123',              // Route param
    'include' => 'posts',       // Query param
    'limit' => '5',             // Query param
    'filter' => 'active'        // JSON body
];
```

#### 2. `$middlewareData` (array)
Dados retornados pelos middlewares.

```php
// AuthMiddleware retornou: ['user' => $userData]
$authUser = $middlewareData['user'];
```

#### 3. `$injectedData` (array)
Dados definidos na rota.

```php
// Rota: $router->delete('/users/{id}', '...', [], ['permission' => 'users.delete'])
$permission = $injectedData['permission'];
```

### Métodos Utilitários

#### `response(array $data, int $statusCode = 200): never`
```php
return $this->response([
    'message' => 'Success',
    'data' => $result
], 201);
```

#### `error(array $data, int $statusCode = 400): never`
```php
return $this->error([
    'message' => 'Validation failed',
    'errors' => $errors
], 422);
```

#### `getRequestData(): array`
Raramente usado manualmente (Router já coleta).

```php
$data = $this->getRequestData();
```

### Propriedades Disponíveis

```php
$this->db;  // PDO connection
```

---

## 🗄️ Models

### Estrutura de um Model

**Local:** `app/Models/`  
**Namespace:** `App\Models`  
**Extends:** `App\Core\Model`

```php
<?php

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    public function __construct()
    {
        parent::__construct(
            'users',                                    // Table name
            ['name', 'email', 'password'],              // Required fields
            'id',                                        // Primary key
            false                                        // Is UUID? (false = auto-increment)
        );
    }
    
    /**
     * Métodos customizados podem ser adicionados
     */
    
    public function findByEmail(string $email): ?self
    {
        return $this->find('email = :email', "email={$email}")->fetch();
    }
    
    public function getActivePosts()
    {
        $sql = "SELECT * FROM posts WHERE user_id = :user_id AND status = 'active'";
        $stmt = Connect::getInstance()->getConnection()->prepare($sql);
        $stmt->execute(['user_id' => $this->id]);
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
}
```

### Model com UUID

```php
class Product extends Model
{
    public function __construct()
    {
        parent::__construct(
            'products',
            ['name', 'price'],
            'uuid',
            true  // ✅ UUID será gerado automaticamente no save()
        );
    }
}
```

**Uso:**
```php
$product = new Product;
$product->name = 'Laptop';
$product->price = 1500;
$product->save();

echo $product->uuid;  // '550e8400-e29b-41d4-a716-446655440000'
```

### Operações CRUD

#### CREATE
```php
$user = new User;
$user->name = 'John Doe';
$user->email = 'john@example.com';
$user->password = Password::hash('senha123');

if ($user->save()) {
    echo $user->id;  // ID gerado
}
```

#### READ (Single)
```php
// Por ID
$user = (new User)->findById(1);

// Com condição
$user = (new User)
    ->find('email = :email', 'email=john@example.com')
    ->fetch();

// Selecionar colunas específicas
$user = (new User)->findById(1, 'id, name, email');
```

#### READ (Multiple)
```php
// Todos os registros
$users = (new User)->find()->fetch(true);

// Com condições
$users = (new User)
    ->find('status = :status', 'status=active')
    ->order('created_at DESC')
    ->limit(10)
    ->fetch(true);

// Array associativo simples (sem objetos Model)
$users = (new User)->find()->fetch(true, true);
```

#### UPDATE
```php
$user = (new User)->findById(1);
$user->name = 'Jane Doe';
$user->email = 'jane@example.com';
$user->save();  // UPDATE automático (porque já tem ID)
```

#### DELETE
```php
$user = (new User)->findById(1);
if ($user->destroy()) {
    // Deletado com sucesso
}
```

### Query Builders

```php
$users = (new User)
    ->find('age > :age', 'age=18')
    ->order('created_at DESC')
    ->limit(20)
    ->offset(40)
    ->fetch(true);
```

### Acesso aos Dados

**Via propriedades dinâmicas:**
```php
$user = (new User)->findById(1);
echo $user->name;       // John Doe
echo $user->email;      // john@example.com
```

**Via objeto data:**
```php
$userData = $user->data;
echo $userData->name;
```

**Como array (com readOnly):**
```php
$users = (new User)->find()->fetch(true, true);
foreach ($users as $user) {
    echo $user['name'];  // Array
}
```

### Tratamento de Erros

```php
$user = new User;
$user->name = 'John';

if (!$user->save()) {
    $error = $user->fail();  // PDOException
    echo $error->getMessage();
    echo $error->getCode();
}
```

### Queries Customizadas

```php
public function getPostsWithAuthor()
{
    $db = Connect::getInstance()->getConnection();
    $sql = "
        SELECT p.*, u.name as author_name
        FROM posts p
        INNER JOIN users u ON p.user_id = u.id
        WHERE p.id = :post_id
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute(['post_id' => $this->id]);
    return $stmt->fetch(\PDO::FETCH_OBJ);
}
```

---

## 🛡️ Middlewares

### Estrutura de um Middleware

**Local:** `app/Middleware/`  
**Namespace:** `App\Middleware`

```php
<?php

namespace App\Middleware;

class ExampleMiddleware
{
    /**
     * @param array $data - Dados da requisição
     * @param array $injectData - Dados injetados na rota
     * @return array|null - Dados para $middlewareData ou null
     */
    public function handle($data, $injectData)
    {
        // Lógica do middleware
        
        // Opção 1: Bloquear requisição
        if (!$authorized) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit;
        }
        
        // Opção 2: Passar dados para controller
        return [
            'user' => $userData,
            'permissions' => $permissions
        ];
        
        // Opção 3: Não passar nada
        return null;
    }
}
```

### Middleware de Autenticação

```php
<?php

namespace App\Middleware;

use App\Core\JWT;
use App\Models\User;

class AuthMiddleware
{
    public function handle($data, $injectData)
    {
        // 1. Extrai token do header
        $headers = getallheaders();
        $token = $headers['Authorization'] ?? null;
        
        if (!$token) {
            http_response_code(401);
            echo json_encode([
                'status' => 'error',
                'message' => 'Token não fornecido'
            ]);
            exit;
        }
        
        // 2. Remove 'Bearer ' do token
        $token = str_replace('Bearer ', '', $token);
        
        // 3. Verifica token
        $payload = JWT::verify($token);
        
        if (!$payload) {
            http_response_code(401);
            echo json_encode([
                'status' => 'error',
                'message' => 'Token inválido ou expirado'
            ]);
            exit;
        }
        
        // 4. Busca usuário
        $user = (new User)->findById($payload['user_id'], 'id, name, email, role');
        
        if (!$user) {
            http_response_code(401);
            echo json_encode([
                'status' => 'error',
                'message' => 'Usuário não encontrado'
            ]);
            exit;
        }
        
        // 5. Retorna dados do usuário para controller
        return [
            'user' => $user->data,
            'token_payload' => $payload
        ];
    }
}
```

**Uso:**
```php
// routes/api.php
$router->get('/profile', 'UserController@profile', ['AuthMiddleware@handle']);
```

```php
// Controller
public function profile($data, $middlewareData)
{
    $user = $middlewareData['user'];  // Dados do middleware
    return $this->response(['user' => $user]);
}
```

### Middleware de Rate Limit

```php
<?php

namespace App\Middleware;

class RateLimitMiddleware
{
    private static $requests = [];
    private $limit = 60;  // requisições
    private $window = 60; // segundos
    
    public function handle($data, $injectData)
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $now = time();
        
        // Limpa requisições antigas
        if (isset(self::$requests[$ip])) {
            self::$requests[$ip] = array_filter(
                self::$requests[$ip],
                fn($timestamp) => ($now - $timestamp) < $this->window
            );
        }
        
        // Verifica limite
        if (isset(self::$requests[$ip]) && count(self::$requests[$ip]) >= $this->limit) {
            http_response_code(429);
            echo json_encode([
                'status' => 'error',
                'message' => 'Rate limit exceeded'
            ]);
            exit;
        }
        
        // Registra requisição
        self::$requests[$ip][] = $now;
        
        return null;
    }
}
```

### Middleware de Logging

```php
<?php

namespace App\Middleware;

class LogMiddleware
{
    public function handle($data, $injectData)
    {
        $log = [
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $_SERVER['REQUEST_METHOD'],
            'path' => $_SERVER['REQUEST_URI'],
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ];
        
        error_log(json_encode($log));
        
        return null;  // Não passa dados
    }
}
```

### Middleware de Permissões

```php
<?php

namespace App\Middleware;

class PermissionMiddleware
{
    public function handle($data, $injectData)
    {
        $user = $data['user']; // De AuthMiddleware
        $requiredPermission = $injectData['permission'];
        
        if (!in_array($requiredPermission, $user['permissions'])) {
            http_response_code(403);
            echo json_encode([
                'status' => 'error',
                'message' => 'Forbidden: Insufficient permissions'
            ]);
            exit;
        }
        
        return null;
    }
}
```

**Uso combinado:**
```php
$router->delete('/users/{id}', 'UserController@delete', 
    ['AuthMiddleware@handle', 'PermissionMiddleware@check'],
    ['permission' => 'users.delete']
);
```

---

## 🔐 Autenticação e Segurança

### Fluxo de Autenticação Completo

#### 1. Registro de Usuário

**Rota:**
```php
$router->post('/auth/register', 'AuthController@register');
```

**Controller:**
```php
public function register($data, $middlewareData)
{
    // Validações
    if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
        return $this->error(['message' => 'All fields are required'], 422);
    }
    
    // Verifica se email já existe
    $existingUser = (new User)->find('email = :email', "email={$data['email']}")->fetch();
    
    if ($existingUser) {
        return $this->error(['message' => 'Email already registered'], 409);
    }
    
    // Cria usuário
    $user = new User;
    $user->name = $data['name'];
    $user->email = $data['email'];
    $user->password = Password::hash($data['password']);
    
    if ($user->save()) {
        // Gera token
        $token = JWT::generate([
            'user_id' => $user->id,
            'email' => $user->email
        ], 60 * 60 * 24 * 30); // 30 dias
        
        return $this->response([
            'message' => 'User registered successfully',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ]
        ], 201);
    }
    
    return $this->error(['message' => 'Failed to register user'], 500);
}
```

#### 2. Login

**Rota:**
```php
$router->post('/auth/login', 'AuthController@login');
```

**Controller:**
```php
public function login($data, $middlewareData)
{
    // Validações
    if (empty($data['email']) || empty($data['password'])) {
        return $this->error(['message' => 'Email and password are required'], 422);
    }
    
    // Busca usuário
    $user = (new User)->find('email = :email', "email={$data['email']}")->fetch();
    
    if (!$user) {
        return $this->error(['message' => 'Invalid credentials'], 401);
    }
    
    // Verifica senha
    if (!Password::verify($data['password'], $user->password)) {
        return $this->error(['message' => 'Invalid credentials'], 401);
    }
    
    // Gera token
    $token = JWT::generate([
        'user_id' => $user->id,
        'email' => $user->email
    ], 60 * 60 * 24 * 30); // 30 dias
    
    return $this->response([
        'token' => $token,
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email
        ]
    ]);
}
```

#### 3. Rotas Protegidas

**Rotas:**
```php
$router->get('/auth/me', 'AuthController@me', ['AuthMiddleware@handle']);
$router->put('/auth/update-password', 'AuthController@updatePassword', ['AuthMiddleware@handle']);
```

**Controller:**
```php
public function me($data, $middlewareData)
{
    $user = $middlewareData['user'];  // Do AuthMiddleware
    
    return $this->response(['user' => $user]);
}

public function updatePassword($data, $middlewareData)
{
    $authUser = $middlewareData['user'];
    
    // Validações
    if (empty($data['current_password']) || empty($data['new_password'])) {
        return $this->error(['message' => 'All fields are required'], 422);
    }
    
    // Busca usuário completo (com senha)
    $user = (new User)->findById($authUser->id);
    
    // Verifica senha atual
    if (!Password::verify($data['current_password'], $user->password)) {
        return $this->error(['message' => 'Current password is incorrect'], 401);
    }
    
    // Atualiza senha
    $user->password = Password::hash($data['new_password']);
    
    if ($user->save()) {
        return $this->response(['message' => 'Password updated successfully']);
    }
    
    return $this->error(['message' => 'Failed to update password'], 500);
}
```

### Requisição com Token (Client-side)

**JavaScript (Fetch API):**
```javascript
const token = localStorage.getItem('token');

fetch('http://localhost/auth/me', {
    method: 'GET',
    headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
    }
})
.then(response => response.json())
.then(data => console.log(data));
```

**cURL:**
```bash
curl -X GET http://localhost/auth/me \
  -H "Authorization: Bearer eyJhbGc..."
```

### Refresh Token (Opcional)

Para implementar refresh token, você pode:

1. Criar tabela `refresh_tokens`
2. Gerar refresh token no login
3. Criar endpoint `/auth/refresh`
4. Validar refresh token e gerar novo access token

---

## 💾 Banco de Dados

### Configuração

**Arquivo:** `config/database.php`

```php
<?php

return [
    'driver' => 'mysql',
    'host' => 'db',                  // Nome do serviço Docker
    'database' => 'database',
    'username' => 'root',
    'password' => 'root',
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix' => '',
];
```

### Conexão

**Via Model (automática):**
```php
$user = (new User)->findById(1);
// Conexão é feita automaticamente
```

**Manual:**
```php
$db = Connect::getInstance()->getConnection();
$stmt = $db->prepare('SELECT * FROM users WHERE id = :id');
$stmt->execute(['id' => 1]);
$user = $stmt->fetch(PDO::FETCH_OBJ);
```

### Exemplo de Schema SQL

**Tabela com Auto-increment:**
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

**Tabela com UUID:**
```sql
CREATE TABLE products (
    uuid VARCHAR(36) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    stock INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Migrações (Manual)

O WallyAPI não possui sistema de migrations automático. Use PHPMyAdmin ou SQL files.

**Exemplo de estrutura:**
```
example_database/
├── 001_create_users_table.sql
├── 002_create_products_table.sql
├── 003_create_orders_table.sql
└── seed_data.sql
```

**Execute via PHPMyAdmin:**
- URL: http://localhost:4100
- Login: root / root
- Import SQL files

---

## 🔧 CLI - Geração de Código

### Comando Make

**Localização:** `make.php`

**Uso:**
```bash
php make.php [comando] [nome]
```

### Criar Controller

```bash
php make.php controller Product
```

**Gera:** `app/Controllers/ProductController.php`

```php
<?php

namespace App\Controllers;

use App\Core\Controller;

class ProductController extends Controller {
    public function index($data, $middlewareData) {
        return $this->response([
            'message' => 'Hello world'
        ]);
    }
}
```

**Próximos passos:**
1. Adicionar métodos (show, create, update, delete)
2. Registrar rotas em `routes/api.php`

### Criar Model

```bash
php make.php model Product
```

**Gera:** `app/Models/Product.php`

```php
<?php

namespace App\Models;

use App\Core\Model;

class Product extends Model {
    public function __construct()
    {
        parent::__construct(
            'table_name',           // ⚠️ Alterar para nome real
            [
                // Adicione aqui os campos que podem ser preenchidos em massa
            ],
            'primary_key',          // ⚠️ Alterar para PK real (ex: 'id')
            true                    // true se UUID, false se auto-increment
        );
    }
}
```

**Edite manualmente:**
```php
parent::__construct(
    'products',
    ['name', 'price', 'stock'],
    'id',
    false
);
```

### Criar Middleware

```bash
php make.php middleware Admin
```

**Gera:** `app/Middleware/AdminMiddleware.php`

```php
<?php

namespace App\Middleware;

class AdminMiddleware {
    public function handle() {
        // Implemente sua lógica aqui
        
        // Retorne null se não quiser passar dados para o controller
        // ou retorne um array com os dados que deseja passar
        return null;
    }
}
```

**Edite manualmente:**
```php
public function handle($data, $injectData)
{
    $user = $data['user'];  // De AuthMiddleware
    
    if ($user['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Admin only']);
        exit;
    }
    
    return null;
}
```

### Customizar Stubs

**Locais:** `app/stubs/`

- `controller.stub`
- `model.stub`
- `middleware.stub`

**Sintaxe de template:**
```php
{{name}}  // Substituído pelo nome fornecido
```

**Exemplo de customização:**
```php
// app/stubs/controller.stub
<?php

namespace App\Controllers;

use App\Core\Controller;

/**
 * {{name}}Controller
 * 
 * @author Your Name
 */
class {{name}}Controller extends Controller
{
    public function index($data, $middlewareData)
    {
        return $this->response(['message' => 'Hello from {{name}}']);
    }
    
    public function show($data, $middlewareData)
    {
        // TODO: Implement
    }
    
    public function create($data, $middlewareData)
    {
        // TODO: Implement
    }
    
    public function update($data, $middlewareData)
    {
        // TODO: Implement
    }
    
    public function delete($data, $middlewareData)
    {
        // TODO: Implement
    }
}
```

---

## 🐳 Ambiente Docker

### Serviços

**docker-compose.yml:**

```yaml
services:
  web:              # PHP 8.2 + Apache
  db:               # MariaDB
  phpmyadmin:       # PHPMyAdmin
```

### URLs

| Serviço | URL | Porta |
|---------|-----|-------|
| API | http://localhost | 80 |
| PHPMyAdmin | http://localhost:4100 | 4100 |

### Comandos Docker

**Iniciar:**
```bash
docker compose up -d
```

**Parar:**
```bash
docker compose down
```

**Rebuild:**
```bash
docker compose up -d --build
```

**Logs:**
```bash
docker compose logs -f web
docker compose logs -f db
```

**Executar comando no container:**
```bash
docker compose exec web bash
docker compose exec db mysql -u root -p
```

### Volumes

```yaml
volumes:
  - ./www:/var/www/html          # Código da aplicação
  - ./datadir:/var/lib/mysql     # Dados do MariaDB
```

### Instalação de Dependências

**Primeira vez:**
```bash
cd www
composer install
```

**Adicionar pacote:**
```bash
composer require vendor/package
```

### Acesso ao Banco

**Via PHPMyAdmin:**
- URL: http://localhost:4100
- User: root
- Pass: root

**Via CLI (dentro do container):**
```bash
docker compose exec db mysql -u root -p
# Senha: root

USE database;
SHOW TABLES;
SELECT * FROM users;
```

---

## 📐 Convenções e Padrões

### Nomenclatura

#### Controllers
- **Padrão:** PascalCase + sufixo "Controller"
- **Exemplos:** `UserController`, `ProductController`, `OrderController`
- **Métodos:** camelCase (index, show, create, update, delete)

#### Models
- **Padrão:** PascalCase (singular)
- **Exemplos:** `User`, `Product`, `Order`
- **Tabela:** snake_case plural (`users`, `products`, `orders`)

#### Middlewares
- **Padrão:** PascalCase + sufixo "Middleware"
- **Exemplos:** `AuthMiddleware`, `AdminMiddleware`, `LogMiddleware`
- **Métodos:** camelCase (handle, check, validate)

### Estrutura de Resposta

**Sucesso:**
```json
{
    "status": "success",
    "message": "Operation successful",
    "data": {...}
}
```

**Erro:**
```json
{
    "status": "error",
    "message": "Error description",
    "errors": [...]
}
```

### Status Codes HTTP

| Código | Significado | Quando usar |
|--------|-------------|-------------|
| 200 | OK | Sucesso geral (GET, PUT, DELETE) |
| 201 | Created | Recurso criado (POST) |
| 204 | No Content | Sucesso sem corpo (DELETE) |
| 400 | Bad Request | Erro genérico do cliente |
| 401 | Unauthorized | Não autenticado (token inválido) |
| 403 | Forbidden | Não autorizado (sem permissão) |
| 404 | Not Found | Recurso não encontrado |
| 422 | Unprocessable Entity | Validação falhou |
| 429 | Too Many Requests | Rate limit excedido |
| 500 | Internal Server Error | Erro do servidor |

### Organização de Rotas

```php
// Agrupar por recurso
// ==================

// Auth
$router->post('/auth/login', 'AuthController@login');
$router->post('/auth/register', 'AuthController@register');
$router->get('/auth/me', 'AuthController@me', ['AuthMiddleware@handle']);

// Users
$router->get('/users', 'UserController@index');
$router->get('/users/{id}', 'UserController@show');
$router->post('/users', 'UserController@create');
$router->put('/users/{id}', 'UserController@update', ['AuthMiddleware@handle']);
$router->delete('/users/{id}', 'UserController@delete', ['AuthMiddleware@handle']);

// Products
$router->get('/products', 'ProductController@index');
$router->get('/products/{id}', 'ProductController@show');
$router->post('/products', 'ProductController@create', ['AuthMiddleware@handle']);
```

### Validação

**No Controller:**
```php
public function create($data, $middlewareData)
{
    // 1. Validar campos obrigatórios
    $required = ['name', 'email', 'password'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            return $this->error([
                'message' => "Field '{$field}' is required"
            ], 422);
        }
    }
    
    // 2. Validar formato
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        return $this->error(['message' => 'Invalid email format'], 422);
    }
    
    // 3. Validar unicidade
    $existing = (new User)->find('email = :email', "email={$data['email']}")->fetch();
    if ($existing) {
        return $this->error(['message' => 'Email already exists'], 409);
    }
    
    // 4. Processar
    // ...
}
```

### Segurança

**✅ SEMPRE:**
- Use `Password::hash()` para senhas
- Use `JWT::verify()` para validar tokens
- Valide todos os inputs
- Use prepared statements (já feito no Model)
- Sanitize outputs

**❌ NUNCA:**
- Retorne senhas em responses
- Use concatenação de strings em SQL
- Confie em dados do cliente sem validação
- Exponha informações sensíveis em erros

---

## ⚠️ Tratamento de Erros

### ErrorHandler Global

**Localização:** `ErrorHandler.php`

**Registrado em:** `index.php`

```php
set_error_handler(['ErrorHandler', 'handle']);
set_exception_handler(['ErrorHandler', 'handleException']);
register_shutdown_function(['ErrorHandler', 'handleFatalError']);
```

### Tipos de Erro Capturados

**PHP Errors:**
```php
public static function handle($errno, $errstr, $errfile, $errline)
{
    $error = [
        'error' => true,
        'message' => $errstr,
        'file' => $errfile,
        'line' => $errline,
        'type' => self::getErrorType($errno)
    ];
    
    self::sendJsonResponse($error, 500);
}
```

**Exceptions:**
```php
public static function handleException($exception)
{
    $error = [
        'error' => true,
        'message' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTrace(),
        'type' => 'Exception'
    ];
    
    self::sendJsonResponse($error, 500);
}
```

**Fatal Errors:**
```php
public static function handleFatalError()
{
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        // ...
    }
}
```

### Try-Catch em Controllers

```php
public function create($data, $middlewareData)
{
    try {
        $user = new User;
        $user->name = $data['name'];
        $user->email = $data['email'];
        
        if (!$user->save()) {
            throw new \Exception($user->fail()->getMessage());
        }
        
        return $this->response(['user' => $user->data], 201);
        
    } catch (\PDOException $e) {
        return $this->error([
            'message' => 'Database error',
            'error' => $e->getMessage()
        ], 500);
        
    } catch (\Exception $e) {
        return $this->error([
            'message' => 'An error occurred',
            'error' => $e->getMessage()
        ], 500);
    }
}
```

### Logs

**error_log():**
```php
error_log("User created: " . $user->id);
error_log(json_encode(['event' => 'user_created', 'user_id' => $user->id]));
```

**Localização dos logs:**
- Docker: `docker compose logs -f web`
- PHP error log: Configurado no php.ini

---

## 📚 Exemplos Práticos

### Exemplo 1: CRUD Completo de Produtos

**1. Criar Model:**
```bash
php make.php model Product
```

**Editar `app/Models/Product.php`:**
```php
<?php

namespace App\Models;

use App\Core\Model;

class Product extends Model
{
    public function __construct()
    {
        parent::__construct(
            'products',
            ['name', 'price'],
            'id',
            false
        );
    }
}
```

**2. Criar Controller:**
```bash
php make.php controller Product
```

**Editar `app/Controllers/ProductController.php`:**
```php
<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;

class ProductController extends Controller
{
    public function index($data, $middlewareData)
    {
        $products = (new Product)
            ->find(null, null, 'id, name, price, stock')
            ->order('created_at DESC')
            ->fetch(true, true);
        
        return $this->response(['products' => $products]);
    }
    
    public function show($data, $middlewareData)
    {
        $product = (new Product)->findById($data['id']);
        
        if (!$product) {
            return $this->error(['message' => 'Product not found'], 404);
        }
        
        return $this->response(['product' => $product->data]);
    }
    
    public function create($data, $middlewareData)
    {
        if (empty($data['name']) || empty($data['price'])) {
            return $this->error(['message' => 'Name and price are required'], 422);
        }
        
        $product = new Product;
        $product->name = $data['name'];
        $product->price = $data['price'];
        $product->stock = $data['stock'] ?? 0;
        
        if ($product->save()) {
            return $this->response(['product' => $product->data], 201);
        }
        
        return $this->error(['message' => 'Failed to create product'], 500);
    }
    
    public function update($data, $middlewareData)
    {
        $product = (new Product)->findById($data['id']);
        
        if (!$product) {
            return $this->error(['message' => 'Product not found'], 404);
        }
        
        $product->name = $data['name'] ?? $product->name;
        $product->price = $data['price'] ?? $product->price;
        $product->stock = $data['stock'] ?? $product->stock;
        
        if ($product->save()) {
            return $this->response(['product' => $product->data]);
        }
        
        return $this->error(['message' => 'Failed to update product'], 500);
    }
    
    public function delete($data, $middlewareData)
    {
        $product = (new Product)->findById($data['id']);
        
        if (!$product) {
            return $this->error(['message' => 'Product not found'], 404);
        }
        
        if ($product->destroy()) {
            return $this->response(['message' => 'Product deleted successfully']);
        }
        
        return $this->error(['message' => 'Failed to delete product'], 500);
    }
}
```

**3. Adicionar Rotas em `routes/api.php`:**
```php
$router->get('/products', 'ProductController@index');
$router->get('/products/{id}', 'ProductController@show');
$router->post('/products', 'ProductController@create', ['AuthMiddleware@handle']);
$router->put('/products/{id}', 'ProductController@update', ['AuthMiddleware@handle']);
$router->delete('/products/{id}', 'ProductController@delete', ['AuthMiddleware@handle']);
```

**4. Criar tabela no banco:**
```sql
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    stock INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Exemplo 2: Relacionamento (Posts com Autor)

**Model Post:**
```php
<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Connect;

class Post extends Model
{
    public function __construct()
    {
        parent::__construct(
            'posts',
            ['title', 'content', 'user_id'],
            'id',
            false
        );
    }
    
    public function getAuthor()
    {
        $db = Connect::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT id, name, email FROM users WHERE id = :user_id');
        $stmt->execute(['user_id' => $this->user_id]);
        return $stmt->fetch(\PDO::FETCH_OBJ);
    }
    
    public static function getWithAuthor($postId)
    {
        $db = Connect::getInstance()->getConnection();
        $sql = "
            SELECT 
                p.id,
                p.title,
                p.content,
                p.created_at,
                u.id as author_id,
                u.name as author_name,
                u.email as author_email
            FROM posts p
            INNER JOIN users u ON p.user_id = u.id
            WHERE p.id = :post_id
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute(['post_id' => $postId]);
        return $stmt->fetch(\PDO::FETCH_OBJ);
    }
}
```

**Controller:**
```php
public function show($data, $middlewareData)
{
    $post = Post::getWithAuthor($data['id']);
    
    if (!$post) {
        return $this->error(['message' => 'Post not found'], 404);
    }
    
    return $this->response(['post' => $post]);
}
```

### Exemplo 3: Upload de Arquivo

**Controller:**
```php
public function uploadAvatar($data, $middlewareData)
{
    $user = $middlewareData['user'];
    
    if (empty($data['files']['avatar'])) {
        return $this->error(['message' => 'Avatar file is required'], 422);
    }
    
    $file = $data['files']['avatar'];
    
    // Validações
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return $this->error(['message' => 'File upload error'], 400);
    }
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        return $this->error(['message' => 'Invalid file type'], 422);
    }
    
    $maxSize = 2 * 1024 * 1024; // 2MB
    if ($file['size'] > $maxSize) {
        return $this->error(['message' => 'File too large'], 422);
    }
    
    // Salvar arquivo
    $uploadDir = __DIR__ . '/../../uploads/avatars/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $destination = $uploadDir . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return $this->error(['message' => 'Failed to save file'], 500);
    }
    
    // Atualizar usuário
    $userModel = (new User)->findById($user->id);
    $userModel->avatar = $filename;
    $userModel->save();
    
    return $this->response([
        'message' => 'Avatar uploaded successfully',
        'avatar_url' => "/uploads/avatars/{$filename}"
    ]);
}
```

---

## 🎯 Checklist para IAs

Ao trabalhar com WallyAPI, verifique:

### ✅ Controllers
- [ ] Extends `App\Core\Controller`
- [ ] Namespace `App\Controllers`
- [ ] Métodos recebem `$data`, `$middlewareData`, `$injectedData`
- [ ] Usa `$this->response()` ou `$this->error()`
- [ ] Validações implementadas
- [ ] Erros tratados adequadamente

### ✅ Models
- [ ] Extends `App\Core\Model`
- [ ] Namespace `App\Models`
- [ ] Construtor chama `parent::__construct()`
- [ ] Nome da tabela correto
- [ ] Campos obrigatórios definidos
- [ ] Primary key configurada corretamente
- [ ] UUID flag correto (true/false)

### ✅ Middlewares
- [ ] Namespace `App\Middleware`
- [ ] Método `handle($data, $injectData)`
- [ ] Retorna `array` ou `null`
- [ ] Bloqueia com `exit` quando necessário
- [ ] Status code correto ao bloquear

### ✅ Rotas
- [ ] Definidas em `routes/api.php`
- [ ] Formato: `Controller@method`
- [ ] Middlewares aplicados corretamente
- [ ] Dados injetados quando necessário
- [ ] Parâmetros de rota entre `{}`

### ✅ Segurança
- [ ] Senhas hasheadas com `Password::hash()`
- [ ] Tokens JWT verificados
- [ ] Inputs validados
- [ ] Rotas protegidas com AuthMiddleware
- [ ] Senhas nunca retornadas em responses

### ✅ Banco de Dados
- [ ] Configuração em `config/database.php`
- [ ] Tabelas criadas no MariaDB
- [ ] Schema correto (tipos, constraints)
- [ ] Índices em campos de busca frequente

---

## 📖 Glossário

| Termo | Definição |
|-------|-----------|
| **Entry Point** | Ponto de entrada da aplicação (`index.php`) |
| **PSR-4** | Padrão de autoloading do PHP |
| **Singleton** | Padrão de design que garante apenas uma instância |
| **Active Record** | Padrão onde Model representa linha do banco |
| **Middleware** | Camada intermediária que processa requisição |
| **JWT** | JSON Web Token (autenticação stateless) |
| **PDO** | PHP Data Objects (abstração de banco) |
| **CORS** | Cross-Origin Resource Sharing |
| **UUID** | Universally Unique Identifier |
| **Prepared Statement** | Query parametrizada (segura) |

---

## 🔗 Referências Rápidas

### Estrutura de Método Controller
```php
public function methodName($data, $middlewareData, $injectedData = [])
{
    // 1. Validar dados
    // 2. Processar lógica
    // 3. Retornar response/error
}
```

### Estrutura de Middleware
```php
public function handle($data, $injectData)
{
    // 1. Verificar condição
    // 2. Bloquear OU passar dados
    return ['key' => 'value'] ou null;
}
```

### Query com Model
```php
(new Model)
    ->find('campo = :valor', 'valor=123')
    ->order('campo DESC')
    ->limit(10)
    ->fetch(true);  // array de objetos Model
```

### Rota Completa
```php
$router->METHOD('/path/{param}', 'Controller@method', 
    ['Middleware@handle'], 
    ['injected' => 'data']
);
```

---

**Fim do Guia Estrutural WallyAPI para IAs**

*Este documento deve ser atualizado sempre que houver mudanças significativas na arquitetura do projeto.*

