<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Log;

class SSOTokenService
{
    private string $secretKey;

    public function __construct()
    {
        // IMPORTANTE: Usar la misma clave que el portal (APP_KEY)
        $key = config('app.key');
        
        Log::debug('SSO Token Service - Clave original', [
            'key_length' => strlen($key),
            'has_base64_prefix' => str_starts_with($key, 'base64:'),
            'key_preview' => substr($key, 0, 20) . '...'
        ]);
        
        // Laravel siempre incluye el prefijo base64: en APP_KEY, decodificarlo
        if (str_starts_with($key, 'base64:')) {
            $this->secretKey = base64_decode(substr($key, 7));
            Log::debug('SSO Token Service - Clave decodificada', [
                'decoded_length' => strlen($this->secretKey)
            ]);
        } else {
            $this->secretKey = $key;
            Log::debug('SSO Token Service - Usando clave sin decodificar');
        }
    }

    /**
     * Valida un token JWT y retorna los datos del usuario
     *
     * @param string $token
     * @return array|null
     */
    public function validateToken(string $token): ?array
    {
        try {
            Log::debug('Validando token SSO', [
                'token_length' => strlen($token),
                'token_preview' => substr($token, 0, 50) . '...',
                'secret_key_length' => strlen($this->secretKey)
            ]);
            
            $decoded = JWT::decode($token, new Key($this->secretKey, 'HS256'));
            
            Log::info('Token SSO decodificado exitosamente', [
                'exp' => $decoded->exp ?? 'no set',
                'iat' => $decoded->iat ?? 'no set',
                'iss' => $decoded->iss ?? 'no set'
            ]);
            
            // Verificar que no haya expirado
            if (isset($decoded->exp) && $decoded->exp < time()) {
                Log::warning('Token SSO expirado', ['exp' => $decoded->exp, 'now' => time()]);
                return null;
            }
            
            // Retornar datos del usuario
            return (array) $decoded->data;
        } catch (\Firebase\JWT\ExpiredException $e) {
            Log::warning('Token SSO expirado: ' . $e->getMessage());
            return null;
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            Log::error('Firma de token SSO inválida: ' . $e->getMessage(), [
                'secret_key_length' => strlen($this->secretKey)
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Error validando token SSO: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Genera un token JWT para SSO (opcional, si este sistema también genera tokens)
     *
     * @param array $userData
     * @param int $expirationMinutes
     * @return string
     */
    public function generateToken(array $userData, int $expirationMinutes = 5): string
    {
        $payload = [
            'iss' => config('app.url'), // Emisor
            'iat' => time(), // Tiempo de emisión
            'exp' => time() + ($expirationMinutes * 60), // Expiración
            'data' => $userData
        ];

        return JWT::encode($payload, $this->secretKey, 'HS256');
    }
}
