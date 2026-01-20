<?php

namespace App\Services\Screens;

use App\Models\User;
use Idei\Usim\Services\UIBuilder;
use Idei\Usim\Services\AbstractUIService;
use Idei\Usim\Services\Components\UIContainer;
use Idei\Usim\Services\Components\TableBuilder;
use App\Services\UI\DataTable\UsersTableModel;

/**
 * Table Demo Service
 *
 * Demonstrates table functionality with:
 * - AbstractDataTableModel for data management
 * - Pagination handled by the model
 * - Edit and Remove action buttons
 * - Column width constraints
 *
 * Version: 2.0 (with DataTableModel abstraction)
 */
class TableDemoService extends AbstractUIService
{
    protected TableBuilder $users_table;

    /**
     * Build the table demo UI
     */
    protected function buildBaseUI(UIContainer $container, ...$params): void
    {
        $container->title('Table Component Demo');

        $table = UIBuilder::table('users_table')
            ->title('Users Table')
            ->pagination(10)
            ->dataModel(UsersTableModel::class)
            ->align('center')
            ->rowMinHeight(40);

        $container->add($table);
    }

    public function onEditUser(array $params): void
    {
        $id = $params['user_id'] ?? null;
        $user = User::find($id);
        $updateData = ['name' => "{$user->name} (E)"];
        $this->users_table->getModel()->updateRow($id, $updateData);
    }

    public function onRemoveUser(array $params): void
    {
        $id = $params['user_id'] ?? null;
    }

    public function onChangePage(array $params): void
    {
        $page = $params['page'] ?? 1;
        $this->users_table->page($page);
    }

}
