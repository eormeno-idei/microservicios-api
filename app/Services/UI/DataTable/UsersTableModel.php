<?php

namespace App\Services\UI\DataTable;

use App\Models\User;

/**
 * Users Table Model
 *
 * Implementation for real User model from database
 */
class UsersTableModel extends AbstractDataTableModel
{
    /**
     * Get all users data from database
     *
     * @return array
     */
    protected function getAllData(): array
    {
        return User::all()->toArray();
    }

    /**
     * Get table columns definition
     *
     * @return array
     */
    public function getColumns(): array
    {
        return [
            // 'id' => ['label' => 'ID', 'width' => [60, 80]],
            'name' => ['label' => 'Name', 'width' => [300, 500]],
            'email' => ['label' => 'Email', 'width' => [250, 350]],
            'actions' => ['label' => 'Actions', 'width' => [100, 150]],
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

    /**
     * Dado el id de un usuario, la cantidad de filas por página y la página actual,
     * determina la fila (row index) correspondiente en la página actual, o null si no está en la página.
     */
    private function getRowIndexInPage(int $userId): ?int
    {
        $pageData = $this->tableBuilder->getPaginationData();
        $currentPage = $pageData['current_page'];
        $perPage = $pageData['per_page'];

        $allData = $this->getAllData();
        $globalIndex = null;

        // Find the global index of the user
        foreach ($allData as $index => $user) {
            if ($user['id'] === $userId) {
                $globalIndex = $index;
                break;
            }
        }

        if ($globalIndex === null) {
            return null; // User not found
        }

        // Calculate start and end index for the current page
        $startIndex = ($currentPage - 1) * $perPage;
        $endIndex = $startIndex + $perPage - 1;

        // Check if the global index falls within the current page range
        if ($globalIndex >= $startIndex && $globalIndex <= $endIndex) {
            return $globalIndex - $startIndex; // Return row index within the page
        }

        return null; // User not in the current page
    }

    /**
     * Update user data in database
     *
     * @param int $userId
     * @param array $data
     * @return bool
     */
    public function updateRow(int $userId, array $data): void
    {
        $user = User::find($userId);
        if (!$user) {
            return;
        }

        // Only update allowed fields
        $allowedFields = ['name', 'email'];
        $updateData = array_intersect_key($data, array_flip($allowedFields));

        if (empty($updateData)) {
            return;
        }

        $user->update($updateData);

        $row = $this->getRowIndexInPage($user->id);
        if ($row !== null) {
            $this->tableBuilder->editCell($row, 0, $user->name);
        }

    }

    /**
     * Delete user from database
     *
     * @param int $userId
     * @return bool
     */
    public function deleteUser(int $userId): bool
    {
        $user = User::find($userId);
        if (!$user) {
            return false;
        }

        return $user->delete();
    }
}
