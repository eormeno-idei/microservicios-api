<?php

namespace App\Services\UI\DataTable;

use Illuminate\Support\Facades\Log;

/**
 * Users Data Table Model
 * 
 * Implementation for the users demo data from users_data.php
 */
class UsersDataTableModel extends AbstractDataTableModel
{
    private static array $dataCache = [];
    private static bool $cacheInitialized = false;

    public function __construct()
    {
        $this->initializeCache();
    }

    /**
     * Initialize the data cache from file on first load
     */
    private function initializeCache(): void
    {
        if (!self::$cacheInitialized) {
            self::$dataCache = require app_path('Data/users_data.php');
            self::$cacheInitialized = true;
        }
    }

    /**
     * Get all users data from cache
     * 
     * @return array
     */
    protected function getAllData(): array
    {
        return self::$dataCache;
    }

    /**
     * Get table columns definition
     * 
     * @return array
     */
    public function getColumns(): array
    {
        return [
            'id' => ['label' => 'Id', 'width' => [50, 80]],
            'name' => ['label' => 'Name', 'width' => [200, 250]],
            'country' => ['label' => 'País', 'width' => [200, 250]],
            'actions' => ['label' => 'Acciones', 'width' => [80, 120]],
            'remove' => ['label' => '', 'width' => [80, 120]],
        ];
    }

    /**
     * Get formatted data for table display
     * 
     * @return array
     */
    public function getFormattedPageData(int $currentPage, int $perPage): array
    {
        $users = $this->getPageData();
        $formatted = [];

        foreach ($users as $index => $user) {
            // rowIndex is the visual row index in the table (0-based within current page)
            // Not the global index across all pages
            $rowIndex = $index;

            $formatted[] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'country' => $user['country'],
                'actions' => [
                    'button' => [
                        'label' => "Edit #{$user['id']}",
                        'action' => 'edit_user',
                        'style' => 'primary',
                        'parameters' => [
                            'user_id' => $user['id'],
                            'row' => $rowIndex,
                        ]
                    ]
                ],
                'remove' => [
                    'button' => [
                        'label' => "Remove #{$user['id']}",
                        'action' => 'remove_user',
                        'style' => 'danger',
                        'parameters' => [
                            'user_id' => $user['id'],
                            'row' => $rowIndex
                        ]
                    ]
                ]
            ];
        }

        return $formatted;
    }

    /**
     * Find user by ID
     * 
     * @param int $userId
     * @return array|null
     */
    public function findUserById(int $userId): ?array
    {
        $users = $this->getAllData();
        foreach ($users as $user) {
            if ($user['id'] == $userId) {
                return $user;
            }
        }
        return null;
    }

    /**
     * Update user data in cache
     * 
     * @param int $userId
     * @param array $data
     * @return bool
     */
    public function updateUser(int $userId, array $data): bool
    {
        foreach (self::$dataCache as &$user) {
            if ($user['id'] == $userId) {
                // Update only the fields provided
                foreach ($data as $key => $value) {
                    if (isset($user[$key])) {
                        $user[$key] = $value;
                    }
                }
                Log::debug("User #$userId updated. " . json_encode($user));
                return true;
            }
        }
        return false;
    }

    public function updateRow(int $rowId, array $newData): void
    {
        $this->updateUser($rowId, $newData);
    }

    public function updateCell(int $rowIndex, int $columnIndex, $newValue): void
    {
        $pageData = $this->tableBuilder->getPaginationData();
        $globalIndex = ($pageData['current_page'] - 1) * $pageData['per_page'] + $rowIndex;
        $columns = array_keys($this->getColumns());
        if (isset(self::$dataCache[$globalIndex]) && isset($columns[$columnIndex])) {
            $columnKey = $columns[$columnIndex];
            self::$dataCache[$globalIndex][$columnKey] = $newValue;
        }
    }

    /**
     * Remove user from cache (mark as removed)
     * 
     * @param int $userId
     * @return bool
     */
    public function removeUser(int $userId): bool
    {
        foreach (self::$dataCache as $index => $user) {
            if ($user['id'] == $userId) {
                unset(self::$dataCache[$index]);
                // Reindex the array to maintain consistency
                self::$dataCache = array_values(self::$dataCache);
                return true;
            }
        }
        return false;
    }

    /**
     * Get the configuration for "removed" user display
     * 
     * Customizes how removed users appear in the table.
     * 
     * @return array Configuration for removed user display
     */
    public function getRemovedRowConfig(): array
    {
        return [
            'primary_message' => '[USER REMOVED]',  // Custom message for users
            'secondary_message' => '---',           // Custom placeholder
            'id_placeholder' => '❌',               // Visual indicator for ID
            'button_placeholder' => '⛔',           // Visual indicator for buttons
            'empty_placeholder' => '',              // Empty cells
        ];
    }
}