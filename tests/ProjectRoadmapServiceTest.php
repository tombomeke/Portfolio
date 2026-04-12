<?php

require_once __DIR__ . '/../app/Services/ProjectRoadmapService.php';

class FakeProjectRoadmapService extends ProjectRoadmapService {
    private array $fakeResponse;

    public function __construct(array $fakeResponse, string $storagePath) {
        parent::__construct('https://example.invalid/api', $storagePath);
        $this->fakeResponse = $fakeResponse;
    }

    protected function callApi(string $repoUrl, ?array $authUser = null): array {
        return $this->fakeResponse;
    }
}

function assertTrue(bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException('Assertion failed: ' . $message);
    }
}

$tmpStore = sys_get_temp_dir() . '/project-roadmap-test-' . bin2hex(random_bytes(4)) . '.json';

$service = new FakeProjectRoadmapService([
    'ok' => true,
    'data' => [
        'apiContractVersion' => 'readmesync-api-todos-v2.2',
        'todos' => [
            [
                'file' => 'src/Feature/Foo.php',
                'line' => 42,
                'text' => 'Refactor extraction to service',
                'status' => 'open',
                'priority' => 'high',
            ],
        ],
    ],
], $tmpStore);

$project = [
    'id' => 10,
    'slug' => 'portfolio',
    'title' => 'Portfolio',
    'repo_url' => 'https://github.com/tombomeke/Portfolio',
];

$sync = $service->syncProjectRoadmap($project, ['id' => 1, 'username' => 'tester']);
assertTrue(($sync['ok'] ?? false) === true, 'sync should succeed');
assertTrue((int) ($sync['itemCount'] ?? 0) === 1, 'sync should store one item');

$record = $service->getProjectRoadmap($project);
assertTrue((int) ($record['projectId'] ?? 0) === 10, 'record should contain projectId');
assertTrue((string) ($record['apiContractVersion'] ?? '') === 'readmesync-api-todos-v2.2', 'contract version should be preserved');
assertTrue(isset($record['items'][0]['file'], $record['items'][0]['line'], $record['items'][0]['text'], $record['items'][0]['status'], $record['items'][0]['priority']), 'todo shape should include required keys');

$serviceNoRepo = new FakeProjectRoadmapService(['ok' => true, 'data' => ['todos' => []]], $tmpStore . '.2');
$noRepoResult = $serviceNoRepo->syncProjectRoadmap(['id' => 1, 'slug' => 'x'], null);
assertTrue(($noRepoResult['ok'] ?? true) === false, 'sync should fail when repo_url missing');

echo "ProjectRoadmapServiceTest passed\n";
