# 🔄 WallyAPI - Exemplos de Fluxo de Dados para IAs

> Exemplos visuais e práticos de como os dados fluem através do WallyAPI

---

## 📊 Fluxo 1: Requisição Simples (GET sem autenticação)

### Requisição
```http
GET /products?category=electronics&page=1 HTTP/1.1
Host: localhost
```

### Fluxo Interno
```
1. Apache recebe requisição
   ↓
2. index.php (entry point)
   ↓
3. routes/api.php carregado
   ↓
4. Router->dispatch() chamado
   ↓
5. Router encontra: $router->get('/products', 'ProductController@index')
   ↓
6. Router coleta dados:
   $data = [
       'category' => 'electronics',  // query string
       'page' => '1'                 // query string
   ]
   ↓
7. Nenhum middleware (rota pública)
   ↓
8. ProductController instanciado
   ↓
9. ProductController->index($data, [], []) chamado
   ↓
10. Controller processa:
    $category = $data['category'];
    $page = $data['page'];
    $products = (new Product)->find('category = :cat', "cat=$category")->fetch(true);
   ↓
11. Controller retorna:
    return $this->response(['products' => $products]);
   ↓
12. response() envia:
    HTTP/1.1 200 OK
    Content-Type: application/json
    
    {
        "status": "success",
        "products": [...]
    }
```

### Código do Controller
```php
public function index($data, $middlewareData)
{
    $category = $data['category'] ?? null;
    $page = (int)($data['page'] ?? 1);
    $limit = 20;
    $offset = ($page - 1) * $limit;
    
    $query = $category 
        ? (new Product)->find('category = :cat', "cat=$category")
        : (new Product)->find();
    
    $products = $query
        ->order('created_at DESC')
        ->limit($limit)
        ->offset($offset)
        ->fetch(true, true);
    
    return $this->response([
        'products' => $products,
        'page' => $page,
        'category' => $category
    ]);
}
```

---

## 📊 Fluxo 2: Requisição com Autenticação (GET protegido)

### Requisição
```http
GET /profile HTTP/1.1
Host: localhost
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

### Fluxo Interno
```
1. Apache recebe requisição
   ↓
2. index.php
   ↓
3. routes/api.php:
   $router->get('/profile', 'UserController@profile', ['AuthMiddleware@handle'])
   ↓
4. Router->dispatch()
   ↓
5. Router coleta dados:
   $data = []  // Nenhum parâmetro nesta requisição
   ↓
6. Router executa middlewares:
   ┌─────────────────────────────────────┐
   │ AuthMiddleware->handle($data, [])   │
   │                                     │
   │ 1. Pega header Authorization        │
   │ 2. Remove 'Bearer '                 │
   │ 3. JWT::verify($token)              │
   │ 4. Busca usuário pelo user_id       │
   │ 5. return ['user' => $userData]     │
   └─────────────────────────────────────┘
   ↓
   $middlewareData = ['user' => {...}]
   ↓
7. UserController->profile($data, $middlewareData, [])
   ↓
8. Controller acessa dados do middleware:
   $user = $middlewareData['user'];
   return $this->response(['user' => $user]);
   ↓
9. Response enviada:
   HTTP/1.1 200 OK
   {
       "status": "success",
       "user": {
           "id": 123,
           "name": "John Doe",
           "email": "john@example.com"
       }
   }
```

### Código Completo

**routes/api.php:**
```php
$router->get('/profile', 'UserController@profile', ['AuthMiddleware@handle']);
```

**app/Middleware/AuthMiddleware.php:**
```php
public function handle($data, $injectData)
{
    $headers = getallheaders();
    $token = $headers['Authorization'] ?? null;
    
    if (!$token) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Token required']);
        exit;
    }
    
    $token = str_replace('Bearer ', '', $token);
    $payload = JWT::verify($token);
    
    if (!$payload) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Invalid token']);
        exit;
    }
    
    $user = (new User)->findById($payload['user_id'], 'id, name, email');
    
    return ['user' => $user->data];  // ← Isso vai para $middlewareData
}
```

**app/Controllers/UserController.php:**
```php
public function profile($data, $middlewareData)
{
    $user = $middlewareData['user'];  // ← Dados do AuthMiddleware
    
    return $this->response(['user' => $user]);
}
```

---

## 📊 Fluxo 3: Criação de Recurso (POST com JSON)

### Requisição
```http
POST /products HTTP/1.1
Host: localhost
Authorization: Bearer eyJhbGc...
Content-Type: application/json

