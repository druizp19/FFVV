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
        // IMPORTANTE: Debe ser la misma clave que el portal principal
        $this->secretKey = env('SSO_SECRET_KEY', 'base64:yCoUf37syuy3prwLtoh1voFf8yZ2u43uckDZtDlU63E=');
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
            $decoded = JWT::decode($token, new Key($this->secretKey, 'HS256'));
            
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
            Log::error('Firma de token SSO inválida: ' . $e->getMessage());
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
