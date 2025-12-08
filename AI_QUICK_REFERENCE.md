# ⚡ WallyAPI - Referência Rápida para IAs

> Guia compacto para assistentes de IA trabalharem rapidamente com WallyAPI

---

## 🎯 Essencial em 60 Segundos

### O que é?
Framework PHP minimalista para APIs RESTful com roteamento, middlewares, JWT, Models (Active Record) e CORS.

### Stack
PHP 8.2 + Apache + MariaDB + Docker

### Entry Point
```
HTTP Request → index.php → routes/api.php → Router → Middleware → Controller → Model → Response
```

---

## 📂 Estrutura Rápida

```
www/
├── app/
│   ├── Controllers/     → Lógica de negócio
│   ├── Core/            → Framework (Router, Model, JWT, Password, etc)
│   ├── Middleware/      → Validações e autenticação
│   ├── Models/          → Acesso ao banco (Active Record)
│   └── stubs/           → Templates para CLI
├── config/
│   └── database.php     → Configuração do banco
├── routes/
│   └── api.php          → Todas as rotas da API
├── index.php            → Entry point
└── make.php             → CLI para gerar código
```

---

## 🚀 Criar Novo Recurso (5 Passos)

### 1. Criar tabela SQL
```sql
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

### 2. Gerar Model
```bash
php make.php model Product
```

Editar `app/Models/Product.php`:
```php
parent::__construct(
    'products',                  // Tabela
    ['name', 'price'],           // Campos obrigatórios
    'id',                        // Primary key
    false                        // false = auto-increment, true = UUID
);
```

### 3. Gerar Controller
```bash
php make.php controller Product
```

Editar `app/Controllers/ProductController.php` (adicionar métodos CRUD)

### 4. Adicionar Rotas
Em `routes/api.php`:
```php
$router->get('/products', 'ProductController@index');
$router->get('/products/{id}', 'ProductController@show');
$router->post('/products', 'ProductController@create', ['AuthMiddleware@handle']);
$router->put('/products/{id}', 'ProductController@update', ['AuthMiddleware@handle']);
$router->delete('/products/{id}', 'ProductController@delete', ['AuthMiddleware@handle']);
```

### 5. Testar
```bash
curl http://localhost/products
```

---

## 🎮 Controller - Template Padrão

```php
<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;

class ProductController extends Controller
{
    // GET /products
    public function index($data, $middlewareData)
    {
        $products = (new Product)->find()->fetch(true, true);
        return $this->response(['products' => $products]);
    }
    
    // GET /products/{id}
    public function show($data, $middlewareData)
    {
        $product = (new Product)->findById($data['id']);
        
        if (!$product) {
            return $this->error(['message' => 'Not found'], 404);
        }
        
        return $this->response(['product' => $product->data]);
    }
    
    // POST /products
    public function create($data, $middlewareData)
    {
        // Validação
        if (empty($data['name'])) {
            return $this->error(['message' => 'Name required'], 422);
        }
        
        // Criação
        $product = new Product;
        $product->name = $data['name'];
        $product->price = $data['price'];
        
        if ($product->save()) {
            return $this->response(['product' => $product->data], 201);
        }
        
        return $this->error(['message' => 'Failed to create'], 500);
    }
    
    // PUT /products/{id}
    public function update($data, $middlewareData)
    {
        $product = (new Product)->findById($data['id']);
        
        if (!$product) {
            return $this->error(['message' => 'Not found'], 404);
        }
        
        $product->name = $data['name'] ?? $product->name;
        $product->price = $data['price'] ?? $product->price;
        
        if ($product->save()) {
            return $this->response(['product' => $product->data]);
        }
        
        return $this->error(['message' => 'Failed to update'], 500);
    }
    
    // DELETE /products/{id}
    public function delete($data, $middlewareData)
    {
        $product = (new Product)->findById($data['id']);
        
        if (!$product) {
            return $this->error(['message' => 'Not found'], 404);
        }
        
        if ($product->destroy()) {
            return $this->response(['message' => 'Deleted successfully']);
        }
        
        return $this->error(['message' => 'Failed to delete'], 500);
    }
}
```

---

## 🗄️ Model - Queries Comuns

### Buscar por ID
```php
$user = (new User)->findById(1);
echo $user->name;
```

### Buscar com condição
```php
$user = (new User)
    ->find('email = :email', 'email=john@example.com')
    ->fetch();
```

### Buscar múltiplos
```php
$users = (new User)
    ->find('status = :status', 'status=active')
    ->order('created_at DESC')
    ->limit(10)
    ->offset(20)
    ->fetch(true);  // Array de objetos Model
