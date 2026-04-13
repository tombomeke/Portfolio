<?php

require_once __DIR__ . '/../app/Services/ProjectRoadmapService.php';

class FakeProjectRoadmapService extends ProjectRoadmapService {
    private array $fakeResponse;
    private $fakeModel;

    public function __construct(array $fakeResponse, string $storagePath, $fakeModel = null) {
        parent::__construct('https://example.invalid/api', $storagePath);
        $this->fakeResponse = $fakeResponse;
        $this->fakeModel = $fakeModel;
    }

    protected function callApi(string $repoUrl, ?array $authUser = null): array {
        return $this->fakeResponse;
    }

    protected function getModel(): ProjectRoadmapModel {
        if ($this->fakeModel !== null) {
            return $this->fakeModel;
        }
        return parent::getModel();
    }
}

class FakeProjectRoadmapModel extends ProjectRoadmapModel {
    public array $requestedProjectIds = [];

    public function getSyncSummaryByProjectIds(array $projectIds): array {
        $this->requestedProjectIds = array_values(array_map('intval', $projectIds));
        return [
            10 => [
                'lastSyncAt' => '2026-04-13 10:00:00',
                'openCount' => 2,
                'doneCount' => 1,
                'totalCount' => 3,
            ],
            11 => [
                'lastSyncAt' => null,
                'openCount' => 0,
                'doneCount' => 0,
                'totalCount' => 0,
            ],
        ];
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

$fakeModel = new FakeProjectRoadmapModel();
$summaryService = new FakeProjectRoadmapService(['ok' => true, 'data' => ['todos' => []]], $tmpStore . '.summary', $fakeModel);
$summary = $summaryService->getSyncSummary([10, 11]);
assertTrue($fakeModel->requestedProjectIds === [10, 11], 'service should pass project ids to aggregated summary method');
assertTrue((string) ($summary[10]['lastSyncAt'] ?? '') === '2026-04-13 10:00:00', 'summary should return last sync timestamp');
assertTrue((int) ($summary[10]['openCount'] ?? -1) === 2, 'summary should return open count');
assertTrue((int) ($summary[10]['doneCount'] ?? -1) === 1, 'summary should return done count');
assertTrue((int) ($summary[10]['totalCount'] ?? -1) === 3, 'summary should return total count');

$serviceNoRepo = new FakeProjectRoadmapService(['ok' => true, 'data' => ['todos' => []]], $tmpStore . '.2');
$noRepoResult = $serviceNoRepo->syncProjectRoadmap(['id' => 1, 'slug' => 'x'], null);
assertTrue(($noRepoResult['ok'] ?? true) === false, 'sync should fail when repo_url missing');

echo "ProjectRoadmapServiceTest passed\n";
