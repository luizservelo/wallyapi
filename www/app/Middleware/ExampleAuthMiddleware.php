<?php

namespace App\Middleware;

use App\Core\JWT;
use App\Models\ExampleUser;

class ExampleAuthMiddleware {
    public function handle() {
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

        $token = str_replace('Bearer ', '', $token);
        
        $payload = JWT::verify($token);
        if (!$payload) {
            http_response_code(401);
            echo json_encode([
                'status' => 'error',
                'message' => 'Token inválido'
            ]);
            exit;
        }

        $user = (new ExampleUser)->findById($payload['user_id'], "user_id, user_email, user_name");

        return [
            'user' => $user->data
        ];
    }
} 