{
    "name": "Laptop Dell",
    "price": 2500.00,
    "category": "electronics",
    "stock": 10
}
```

### Fluxo de Dados
```
REQUISIÇÃO (JSON body)
    ↓
Router coleta dados
    ↓
$data = [
    'name' => 'Laptop Dell',
    'price' => 2500.00,
    'category' => 'electronics',
    'stock' => 10
]
    ↓
AuthMiddleware executa
    ↓
$middlewareData = ['user' => {...}]
    ↓
ProductController->create($data, $middlewareData)
    ↓
┌────────────────────────────────────────────┐
│ 1. Validação                               │
│    if (empty($data['name'])) { error }     │
│                                            │
│ 2. Verificar permissão                     │
│    $authUser = $middlewareData['user']     │
│                                            │
│ 3. Criar produto                           │
│    $product = new Product;                 │
│    $product->name = $data['name'];         │
│    $product->price = $data['price'];       │
│    $product->category = $data['category']; │
│    $product->stock = $data['stock'];       │
│                                            │
│ 4. Salvar                                  │
│    if ($product->save()) {                 │
│        return response(201)                │
│    }                                       │
└────────────────────────────────────────────┘
    ↓
RESPONSE
HTTP/1.1 201 Created
{
    "status": "success",
    "product": {
        "id": 45,
        "name": "Laptop Dell",
        "price": 2500.00,
        "category": "electronics",
        "stock": 10
    }
}
```

### Código do Controller
```php
public function create($data, $middlewareData)
{
    // 1. Validação
    if (empty($data['name']) || empty($data['price'])) {
        return $this->error(['message' => 'Name and price are required'], 422);
    }
    
    // 2. Verificar permissão (opcional)
    $authUser = $middlewareData['user'];
    if ($authUser->role !== 'admin') {
        return $this->error(['message' => 'Admin only'], 403);
    }
    
    // 3. Criar produto
    $product = new Product;
    $product->name = $data['name'];
    $product->price = $data['price'];
    $product->category = $data['category'] ?? 'general';
    $product->stock = $data['stock'] ?? 0;
    
    // 4. Salvar
    if ($product->save()) {
        return $this->response([
            'message' => 'Product created successfully',
            'product' => $product->data
        ], 201);
    }
    
    return $this->error([
        'message' => 'Failed to create product',
        'error' => $product->fail()->getMessage()
    ], 500);
}
```

---

## 📊 Fluxo 4: Rota com Parâmetros e Múltiplos Middlewares

### Requisição
```http
DELETE /users/456 HTTP/1.1
Host: localhost
Authorization: Bearer eyJhbGc...
```

### Definição da Rota
```php
$router->delete('/users/{id}', 'UserController@delete', 
    ['AuthMiddleware@handle', 'AdminMiddleware@check'],
    ['permission' => 'users.delete', 'log_action' => true]
);
```

### Fluxo Completo
```
REQUISIÇÃO: DELETE /users/456
    ↓
┌─────────────────────────────────────────────────────┐
│ Router Match & Extract                              │
│                                                     │
│ Path pattern: /users/{id}                           │
│ Request path: /users/456                            │
│ Match: ✅                                           │
│ Extract: id = 456                                   │
└─────────────────────────────────────────────────────┘
    ↓
$data = ['id' => '456']
    ↓
┌─────────────────────────────────────────────────────┐
│ MIDDLEWARE 1: AuthMiddleware->handle()              │
│                                                     │
│ 1. Extrair token                                    │
│ 2. Verificar token                                  │
│ 3. Buscar usuário                                   │
│ 4. return ['user' => $userData]                     │
└─────────────────────────────────────────────────────┘
    ↓
