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
            'name' => ['label' => 'Name', 'width' => [300, 500]],
            'email' => ['label' => 'Email', 'width' => [250, 350]],
            'actions' => ['label' => 'Actions', 'width' => [100, 150]],
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
            // rowIndex is the visual row index in the table (0-based within current page)
            // $rowIndex = $index;

            $formatted[] = [
                // 'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
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

        UIDebug::debug("Making HTTP GET request to $url with token '$auth_token'", $queryParams);

        $response = Http::withHeaders([
            'Authorization' => "Bearer $auth_token"
        ])->get($url, $queryParams);

        UIDebug::debug("Received response from $url", $response);

        return $response->json();
    }
}
