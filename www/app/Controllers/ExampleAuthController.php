<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\JWT;
use App\Core\Password;
use App\Models\ExampleUser;

class ExampleAuthController extends Controller {
    public function login($data, $middlewareData) {

        if(empty($data['user_email'])) {
            return $this->response([
                'message' => 'Email is required'
            ], 400);
        }
        
        if(empty($data['user_password'])) {
            return $this->response([
                'message' => 'Password is required'
            ], 400);
        }
        
        $user = (new ExampleUser)->find('user_email = :email', 'email=' . $data['user_email'])->fetch();

        if(!$user) {
            return $this->error([
                'message' => 'User not found'
            ], 404);
        }

        // Debug temporário
        $debug = [
            'senha_digitada' => $data['user_password'],
            'hash_do_banco' => $user->user_password,
            'verificacao' => Password::verify($data['user_password'], $user->user_password)
        ];
        error_log(print_r($debug, true));

        if(!Password::verify($data['user_password'], $user->user_password)) {
            return $this->error([
                'message' => 'Invalid password',
                'debug' => $debug // Temporário para debug
            ], 401);
        }

        $token = JWT::generate([
            'user_id' => $user->user_id
        ], 60 * 60 * 24 * 30);

        return $this->response([
            'token' => $token
        ]);
    }

    public function authenticated($data, $middlewareData) {

        $user = $middlewareData['user'];

        return $this->response([
            'user' => $user
        ]);
    }
} 