<?php

namespace App\Middleware;

class ExampleMiddleware {
    public function handle() {
        // Implemente sua lógica aqui
        
        // Retorne null se não quiser passar dados para o controller
        // ou retorne um array com os dados que deseja passar
        return null;
    }

    public function logs($data, $injectData) {
        // Implemente sua lógica aqui
        
        // Retorne null se não quiser passar dados para o controller
        // ou retorne um array com os dados que deseja passar
        return [
            'data' => $data,
            'injectData' => $injectData,
            'log' => 'logs example'
        ];
    }
} 