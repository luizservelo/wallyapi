<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Password;
use App\Models\ExampleUser;

class ExampleUserController extends Controller {
    public function create($data, $middlewareData) {

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

        if(empty($data['user_name'])) {
            return $this->response([
                'message' => 'Name is required'
            ], 400);
        }

        $user = (new ExampleUser)->find('user_email = :email', 'email=' . $data['user_email'])->fetch();

        if($user) {
            return $this->response([
                'message' => 'User already exists'
            ], 400);
        }

        $user = new ExampleUser;

        $user->user_email = $data['user_email'];
        $user->user_password = Password::hash($data['user_password']);
        $user->user_name = $data['user_name'];
        
        if($user->save()) {
            return $this->response([
                'message' => 'User created successfully',
                'user' => $user->data
            ], 201);
        }
        else {
            return $this->response([
                'message' => 'User not created',
                'error' => $user->fail()
            ], 400);
        }
        

        return $this->response([
            'message' => 'Hello world'
        ]);
    }

    public function index($data, $middlewareData) {
        $users = (new ExampleUser)->find("", "", "user_id, user_email, user_name")->fetch(true, true);
        return $this->response([
            'users' => $users
        ]);
    }

    public function show($data, $middlewareData) {
        $user = (new ExampleUser)->findById($data['user_id'], "user_id, user_email, user_name");

        if(!$user) {
            return $this->error([
                'message' => 'User not found'
            ], 404);
        }

        return $this->response([
            'user' => $user->data
        ]);
    }

    public function update($data, $middlewareData) {
        $user = (new ExampleUser)->findById($data['user_id'], "user_id, user_email, user_name")->fetch();

        if(!$user) {
            return $this->error([
                'message' => 'User not found'
            ], 404);
        }
        
        if(empty($data['user_email'])) {
            return $this->response([
                'message' => 'Email is required'
            ], 400);
        }

        if(empty($data['user_name'])) {
            return $this->response([
                'message' => 'Name is required'
            ], 400);
        }

        $user->user_email = $data['user_email'];
        $user->user_name = $data['user_name'];

        if(!empty($data['user_password'])) {
            $user->user_password = Password::hash($data['user_password']);
        }

        if($user->save()) {
            return $this->response([
                'user' => $user->data
            ]);
        }
        else{
            return $this->error([
                'message' => 'User not updated',
                'error' => $user->fail()
            ], 400);
        }
    }

    public function delete($data, $middlewareData, $injectedData) {

        $authUser = $middlewareData['user'];

        if(!in_array($injectedData['permissions'], $authUser['permissions'])) {
            return $this->error([
                'message' => 'You are not allowed to delete this user'
            ], 403);
        }

        $user = (new ExampleUser)->findById($data['user_id'], "user_id, user_email, user_name");

        if(!$user) {
            return $this->error([
                'message' => 'User not found'
            ], 404);
        }

        if($user->destroy()) {
            return $this->response([
                'message' => 'User deleted successfully'
            ], 200);    
        }
        else{
            return $this->error([
                'message' => 'User not deleted',
                'error' => $user->fail()
            ], 400);
        }
    }
} 