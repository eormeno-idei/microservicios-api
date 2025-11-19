<?php

namespace App\Services\UI\DataTable;

use App\Models\User;
use App\Services\UI\Support\UIDebug;
use Illuminate\Support\Facades\Http;
use App\Services\UI\Support\UIStateManager;

/**
 * User API Table Model
 *
 * Implementation for real User model from database
 */
class UserApiTableModel extends AbstractDataTableModel
{
    public function getColumns(): array
    {
        return [
            'name' => ['label' => 'Name', 'width' => [500, 500]],
            'email' => ['label' => 'Email', 'width' => [400, 400]],
            'email_verified' => ['label' => 'Verified', 'width' => [100, 100]],
            'roles' => ['label' => 'Roles', 'width' => [200, 200]],
            'updated_at' => ['label' => 'Updated', 'width' => [200, 200]],
            'actions' => ['label' => 'Actions', 'width' => [150, 150]],
        ];
    }

    protected function getAllData(): array
    {
        return [];
    }

    protected function countTotal(): int
    {
        return $this->httpGet('users.count')['data']['count'] ?? 0;
    }

    public function getPageData(): array
    {
        $paginationData = $this->tableBuilder->getPaginationData();
        $currentPage = $paginationData['current_page'];
        $perPage = $paginationData['per_page'];

        $data = $this->httpGet('users.index', [
            'per_page' => $perPage,
            'page' => $currentPage,
        ]);
        return $data['data']['users'] ?? [];
    }

    public function getFormattedPageData(int $currentPage, int $perPage): array
    {
        $users = $this->getPageData();
        $formatted = [];

        foreach ($users as $index => $user) {

            $formatted[] = [
                // 'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'email_verified' => $user['email_verified'] ? '✅' : '⚠️',
                'roles' => $user['roles'],
                'updated_at' => $user['updated_at'],
                'actions' => [
                    'button' => [
                        'label' => "✏️",
                        'action' => 'edit_user',
                        'parameters' => [
                            'user_id' => $user['id'],
                        ]
                    ]
                ],
            ];
        }

        return $formatted;
    }

    private function httpGet(string $route, array $queryParams = []): array
    {
        $url = route($route);
        $auth_token = UIStateManager::getAuthToken();

        $response = Http::withHeaders([
            'Authorization' => "Bearer $auth_token"
        ])->get($url, $queryParams);

        return $response->json();
    }
}
