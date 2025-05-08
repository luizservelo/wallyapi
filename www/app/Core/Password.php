<?php

namespace App\Core;

class Password
{
    /**
     * Gera um hash seguro da senha
     * @param string $password
     * @return string
     */
    public static function hash(string $password): string
    {
        // Usa ARGON2ID se disponível, senão usa BCRYPT
        $algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
        
        // Configurações recomendadas para ARGON2ID
        $options = [
            'memory_cost' => 4096,     // 4MB
            'time_cost' => 4,          // 4 iterações
            'threads' => 3             // 3 threads
        ];
        
        $hash = password_hash($password, $algo, $options);
        
        // Verifica se o hash foi gerado corretamente
        if (!$hash) {
            throw new \Exception("Erro ao gerar hash da senha");
        }
        
        return $hash;
    }

    /**
     * Verifica se a senha corresponde ao hash
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public static function verify(string $password, string $hash): bool
    {
        if (empty($password) || empty($hash)) {
            return false;
        }
        
        return password_verify($password, $hash);
    }

    /**
     * Verifica se o hash precisa ser atualizado
     * @param string $hash
     * @return bool
     */
    public static function needsRehash(string $hash): bool
    {
        $algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
        $options = [
            'memory_cost' => 4096,
            'time_cost' => 4,
            'threads' => 3
        ];
        
        return password_needs_rehash($hash, $algo, $options);
    }

    /**
     * Testa a geração e verificação de hash
     * @param string $password
     * @return array
     */
    public static function test(string $password): array
    {
        $hash = self::hash($password);
        $verify = self::verify($password, $hash);
        
        return [
            'password' => $password,
            'hash' => $hash,
            'verify' => $verify,
            'needs_rehash' => self::needsRehash($hash)
        ];
    }
} 