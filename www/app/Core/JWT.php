<?php

namespace App\Core;

class JWT {
    private static $secret = 'sua-chave-secreta-aqui'; // Altere para uma chave segura
    private static $algorithm = 'HS256';

    /**
     * Gera um token JWT
     * @param array $payload Dados a serem armazenados no token
     * @param int $expiration Tempo de expiração em segundos (padrão: 1 hora)
     * @return string Token JWT
     */
    public static function generate(array $payload, int $expiration = 3600): string {
        $header = [
            'alg' => self::$algorithm,
            'typ' => 'JWT'
        ];

        $payload['iat'] = time();
        $payload['exp'] = time() + $expiration;

        $base64UrlHeader = self::base64UrlEncode(json_encode($header));
        $base64UrlPayload = self::base64UrlEncode(json_encode($payload));

        $signature = self::sign($base64UrlHeader . '.' . $base64UrlPayload);
        $base64UrlSignature = self::base64UrlEncode($signature);

        return $base64UrlHeader . '.' . $base64UrlPayload . '.' . $base64UrlSignature;
    }

    /**
     * Verifica se um token JWT é válido
     * @param string $token Token JWT
     * @return array|false Payload do token se válido, false caso contrário
     */
    public static function verify(string $token) {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return false;
        }

        list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = $parts;

        $signature = self::base64UrlDecode($base64UrlSignature);
        $expectedSignature = self::sign($base64UrlHeader . '.' . $base64UrlPayload);

        if (!hash_equals($signature, $expectedSignature)) {
            return false;
        }

        $payload = json_decode(self::base64UrlDecode($base64UrlPayload), true);

        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false;
        }

        return $payload;
    }

    /**
     * Decodifica um token JWT sem verificar a assinatura
     * @param string $token Token JWT
     * @return array|false Payload do token se válido, false caso contrário
     */
    public static function decode(string $token) {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return false;
        }

        $payload = json_decode(self::base64UrlDecode($parts[1]), true);
        
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false;
        }

        return $payload;
    }

    public static function getHeaderToken() {
        $token = $_SERVER['HTTP_AUTHORIZATION'];
        if (!$token) {
            return false;
        }

        return $token;
    }

    /**
     * Gera a assinatura do token
     * @param string $input String a ser assinada
     * @return string Assinatura
     */
    private static function sign(string $input): string {
        return hash_hmac('sha256', $input, self::$secret, true);
    }

    /**
     * Codifica uma string em base64 URL-safe
     * @param string $data String a ser codificada
     * @return string String codificada
     */
    private static function base64UrlEncode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Decodifica uma string em base64 URL-safe
     * @param string $data String a ser decodificada
     * @return string String decodificada
     */
    private static function base64UrlDecode(string $data): string {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}