```

### Buscar como array simples
```php
$users = (new User)->find()->fetch(true, true);  // Array associativo
```

### Criar
```php
$user = new User;
$user->name = 'John';
$user->email = 'john@example.com';
$user->save();  // INSERT
```

### Atualizar
```php
$user = (new User)->findById(1);
$user->name = 'Jane';
$user->save();  // UPDATE
```

### Deletar
```php
$user = (new User)->findById(1);
$user->destroy();
```

### Tratar erro
```php
if (!$user->save()) {
    $error = $user->fail();  // PDOException
    echo $error->getMessage();
}
```

---

## 🛡️ Middleware - Template AuthMiddleware

```php
<?php

namespace App\Middleware;

use App\Core\JWT;
use App\Models\User;

class AuthMiddleware
{
    public function handle($data, $injectData)
    {
        // 1. Extrair token
        $headers = getallheaders();
        $token = $headers['Authorization'] ?? null;
        
        if (!$token) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Token required']);
            exit;
        }
        
        // 2. Remover 'Bearer '
        $token = str_replace('Bearer ', '', $token);
        
        // 3. Verificar token
        $payload = JWT::verify($token);
        
        if (!$payload) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Invalid token']);
            exit;
        }
        
        // 4. Buscar usuário
        $user = (new User)->findById($payload['user_id'], 'id, name, email');
        
        if (!$user) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'User not found']);
            exit;
        }
        
        // 5. Retornar dados para controller
        return ['user' => $user->data];
    }
}
```

**Uso no Controller:**
```php
public function profile($data, $middlewareData)
{
    $user = $middlewareData['user'];  // Dados do middleware
    return $this->response(['user' => $user]);
}
```

---

## 🔐 Auth - Login e Register

### Register
```php
public function register($data, $middlewareData)
{
    // Validar
    if (empty($data['email']) || empty($data['password'])) {
        return $this->error(['message' => 'All fields required'], 422);
    }
    
    // Verificar se existe
    $exists = (new User)->find('email = :email', "email={$data['email']}")->fetch();
    if ($exists) {
        return $this->error(['message' => 'Email already exists'], 409);
    }
    
    // Criar
    $user = new User;
    $user->name = $data['name'];
    $user->email = $data['email'];
    $user->password = Password::hash($data['password']);
    
    if (!$user->save()) {
        return $this->error(['message' => 'Failed to register'], 500);
    }
    
    // Gerar token
    $token = JWT::generate(['user_id' => $user->id], 60 * 60 * 24 * 30); // 30 dias
    
    return $this->response([
        'token' => $token,
        'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email]
    ], 201);
}
```

### Login
```php
public function login($data, $middlewareData)
{
    // Validar
    if (empty($data['email']) || empty($data['password'])) {
        return $this->error(['message' => 'Email and password required'], 422);
    }
    
    // Buscar usuário
    $user = (new User)->find('email = :email', "email={$data['email']}")->fetch();
    
    if (!$user) {
        return $this->error(['message' => 'Invalid credentials'], 401);
    }
    
    // Verificar senha
    if (!Password::verify($data['password'], $user->password)) {
        return $this->error(['message' => 'Invalid credentials'], 401);
    }
    
    // Gerar token
    $token = JWT::generate(['user_id' => $user->id], 60 * 60 * 24 * 30);
    
    return $this->response([
        'token' => $token,
        'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email]
    ]);
}
```

---

## 🛣️ Rotas - Padrões Comuns

### Rota simples
```php
$router->get('/users', 'UserController@index');
```

### Rota com parâmetro
```php
$router->get('/users/{id}', 'UserController@show');
$router->get('/posts/{slug}/comments/{comment_id}', 'CommentController@show');
```

### Rota protegida
```php
$router->get('/profile', 'UserController@profile', ['AuthMiddleware@handle']);
```

### Rota com múltiplos middlewares
```php
$router->delete('/users/{id}', 'UserController@delete', [
    'AuthMiddleware@handle',
    'AdminMiddleware@check'
]);
```

### Rota com dados injetados
```php
$router->delete('/users/{id}', 'UserController@delete', 
    ['AuthMiddleware@handle'], 
    ['permission' => 'users.delete']
);
```

### Middleware global
```php
$router->addMiddleware('/*', 'LogMiddleware@log');           // Todas as rotas
$router->addMiddleware('/admin/*', 'AdminMiddleware@check'); // /admin/*
```

---

## 📦 Dados da Requisição

### $data contém TUDO:
```php
// GET /users/123?page=2&limit=10
// Body: {"filter": "active"}

$data = [
    'id' => '123',           // Route param {id}
    'page' => '2',           // Query param
    'limit' => '10',         // Query param
    'filter' => 'active',    // JSON body
    'files' => [...]         // Uploads (se houver)
];
```

### Acessar no Controller:
```php
public function show($data, $middlewareData)
{
    $id = $data['id'];                    // Route param
    $page = $data['page'] ?? 1;           // Query param com default
    $filter = $data['filter'] ?? null;    // JSON body
    $file = $data['files']['avatar'] ?? null;  // Upload
}
```

---

## ⚙️ Utilidades Core

### JWT
```php
// Gerar token
$token = JWT::generate(['user_id' => 123], 3600);  // 1 hora