$middlewareData = ['user' => {...}]
    ↓
┌─────────────────────────────────────────────────────┐
│ MIDDLEWARE 2: AdminMiddleware->check()              │
│                                                     │
│ 1. Pegar usuário: $user = $data['user']            │
│    (AuthMiddleware passou via $middlewareData)      │
│ 2. Verificar role: if ($user->role !== 'admin')    │
│    → BLOQUEAR com 403                               │
│ 3. return null (não precisa passar dados)           │
└─────────────────────────────────────────────────────┘
    ↓
$middlewareData permanece ['user' => {...}]
$injectedData = ['permission' => 'users.delete', 'log_action' => true]
    ↓
┌─────────────────────────────────────────────────────┐
│ UserController->delete($data, $middlewareData,      │
│                        $injectedData)               │
│                                                     │
│ $id = $data['id'];                    // '456'      │
│ $user = $middlewareData['user'];      // {...}      │
│ $permission = $injectedData['permission'];          │
│ $logAction = $injectedData['log_action'];           │
│                                                     │
│ // Verificar permissão                              │
│ if (!in_array($permission, $user->permissions)) {   │
│     return error(403)                               │
│ }                                                   │
│                                                     │
│ // Buscar e deletar                                 │
│ $userToDelete = (new User)->findById($id);          │
│ if ($userToDelete->destroy()) {                     │
│     // Log se necessário                            │
│     if ($logAction) {                               │
│         error_log("User {$id} deleted by {$user->id}");│
│     }                                               │
│     return response(200)                            │
│ }                                                   │
└─────────────────────────────────────────────────────┘
    ↓
RESPONSE
HTTP/1.1 200 OK
{
    "status": "success",
    "message": "User deleted successfully"
}
```

### Código Completo

**AdminMiddleware.php:**
```php
public function check($data, $injectData)
{
    // Usuário já foi passado pelo AuthMiddleware
    // que foi executado ANTES
    $user = $data['user'] ?? null;
    
    if (!$user || $user->role !== 'admin') {
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'message' => 'Admin access required'
        ]);
        exit;
    }
    
    return null;  // Não precisa passar dados adicionais
}
```

**UserController.php:**
```php
public function delete($data, $middlewareData, $injectedData)
{
    $userId = $data['id'];
    $authUser = $middlewareData['user'];
    $permission = $injectedData['permission'];
    $logAction = $injectedData['log_action'];
    
    // Verificação adicional de permissão
    if (!in_array($permission, $authUser->permissions)) {
        return $this->error(['message' => 'Insufficient permissions'], 403);
    }
    
    // Buscar usuário
    $user = (new User)->findById($userId);
    
    if (!$user) {
        return $this->error(['message' => 'User not found'], 404);
    }
    
    // Não permitir auto-deleção
    if ($user->id == $authUser->id) {
        return $this->error(['message' => 'Cannot delete yourself'], 400);
    }
    
    // Deletar
    if ($user->destroy()) {
        // Log da ação
        if ($logAction) {
            error_log(json_encode([
                'action' => 'user_deleted',
                'user_id' => $userId,
                'deleted_by' => $authUser->id,
                'timestamp' => date('Y-m-d H:i:s')
            ]));
        }
        
        return $this->response(['message' => 'User deleted successfully']);
    }
    
    return $this->error(['message' => 'Failed to delete user'], 500);
}
```

---

## 📊 Fluxo 5: Upload de Arquivo

### Requisição
```http
POST /users/123/avatar HTTP/1.1
Host: localhost
Authorization: Bearer eyJhbGc...
Content-Type: multipart/form-data; boundary=----WebKitFormBoundary

------WebKitFormBoundary
Content-Disposition: form-data; name="avatar"; filename="photo.jpg"
Content-Type: image/jpeg

[binary data]
------WebKitFormBoundary--
```

### Fluxo de Dados
```
REQUISIÇÃO (multipart/form-data)
    ↓
Router extrai dados
    ↓
