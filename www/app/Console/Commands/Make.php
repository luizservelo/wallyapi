<?php

namespace App\Console\Commands;

class Make {
    private $basePath;
    private $stubsPath;

    public function __construct() {
        $this->basePath = dirname(__DIR__, 2);
        $this->stubsPath = $this->basePath . '/stubs';
    }

    public function run($args) {
        if (count($args) < 2) {
            $this->showHelp();
            return;
        }

        $command = $args[1];
        $name = $args[2] ?? null;

        if (!$name) {
            echo "Erro: Nome é obrigatório\n";
            $this->showHelp();
            return;
        }

        switch ($command) {
            case 'controller':
                $this->makeController($name);
                break;
            case 'model':
                $this->makeModel($name);
                break;
            case 'middleware':
                $this->makeMiddleware($name);
                break;
            default:
                echo "Comando inválido\n";
                $this->showHelp();
        }
    }

    private function makeController($name) {
        $stub = file_get_contents($this->stubsPath . '/controller.stub');
        $content = str_replace('{{name}}', $name, $stub);
        
        $path = $this->basePath . '/Controllers/' . $name . 'Controller.php';
        $this->writeFile($path, $content);
        
        echo "Controller criado: $path\n";
    }

    private function makeModel($name) {
        $stub = file_get_contents($this->stubsPath . '/model.stub');
        $content = str_replace('{{name}}', $name, $stub);
        
        $path = $this->basePath . '/Models/' . $name . '.php';
        $this->writeFile($path, $content);
        
        echo "Model criado: $path\n";
    }

    private function makeMiddleware($name) {
        $stub = file_get_contents($this->stubsPath . '/middleware.stub');
        $content = str_replace('{{name}}', $name, $stub);
        
        $path = $this->basePath . '/Middleware/' . $name . 'Middleware.php';
        $this->writeFile($path, $content);
        
        echo "Middleware criado: $path\n";
    }

    private function writeFile($path, $content) {
        $directory = dirname($path);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        file_put_contents($path, $content);
    }

    private function showHelp() {
        echo "Uso: php make.php [comando] [nome]\n\n";
        echo "Comandos disponíveis:\n";
        echo "  controller [nome]  Cria um novo controller\n";
        echo "  model [nome]       Cria um novo model\n";
        echo "  middleware [nome]  Cria um novo middleware\n";
    }
} 