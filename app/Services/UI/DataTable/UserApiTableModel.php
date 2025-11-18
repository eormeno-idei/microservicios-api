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
        $paginationData = $this->getPaginationData();
        // UIDebug::info("Pagination Data", $paginationData);
        $data = $this->httpGet('users.index', [
            'per_page' => $paginationData['per_page'],
            'page' => $paginationData['current_page'],
        ]);
        // UIDebug::info("Fetched data from API", $users);
        return $data['data']['users'] ?? [];
        // return User::all()->toArray();
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
        $response = Http::withHeaders([
            'Authorization' => "Bearer $auth_token"
        ])->get($url, $queryParams);
        return $response->json();
    }
}
