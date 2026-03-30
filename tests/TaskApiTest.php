<?php
namespace Tests;

class TaskApiTest extends TestCase
{
    private string $baseUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->baseUrl = rtrim($_ENV['API_BASE_URL'] ?? 'http://localhost/aiproyectos/backend/public', '/');
    }

    private function createProjectAndReturnId(string $token): int
    {
        $adminToken = $this->authenticateAdmin();
        $this->http->post("{$this->baseUrl}/dashboard/create", [
            'headers' => ['Authorization' => "Bearer {$adminToken}"],
            'form_params' => ['title' => 'Tasks Dash', 'description' => 'D']
        ]);
        $response = $this->http->post("{$this->baseUrl}/project/create", [
            'headers' => ['Authorization' => "Bearer {$token}"],
            'multipart' => [
                ['name' => 'title', 'contents' => 'Tasks Project'],
                ['name' => 'description', 'contents' => 'P'],
                ['name' => 'slug', 'contents' => 'tasks-dash']
            ]
        ]);
        $body = json_decode((string)$response->getBody(), true);
        return $body['project_id'] ?? 1;
    }

    public function test_can_create_and_get_tasks(): void
    {
        $token = $this->authenticateUser();
        $projectId = $this->createProjectAndReturnId($token);

        $resCreate = $this->http->post("{$this->baseUrl}/task/create", [
            'headers' => ['Authorization' => "Bearer {$token}"],
            'form_params' => [
                'project_id' => $projectId,
                'title' => 'New Task',
                'description' => 'Desc for generic task',
                'due_date' => '2026-10-10',
                'priority' => 'high'
            ]
        ]);
        $this->assertTrue(in_array($resCreate->getStatusCode(), [200, 201]), (string)$resCreate->getBody());

        $resGet = $this->http->get("{$this->baseUrl}/task/getTasks?id={$projectId}", [
            'headers' => ['Authorization' => "Bearer {$token}"]
        ]);
        $this->assertEquals(200, $resGet->getStatusCode());
    }

    public function test_can_update_task_status(): void
    {
        $token = $this->authenticateUser();
        $projectId = $this->createProjectAndReturnId($token);

        $this->http->post("{$this->baseUrl}/task/create", [
            'headers' => ['Authorization' => "Bearer {$token}"],
            'form_params' => ['project_id' => $projectId, 'title' => 'Status Task', 'description'=>'A', 'due_date'=>'2026-10-10', 'priority'=>'low']
        ]);

        $resList = $this->http->get("{$this->baseUrl}/task/getTasks?id={$projectId}", [
            'headers' => ['Authorization' => "Bearer {$token}"]
        ]);
        
        $tasks = json_decode((string)$resList->getBody(), true)['tasks'] ?? [];
        $taskId = count($tasks) > 0 ? $tasks[0]['id'] : 1;

        $resStatus = $this->http->post("{$this->baseUrl}/task/updateStatus", [
            'headers' => ['Authorization' => "Bearer {$token}"],
            'form_params' => ['task_id' => $taskId, 'status' => 'completed']
        ]);
        $this->assertEquals(200, $resStatus->getStatusCode(), (string)$resStatus->getBody());
    }
}
