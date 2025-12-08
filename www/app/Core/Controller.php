<?php

namespace App\Core;

use PDO;

/**
 * Base Controller Class
 * 
 * Provides common functionality for all controllers including
 * database access, request handling, and response formatting.
 */
abstract class Controller
{
    /**
     * @var PDO Database connection instance
     */
    protected PDO $db;

    /**
     * Initialize controller with database connection
     */
    public function __construct()
    {
        $this->db = Connect::getInstance()->getConnection();
    }

    /**
     * Send a successful JSON response
     *
     * @param array $data Response data
     * @param int $statusCode HTTP status code
     * @return never
     */
    protected function response(array $data = [], int $statusCode = 200): never
    {
        $this->sendJsonResponse(
            array_merge(['status' => 'success'], $data),
            $statusCode
        );
    }

    /**
     * Send an error JSON response
     *
     * @param array $data Error data
     * @param int $statusCode HTTP status code
     * @return never
     */
    protected function error(array $data = [], int $statusCode = 400): never
    {
        $this->sendJsonResponse(
            array_merge(['status' => 'error'], $data),
            $statusCode
        );
    }

    /**
     * Send JSON response and terminate execution
     *
     * @param array $data Response data
     * @param int $statusCode HTTP status code
     * @return never
     */
    private function sendJsonResponse(array $data, int $statusCode): never
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Get all request data from various sources
     * 
     * Retrieves data from POST, PUT, JSON body, query string, and uploaded files
     *
     * @return array Combined request data
     */
    protected function getRequestData(): array
    {
        $data = [];

        // Get POST/PUT form data
        if (in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'PATCH'], true)) {
            $data = $_POST;
        }

        // Get JSON data from request body
        $jsonData = $this->getJsonInput();
        if (!empty($jsonData)) {
            $data = array_merge($data, $jsonData);
        }

        // Get query string parameters
        $queryData = $this->getQueryStringData();
        if (!empty($queryData)) {
            $data = array_merge($data, $queryData);
        }

        // Get uploaded files
        if (!empty($_FILES)) {
            $data['files'] = $this->processFiles($_FILES);
        }

        return $data;
    }

    /**
     * Get JSON data from request body
     *
     * @return array Decoded JSON data or empty array
     */
    private function getJsonInput(): array
    {
        $json = file_get_contents('php://input');
        
        if (empty($json)) {
            return [];
        }

        $jsonData = json_decode($json, true);
        
        return is_array($jsonData) ? $jsonData : [];
    }

    /**
     * Get query string parameters
     *
     * @return array Query string data
     */
    private function getQueryStringData(): array
    {
        $queryString = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_QUERY);
        
        if (empty($queryString)) {
            return [];
        }

        parse_str($queryString, $queryData);
        
        return $queryData ?? [];
    }

    /**
     * Process uploaded files into normalized structure
     *
     * @param array $files Raw $_FILES array
     * @return array Processed files array
     */
    private function processFiles(array $files): array
    {
        $processed = [];

        foreach ($files as $key => $file) {
            if (!isset($file['name'])) {
                continue;
            }

            if (is_array($file['name'])) {
                // Multiple files
                $processed[$key] = $this->processMultipleFiles($file);
            } else {
                // Single file
                $processed[$key] = $this->processSingleFile($file);
            }
        }

        return $processed;
    }

    /**
     * Process multiple uploaded files
     *
     * @param array $file File data array
     * @return array Processed files
     */
    private function processMultipleFiles(array $file): array
    {
        $processed = [];
        $count = count($file['name']);

        for ($i = 0; $i < $count; $i++) {
            $processed[] = [
                'name' => $file['name'][$i] ?? '',
                'type' => $file['type'][$i] ?? '',
                'tmp_name' => $file['tmp_name'][$i] ?? '',
                'error' => $file['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                'size' => $file['size'][$i] ?? 0
            ];
        }

        return $processed;
    }

    /**
     * Process single uploaded file
     *
     * @param array $file File data array
     * @return array Processed file
     */
    private function processSingleFile(array $file): array
    {
        return [
            'name' => $file['name'] ?? '',
            'type' => $file['type'] ?? '',
            'tmp_name' => $file['tmp_name'] ?? '',
            'error' => $file['error'] ?? UPLOAD_ERR_NO_FILE,
            'size' => $file['size'] ?? 0
        ];
    }
}
