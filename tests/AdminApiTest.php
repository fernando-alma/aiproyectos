<?php
namespace Tests;

class AdminApiTest extends TestCase
{
    private string $baseUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->baseUrl = rtrim($_ENV['API_BASE_URL'] ?? 'http://localhost/aiproyectos/backend/public', '/');
    }

    public function test_admin_can_get_users(): void
    {
        $token = $this->authenticateAdmin();
        $response = $this->http->get("{$this->baseUrl}/admin/users", [
            'headers' => ['Authorization' => "Bearer {$token}"]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_admin_can_get_stats(): void
    {
        $token = $this->authenticateAdmin();
        $response = $this->http->get("{$this->baseUrl}/admin/stats", [
            'headers' => ['Authorization' => "Bearer {$token}"]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_admin_can_change_role(): void
    {
        $token = $this->authenticateAdmin();
        
        // Creamos un usuario normal para cambiarle su rol
        $userToken = $this->authenticateUser(); // Esto llenará la DB temporalmente
        
        // Extraer id del JWT
        $parts = explode('.', $userToken);
        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
        $userId = $payload['sub'];

        $response = $this->http->post("{$this->baseUrl}/admin/changeRole", [
            'headers' => ['Authorization' => "Bearer {$token}"],
            'form_params' => [
                'user_id' => $userId,
                'role' => 'admin'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode(), (string)$response->getBody());
    }
}
