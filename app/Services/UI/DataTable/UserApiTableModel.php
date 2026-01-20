<?php

namespace App\Services\UI\DataTable;

use App\Services\UI\Support\UIDebug;
use Idei\Usim\Services\Support\HttpClient;
use Idei\Usim\Services\Support\UIStateManager;
use Idei\Usim\Services\DataTable\AbstractDataTableModel;

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
            'name' => ['label' => 'Name', 'width' => [400, 400], 'sort_by' => 'name'],
            'email' => ['label' => 'Email', 'width' => [350, 350], 'sort_by' => 'email'],
            'email_verified' => ['label' => 'Verified', 'width' => [100, 100], 'sort_by' => 'email_verified_at'],
            'roles' => ['label' => 'Role', 'width' => [100, 100], 'sort_by' => 'roles'],
            'updated_at' => ['label' => 'Updated', 'width' => [200, 200], 'sort_by' => 'updated_at'],
            'edit' => ['label' => '', 'width' => [25, 25]],
            'delete' => ['label' => '', 'width' => [25, 25]],
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
        $sortBy = $this->tableBuilder->getSortColumn();
        $sortDirection = $this->tableBuilder->getSortDirection();
        $searchTerm = $this->getSearchTerm();
        $query = [];
        if ($searchTerm) {
            $query['search'] = $searchTerm;
        }
        $currentPage = $paginationData['current_page'];
        $perPage = $paginationData['per_page'];
        $query['per_page'] = $perPage;
        $query['page'] = $currentPage;
        if ($sortBy) {
            $query['sort_by'] = $sortBy;
        }
        if ($sortDirection) {
            $query['sort_direction'] = $sortDirection;
        }

        $data = HttpClient::get('users.index', $query);
        return $data['data']['users'] ?? [];
    }

    public function getFormattedPageData(int $currentPage, int $perPage): array
    {
        $users = $this->getPageData();
        $formatted = [];

        foreach ($users as $index => $user) {

            $formatted[] = [
                'name' => $user['name'],
                'email' => $user['email'],
                'email_verified' => $user['email_verified'] ? '✅' : '⚠️',
                'roles' => $user['roles'],
                'updated_at' => $user['updated_at'],
                'edit' => [
                    'button' => [
                        'label' => "✏️",
                        'action' => 'edit_user',
                        'style' => 'secondary',
                        'parameters' => [
                            'user_id' => $user['id'],
                        ]
                    ]
                ],
                'delete' => [
                    'button' => [
                        'label' => "🗑️",
                        'action' => 'delete_user',
                        'style' => 'danger',
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
