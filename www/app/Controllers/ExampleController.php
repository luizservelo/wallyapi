<?php

namespace App\Controllers;

use App\Core\Controller;

class ExampleController extends Controller {
    public function index($data, $middlewareData) {
        return $this->response([
            'message' => 'Hello world'
        ]);
    }

    public function testGet($data, $middlewareData) {
        return $this->response([
            'message' => 'Hello world',
            'data' => $data,
            'middlewareData' => $middlewareData
        ]);
    }

    public function testPost($data, $middlewareData) {
        return $this->response([
            'message' => 'Hello world',
            'data' => $data,
            'middlewareData' => $middlewareData
        ]);
    }

    public function testPut($data, $middlewareData) {
        return $this->response([
            'message' => 'Hello world',
            'data' => $data,
            'middlewareData' => $middlewareData
        ]);
    }

    public function testDelete($data, $middlewareData) {
        return $this->response([
            'message' => 'Hello world',
            'data' => $data,
            'middlewareData' => $middlewareData
        ]);
    }
} 
