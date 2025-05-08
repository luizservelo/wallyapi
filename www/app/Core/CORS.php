<?php

namespace App\Core;

class CORS
{
    // Configurações padrão
    private static $allowedOrigins = ['*']; // Pode ser alterado para domínios específicos
    private static $allowedMethods = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'];
    private static $allowedHeaders = ['Content-Type', 'Authorization', 'X-Requested-With'];
    private static $exposedHeaders = [];
    private static $maxAge = 86400;

    public static function handle()
    {
        // Origem da requisição
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
        if (in_array('*', self::$allowedOrigins) || in_array($origin, self::$allowedOrigins)) {
            header('Access-Control-Allow-Origin: ' . (in_array('*', self::$allowedOrigins) ? '*' : $origin));
        }
        header('Access-Control-Allow-Methods: ' . implode(', ', self::$allowedMethods));
        header('Access-Control-Allow-Headers: ' . implode(', ', self::$allowedHeaders));
        if (!empty(self::$exposedHeaders)) {
            header('Access-Control-Expose-Headers: ' . implode(', ', self::$exposedHeaders));
        }
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: ' . self::$maxAge);

        // Se for uma requisição OPTIONS, responde imediatamente
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }

    // Permite customizar origens permitidas
    public static function setAllowedOrigins(array $origins)
    {
        self::$allowedOrigins = $origins;
    }

    // Permite customizar métodos permitidos
    public static function setAllowedMethods(array $methods)
    {
        self::$allowedMethods = $methods;
    }

    // Permite customizar headers permitidos
    public static function setAllowedHeaders(array $headers)
    {
        self::$allowedHeaders = $headers;
    }

    // Permite customizar headers expostos
    public static function setExposedHeaders(array $headers)
    {
        self::$exposedHeaders = $headers;
    }

    // Permite customizar o max-age
    public static function setMaxAge($seconds)
    {
        self::$maxAge = (int)$seconds;
    }
}
