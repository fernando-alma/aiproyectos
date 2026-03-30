<?php
namespace Tests;

class DashboardApiTest extends TestCase
{
    private string $baseUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->baseUrl = rtrim($_ENV['API_BASE_URL'] ?? 'http://localhost/aiproyectos/backend/public', '/');
    }

    public function test_can_get_all_dashboards(): void
    {
        $response = $this->http->get("{$this->baseUrl}/dashboards");
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_can_create_dashboard_as_admin(): void
    {
        $token = $this->authenticateAdmin();
        $response = $this->http->post("{$this->baseUrl}/dashboard/create", [
            'headers' => ['Authorization' => "Bearer {$token}"],
            'form_params' => [
                'title' => 'Dashboard Test',
                'description' => 'Descripcion hackathon',
                'color' => 'blue'
            ]
        ]);
        
        $this->assertTrue(in_array($response->getStatusCode(), [200, 201]), (string)$response->getBody());
    }

    public function test_can_get_dashboard(): void
    {
        $this->test_can_create_dashboard_as_admin();

        // Si creamos "Dashboard Test", su slug será "dashboard-test"
        $response = $this->http->get("{$this->baseUrl}/dashboard/get?slug=dashboard-test");
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_can_update_dashboard(): void
    {
        $token = $this->authenticateAdmin();
        $this->http->post("{$this->baseUrl}/dashboard/create", [
            'headers' => ['Authorization' => "Bearer {$token}"],
            'form_params' => ['title' => 'Update Me', 'description' => 'A']
        ]);

        $response = $this->http->post("{$this->baseUrl}/dashboard/update", [
            'headers' => ['Authorization' => "Bearer {$token}"],
            'form_params' => [
                'slug' => 'update-me',
                'title' => 'Updated Dashboard',
                'description' => 'New Description'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_can_delete_dashboard(): void
    {
        $token = $this->authenticateAdmin();
        $this->http->post("{$this->baseUrl}/dashboard/create", [
            'headers' => ['Authorization' => "Bearer {$token}"],
            'form_params' => ['title' => 'Delete Me', 'description' => 'A']
        ]);

        $response = $this->http->post("{$this->baseUrl}/dashboard/delete", [
            'headers' => ['Authorization' => "Bearer {$token}"],
            'form_params' => ['slug' => 'delete-me']
        ]);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