// Verificar token
$payload = JWT::verify($token);
if ($payload) {
    $userId = $payload['user_id'];
}
```

### Password
```php
// Hash
$hash = Password::hash('senha123');

// Verificar
if (Password::verify('senha123', $hash)) {
    // Senha correta
}

// Verificar se precisa rehash
if (Password::needsRehash($hash)) {
    $newHash = Password::hash($password);
}
```

### CORS (configurar em index.php)
```php
CORS::setAllowedOrigins(['*']);
CORS::setAllowedMethods(['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS']);
CORS::setAllowedHeaders(['Content-Type', 'Authorization']);
CORS::handle();
```

---

## 🎨 Padrões de Response

### Sucesso
```php
return $this->response([
    'message' => 'Success',
    'data' => $result
], 200);
```
**Output:**
```json
{"status": "success", "message": "Success", "data": {...}}
```

### Erro
```php
return $this->error([
    'message' => 'Validation failed',
    'errors' => ['field' => 'error message']
], 422);
```
**Output:**
```json
{"status": "error", "message": "Validation failed", "errors": {...}}
```

---

## 🔢 HTTP Status Codes

| Código | Uso |
|--------|-----|
| 200 | GET/PUT/DELETE sucesso |
| 201 | POST criado com sucesso |
| 400 | Erro genérico do cliente |
| 401 | Não autenticado (sem/token inválido) |
| 403 | Não autorizado (sem permissão) |
| 404 | Não encontrado |
| 422 | Validação falhou |
| 500 | Erro interno do servidor |

---

## 🐳 Docker - Comandos Essenciais

### Iniciar
```bash
docker compose up -d
```

### Parar
```bash
docker compose down
```

### Logs
```bash
docker compose logs -f web
```

### Acessar container
```bash
docker compose exec web bash
```

### Acessar banco
```bash
docker compose exec db mysql -u root -p
# Senha: root
```

---

## 🗃️ Banco de Dados

### Configuração
`config/database.php`:
```php
return [
    'driver' => 'mysql',
    'host' => 'db',
    'database' => 'database',
    'username' => 'root',
    'password' => 'root',
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci'
];
```

### Acesso direto PDO
```php
$db = Connect::getInstance()->getConnection();
$stmt = $db->prepare('SELECT * FROM users WHERE id = :id');
$stmt->execute(['id' => 1]);
$user = $stmt->fetch(PDO::FETCH_OBJ);
```

### PHPMyAdmin
- URL: http://localhost:4100
- User: root
- Pass: root

---

## ✅ Checklist Rápido

**Ao criar Controller:**
- [ ] Extends `App\Core\Controller`
- [ ] Namespace `App\Controllers`
- [ ] Métodos: `$data`, `$middlewareData`, `$injectedData`
- [ ] Retorna `$this->response()` ou `$this->error()`

**Ao criar Model:**
- [ ] Extends `App\Core\Model`
- [ ] Namespace `App\Models`
- [ ] `parent::__construct()` correto
- [ ] Tabela, campos obrigatórios, PK, UUID flag

**Ao criar Middleware:**
- [ ] Namespace `App\Middleware`
- [ ] Método `handle($data, $injectData)`
- [ ] Retorna `array` ou `null`
- [ ] Bloqueia com `exit` se necessário

**Ao criar Rota:**
- [ ] Em `routes/api.php`
- [ ] Formato: `'Controller@method'`
- [ ] Middlewares corretos
- [ ] Parâmetros entre `{}`

**Segurança:**
- [ ] Senhas com `Password::hash()`
- [ ] Tokens com `JWT::verify()`
- [ ] Validação de inputs
- [ ] Nunca retornar senhas

---

## 🚨 Erros Comuns

### "Controller not found"
- Verificar namespace: `App\Controllers`
- Verificar nome do arquivo: `ExampleController.php`
- Verificar classe: `class ExampleController extends Controller`

### "Class not found"
```bash
cd www && composer dump-autoload
```

### "Connection failed"
- Verificar `config/database.php`
- Verificar se container `db` está rodando: `docker compose ps`

### "Token invalid"
- Verificar se token está sendo enviado: `Authorization: Bearer TOKEN`
- Verificar se JWT secret é o mesmo

---

## 📚 Links Úteis

- **API:** http://localhost
- **PHPMyAdmin:** http://localhost:4100
- **Logs:** `docker compose logs -f web`

---

**Referência completa:** Veja `AI_PROJECT_GUIDE.md`

