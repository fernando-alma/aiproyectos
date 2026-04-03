<?php
namespace Tests;

class AuthApiTest extends TestCase
{
    private string $baseUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->baseUrl = rtrim($_ENV['API_BASE_URL'] ?? 'http://localhost/aiproyectos/backend/public', '/');
    }

    public function test_user_can_register(): void
    {
        $userData = [
            'name' => 'Usuario Test',
            'email' => 'test_auth@example.com',
            'password' => 'password123',
            'password_confirm' => 'password123'
        ];

        $response = $this->http->post("{$this->baseUrl}/auth/register", [
            'json' => $userData
        ]);

        $this->assertEquals(201, $response->getStatusCode());
    }

    public function test_user_can_login(): void
    {
        // Registrar primero para que la clave tenga un hash válido (password_hash() de PHP)
        $this->test_user_can_register(); 

        $response = $this->http->post("{$this->baseUrl}/auth/login", [
            'json' => [
                'email' => 'test_auth@example.com',
                'password' => 'password123'
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('token', $body);
    }

    public function test_user_can_get_me(): void
    {
        $token = $this->authenticateUser();
        
        $response = $this->http->get("{$this->baseUrl}/auth/me", [
            'headers' => [
                'Authorization' => "Bearer {$token}"
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_user_can_logout(): void
    {
        $token = $this->authenticateUser();
        
        $response = $this->http->post("{$this->baseUrl}/auth/logout", [
            'headers' => [
                'Authorization' => "Bearer {$token}"
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_user_can_change_password(): void
    {
        // Registrar y loguear
        $this->test_user_can_register();
        
        $resLogin = $this->http->post("{$this->baseUrl}/auth/login", [
            'json' => [
                'email' => 'test_auth@example.com',
                'password' => 'password123'
            ]
        ]);
        $token = json_decode((string)$resLogin->getBody(), true)['token'] ?? '';

        $response = $this->http->post("{$this->baseUrl}/auth/change-password", [
            'headers' => ['Authorization' => "Bearer {$token}"],
            'json' => [
                'current_password' => 'password123',
                'new_password' => 'NewPassword456',
                'new_password_confirm' => 'NewPassword456'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_user_can_update_profile(): void
    {
        $token = $this->authenticateUser();
        
        $response = $this->http->post("{$this->baseUrl}/auth/update-profile", [
            'headers' => ['Authorization' => "Bearer {$token}"],
            'json' => [
                'name' => 'Nombre Modificado'
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode((string)$response->getBody(), true);
        $this->assertTrue($body['success']);
    }

    public function test_user_can_request_password_reset(): void
    {
        // Registrar primero
        $this->test_user_can_register();

        $response = $this->http->post("{$this->baseUrl}/auth/forgot-password", [
            'json' => [
                'email' => 'test_auth@example.com'
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode((string)$response->getBody(), true);
        $this->assertTrue($body['success']);
        
        // El test de reset-password completo implicaría interceptar el token generado en DB. 
        // Como es una prueba funcional de caja negra y no usamos DB mockeada separada aquí,
        // validamos hasta el step de request que ya es una gran cobertura.
    }
}