$data = [
    'id' => '123',              // Route param {id}
    'files' => [
        'avatar' => [
            'name' => 'photo.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => '/tmp/phpXXXXXX',
            'error' => 0,
            'size' => 524288
        ]
    ]
]
    ↓
AuthMiddleware executa
    ↓
$middlewareData = ['user' => {...}]
    ↓
UserController->uploadAvatar($data, $middlewareData)
    ↓
┌────────────────────────────────────────────────────┐
│ 1. Verificar se é o próprio usuário                │
│    $authUserId = $middlewareData['user']->id       │
│    $targetUserId = $data['id']                     │
│    if ($authUserId != $targetUserId) error(403)    │
│                                                    │
│ 2. Validar arquivo                                 │
│    $file = $data['files']['avatar']                │
│    - Verificar se foi enviado                      │
│    - Verificar tipo (MIME)                         │
│    - Verificar tamanho                             │
│                                                    │
│ 3. Processar upload                                │
│    - Gerar nome único                              │
│    - Mover para diretório                          │
│    - Atualizar banco de dados                      │
│                                                    │
│ 4. Retornar sucesso com URL                        │
└────────────────────────────────────────────────────┘
    ↓
RESPONSE
{
    "status": "success",
    "avatar_url": "/uploads/avatars/5f3b2e1a9c4d8.jpg"
}
```

### Código do Controller
```php
public function uploadAvatar($data, $middlewareData)
{
    $authUser = $middlewareData['user'];
    $targetUserId = $data['id'];
    
    // 1. Verificar permissão
    if ($authUser->id != $targetUserId && $authUser->role !== 'admin') {
        return $this->error(['message' => 'Unauthorized'], 403);
    }
    
    // 2. Verificar se arquivo foi enviado
    if (empty($data['files']['avatar'])) {
        return $this->error(['message' => 'Avatar file is required'], 422);
    }
    
    $file = $data['files']['avatar'];
    
    // 3. Validar upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return $this->error(['message' => 'File upload error'], 400);
    }
    
    // 4. Validar tipo
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        return $this->error(['message' => 'Invalid file type. Only JPG, PNG, GIF allowed'], 422);
    }
    
    // 5. Validar tamanho (2MB max)
    $maxSize = 2 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        return $this->error(['message' => 'File too large. Max 2MB'], 422);
    }
    
    // 6. Criar diretório se não existir
    $uploadDir = __DIR__ . '/../../uploads/avatars/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // 7. Gerar nome único
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $destination = $uploadDir . $filename;
    
    // 8. Mover arquivo
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return $this->error(['message' => 'Failed to save file'], 500);
    }
    
    // 9. Atualizar banco
    $user = (new User)->findById($targetUserId);
    
    // Deletar avatar antigo se existir
    if ($user->avatar && file_exists($uploadDir . $user->avatar)) {
        unlink($uploadDir . $user->avatar);
    }
    
    $user->avatar = $filename;
    
    if (!$user->save()) {
        // Rollback: deletar arquivo se falhar ao salvar no banco
        unlink($destination);
        return $this->error(['message' => 'Failed to update user'], 500);
    }
    
    // 10. Retornar sucesso
    return $this->response([
        'message' => 'Avatar uploaded successfully',
        'avatar_url' => "/uploads/avatars/{$filename}"
    ]);
}
```

---

## 📊 Fluxo 6: Relacionamentos (Posts com Autor)

### Requisição
```http
GET /posts/789 HTTP/1.1
Host: localhost
```

### Model com Relacionamento
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
    
    /**
     * Busca o autor do post
     */
    public function getAuthor()
    {
        $db = Connect::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT id, name, email FROM users WHERE id = :user_id');
        $stmt->execute(['user_id' => $this->user_id]);
        return $stmt->fetch(\PDO::FETCH_OBJ);
    }
    
    /**
     * Busca post com autor em uma query (JOIN)
     */
    public static function findWithAuthor($postId)
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

### Controller
```php
public function show($data, $middlewareData)
{
    $postId = $data['id'];
    
    // Opção 1: Buscar separadamente (2 queries)
    $post = (new Post)->findById($postId);
    
    if (!$post) {
        return $this->error(['message' => 'Post not found'], 404);
    }
    
    $author = $post->getAuthor();
    
    return $this->response([
        'post' => $post->data,
        'author' => $author
    ]);
    
    // OU
    
    // Opção 2: Buscar com JOIN (1 query)
    $post = Post::findWithAuthor($postId);
    
    if (!$post) {
        return $this->error(['message' => 'Post not found'], 404);
    }
    
    return $this->response(['post' => $post]);
}
```

### Response
```json
{
    "status": "success",
    "post": {
        "id": 789,
        "title": "My First Post",
        "content": "...",
        "created_at": "2024-01-15 10:30:00",
        "author_id": 123,
        "author_name": "John Doe",
        "author_email": "john@example.com"
    }
}
```

---

## 📊 Diagrama: Composição de $data

```
┌─────────────────────────────────────────────────────────────────┐
│                      REQUEST                                     │
│                                                                  │
│  GET /users/123/posts?page=2&limit=10                           │
│  Authorization: Bearer TOKEN                                     │
│  Content-Type: application/json                                 │
│                                                                  │
│  Body:                                                           │
│  {                                                               │
│      "status": "published",                                      │
│      "category": "tech"                                          │
│  }                                                               │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│                    Router Coleta Dados                           │
│                                                                  │
│  1. Route params (/users/{user_id}/posts)                       │
│     → user_id = '123'                                            │
│                                                                  │
│  2. Query string (?page=2&limit=10)                             │
│     → page = '2'                                                 │
│     → limit = '10'                                               │
│                                                                  │
│  3. JSON body                                                    │
│     → status = 'published'                                       │
│     → category = 'tech'                                          │
│                                                                  │
│  4. Merge tudo em $data                                          │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│                    $data COMPLETO                                │
│                                                                  │
│  [                                                               │
│      'user_id' => '123',        ← Route param                   │
│      'page' => '2',             ← Query param                   │
│      'limit' => '10',           ← Query param                   │
│      'status' => 'published',   ← JSON body                     │
│      'category' => 'tech'       ← JSON body                     │
│  ]                                                               │
└─────────────────────────────────────────────────────────────────┘
                            ↓
                    Controller recebe
