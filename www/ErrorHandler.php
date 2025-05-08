<?php

class ErrorHandler {
    public static function handle($errno, $errstr, $errfile, $errline) {
        if (!(error_reporting() & $errno)) {
            return false;
        }

        $error = [
            'error' => true,
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
            'type' => self::getErrorType($errno)
        ];

        self::sendJsonResponse($error, 500);
        return true;
    }

    public static function handleException($exception) {
        $error = [
            'error' => true,
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTrace(),
            'type' => 'Exception'
        ];

        self::sendJsonResponse($error, 500);
    }

    public static function handleFatalError() {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $error = [
                'error' => true,
                'message' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line'],
                'type' => self::getErrorType($error['type'])
            ];

            self::sendJsonResponse($error, 500);
        }
    }

    private static function getErrorType($errno) {
        $types = [
            E_ERROR => 'E_ERROR',
            E_WARNING => 'E_WARNING',
            E_PARSE => 'E_PARSE',
            E_NOTICE => 'E_NOTICE',
            E_CORE_ERROR => 'E_CORE_ERROR',
            E_CORE_WARNING => 'E_CORE_WARNING',
            E_COMPILE_ERROR => 'E_COMPILE_ERROR',
            E_COMPILE_WARNING => 'E_COMPILE_WARNING',
            E_USER_ERROR => 'E_USER_ERROR',
            E_USER_WARNING => 'E_USER_WARNING',
            E_USER_NOTICE => 'E_USER_NOTICE',
            E_STRICT => 'E_STRICT',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_DEPRECATED => 'E_DEPRECATED',
            E_USER_DEPRECATED => 'E_USER_DEPRECATED'
        ];

        return $types[$errno] ?? 'UNKNOWN';
    }

    private static function sendJsonResponse($data, $statusCode) {
        http_response_code($statusCode);
        // header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }
} 