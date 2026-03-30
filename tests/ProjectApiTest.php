<?php
namespace Tests;

class ProjectApiTest extends TestCase
{
    private string $baseUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->baseUrl = rtrim($_ENV['API_BASE_URL'] ?? 'http://localhost/aiproyectos/backend/public', '/');
    }

    private function createDashboardAndProject(string $token): int
    {
        // Admin crea dashboard
        $adminToken = $this->authenticateAdmin();
        $this->http->post("{$this->baseUrl}/dashboard/create", [
            'headers' => ['Authorization' => "Bearer {$adminToken}"],
            'form_params' => ['title' => 'Project Dashboard', 'description' => 'Desc']
        ]);

        // Usuario crea proyecto
        $response = $this->http->post("{$this->baseUrl}/project/create", [
            'headers' => ['Authorization' => "Bearer {$token}"],
            'multipart' => [
                ['name' => 'title', 'contents' => 'My Amazing Project'],
                ['name' => 'description', 'contents' => 'Proj Desc'],
                ['name' => 'slug', 'contents' => 'project-dashboard']
            ]
        ]);
        
        if ($response->getStatusCode() !== 201) {
            var_dump((string)$response->getBody());
        }
        $this->assertEquals(201, $response->getStatusCode(), 'Project creation failed');

        $body = json_decode((string)$response->getBody(), true);
        return $body['project_id'] ?? 1;
    }

    public function test_can_create_project_and_get_all(): void
    {
        $token = $this->authenticateUser();
        $projectId = $this->createDashboardAndProject($token);

        $resGet = $this->http->get("{$this->baseUrl}/project/getProjects?slug=project-dashboard");
        $this->assertEquals(200, $resGet->getStatusCode());
    }

    public function test_can_get_single_project(): void
    {
        $token = $this->authenticateUser();
        $projectId = $this->createDashboardAndProject($token);

        $resGet = $this->http->get("{$this->baseUrl}/project/get?id={$projectId}");
        $this->assertEquals(200, $resGet->getStatusCode());
    }

    public function test_can_update_project(): void
    {
        $token = $this->authenticateUser();
        $projectId = $this->createDashboardAndProject($token);

        $resUp = $this->http->post("{$this->baseUrl}/project/update", [
            'headers' => ['Authorization' => "Bearer {$token}"],
            'multipart' => [
                ['name' => 'id', 'contents' => $projectId],
                ['name' => 'title', 'contents' => 'Updated Project Title']
            ]
        ]);
        $this->assertEquals(200, $resUp->getStatusCode());
    }

    public function test_can_get_stats(): void
    {
        $token = $this->authenticateUser();
        $projectId = $this->createDashboardAndProject($token);

        $res = $this->http->get("{$this->baseUrl}/project/stats?id={$projectId}");
        $this->assertEquals(200, $res->getStatusCode());
    }

    public function test_join_request_flow(): void
    {
        // 1. Owner creador del proyecto
        $ownerToken = $this->authenticateUser(); 
        $projectId = $this->createDashboardAndProject($ownerToken);

        // 2. Otro usuario envía solicitud
        $requesterToken = $this->authenticateAdmin(); // Solo para tener otro token valido
        
        $resReq = $this->http->post("{$this->baseUrl}/project/sendJoinRequest", [
            'headers' => ['Authorization' => "Bearer {$requesterToken}"],
            'form_params' => ['project_id' => $projectId]
        ]);
        // Podría ser 200 o 201
        $this->assertTrue(in_array($resReq->getStatusCode(), [200, 201]), (string)$resReq->getBody());

        // 3. Owner (creador) aprueba solicitud
        // Primero obtener el ID de la solicitud
        $resListReq = $this->http->get("{$this->baseUrl}/project/getJoinRequests?project_id={$projectId}", [
            'headers' => ['Authorization' => "Bearer {$ownerToken}"]
        ]);
        $reqs = json_decode((string)$resListReq->getBody(), true)['requests'] ?? [];
        $reqId = count($reqs) > 0 ? $reqs[0]['id'] : 1;

        $resApprove = $this->http->post("{$this->baseUrl}/project/approveJoinRequest", [
            'headers' => ['Authorization' => "Bearer {$ownerToken}"],
            'form_params' => ['request_id' => $reqId]
        ]);
        $this->assertEquals(200, $resApprove->getStatusCode(), (string)$resApprove->getBody());
    }

    public function test_can_delete_project(): void
    {
        $token = $this->authenticateUser();
        $projectId = $this->createDashboardAndProject($token);

        $resDel = $this->http->post("{$this->baseUrl}/project/delete", [
            'headers' => ['Authorization' => "Bearer {$token}"],
            'form_params' => ['id' => $projectId]
        ]);
        $this->assertEquals(200, $resDel->getStatusCode());
    }
}