```

---

## 🎯 Resumo Visual dos 3 Parâmetros do Controller

```php
public function method($data, $middlewareData, $injectedData)
```

### $data
```
┌─────────────────────────────────────┐
│ Origem: REQUEST                     │
├─────────────────────────────────────┤
│ • Route params:    {id}, {slug}     │
│ • Query string:    ?page=1          │
│ • POST/PUT data:   form-data        │
│ • JSON body:       {"key":"value"}  │
│ • Files:           $_FILES          │
└─────────────────────────────────────┘
```

### $middlewareData
```
┌─────────────────────────────────────┐
│ Origem: MIDDLEWARES                 │
├─────────────────────────────────────┤
│ Dados retornados por middlewares    │
│                                     │
│ Exemplo:                            │
│ AuthMiddleware retorna:             │
│   ['user' => {...}]                 │
│                                     │
│ LogMiddleware retorna:              │
│   ['request_id' => 'abc123']        │
│                                     │
│ $middlewareData = [                 │
│     'user' => {...},                │
│     'request_id' => 'abc123'        │
│ ]                                   │
└─────────────────────────────────────┘
```

### $injectedData
```
┌─────────────────────────────────────┐
│ Origem: DEFINIÇÃO DA ROTA           │
├─────────────────────────────────────┤
│ Dados definidos ao criar rota       │
│                                     │
│ Exemplo de rota:                    │
│ $router->delete(                    │
│     '/users/{id}',                  │
│     'UserController@delete',        │
│     ['AuthMiddleware@handle'],      │
│     ['permission' => 'users.delete',│
│      'log' => true]  ← ESTE ARRAY   │
│ );                                  │
│                                     │
│ $injectedData = [                   │
│     'permission' => 'users.delete', │
│     'log' => true                   │
│ ]                                   │
└─────────────────────────────────────┘
```

---

**Fim dos Exemplos de Fluxo**

