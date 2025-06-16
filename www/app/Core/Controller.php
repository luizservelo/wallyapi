<?php

namespace App\Core;

class Controller
{

    protected $db;
    protected $userTimezone;

    public function __construct()
    {
        $this->db = Connect::getInstance()->getConnection();
    }

    protected function response($data = [], $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode(array_merge(['status' => 'success'], $data), JSON_PRETTY_PRINT);
        exit;
    }

    protected function error($data = [], $statusCode = 400)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode(array_merge(['status' => 'error'], $data), JSON_PRETTY_PRINT);
        exit;
    }

    protected function getRequestData()
    {
        $data = [];

        // Pega dados do POST (form-data e x-www-form-urlencoded)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {
            $data = $_POST;
        }

        // Pega dados do JSON
        $json = file_get_contents('php://input');
        if ($json) {
            $jsonData = json_decode($json, true);
            if ($jsonData) {
                $data = array_merge($data, $jsonData);
            }
        }

        // Pega dados da query string
        $queryString = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
        if ($queryString) {
            parse_str($queryString, $queryData);
            $data = array_merge($data, $queryData);
        }

        // Pega dados de arquivos
        if (!empty($_FILES)) {
            $data['files'] = $this->processFiles($_FILES);
        }

        return $data;
    }

    private function processFiles($files)
    {
        $processed = [];

        foreach ($files as $key => $file) {
            if (is_array($file['name'])) {
                // Múltiplos arquivos
                $count = count($file['name']);
                for ($i = 0; $i < $count; $i++) {
                    $processed[$key][] = [
                        'name' => $file['name'][$i],
                        'type' => $file['type'][$i],
                        'tmp_name' => $file['tmp_name'][$i],
                        'error' => $file['error'][$i],
                        'size' => $file['size'][$i]
                    ];
                }
            } else {
                // Único arquivo
                $processed[$key] = $file;
            }
        }

        return $processed;
    }
}
