<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/ErrorHandler.php';

use App\Core\CORS;

// Configuração de erros
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Registrar manipuladores de erro
set_error_handler(['ErrorHandler', 'handle']);
set_exception_handler(['ErrorHandler', 'handleException']);
register_shutdown_function(['ErrorHandler', 'handleFatalError']);

// CORS 
CORS::setAllowedOrigins(['*']);
CORS::setAllowedMethods(['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS']);
CORS::setAllowedHeaders(['Content-Type', 'Authorization']);
CORS::setMaxAge(3600);
CORS::handle(); 

// Carregar as rotas
require_once __DIR__ . '/routes/api.php';

