<?php

namespace App\Services\UI\DataTable;

use App\Services\UI\Support\HttpClient;
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
            'name' => ['label' => 'Name', 'width' => [400, 400]],
            'email' => ['label' => 'Email', 'width' => [350, 350]],
            'email_verified' => ['label' => 'Verified', 'width' => [100, 100]],
            'role' => ['label' => 'Role', 'width' => [100, 100]],
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
        $searchTerm = $this->getSearchTerm();
        $query = [];
        if ($searchTerm) {
            $query['search'] = $searchTerm;
        }
        return HttpClient::get('users.count', $query)['data']['count'] ?? 0;
    }

    public function setSearchTerm(string|null $searchTerm): void
    {
        UIStateManager::storeKeyValue(
            'user_table_search',
            $searchTerm
        );
    }

    public function getSearchTerm(): ?string
    {
        return UIStateManager::getKeyValue(
            'user_table_search'
        );
    }

    public function clearSearch(): void
    {
        UIStateManager::clearKeyValue(
            'user_table_search'
        );
    }

    public function getPageData(): array
    {
        $paginationData = $this->tableBuilder->getPaginationData();
        $searchTerm = $this->getSearchTerm();
        $query = [];
        if ($searchTerm) {
            $query['search'] = $searchTerm;
        }
        $currentPage = $paginationData['current_page'];
        $perPage = $paginationData['per_page'];
        $query['per_page'] = $perPage;
        $query['page'] = $currentPage;

        $data = HttpClient::get('users.index', $query);
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
}
