<?php
namespace App\Services\Screens;

use App\Services\UI\UIBuilder;
use App\Services\UI\AbstractUIService;
use App\Services\UI\Components\UIContainer;
use App\Services\UI\Components\TableBuilder;
use App\Services\UI\DataTable\UsersTableModel;
use App\Services\UI\DataTable\UserApiTableModel;

class AdminDashboardService extends AbstractUIService
{
    protected TableBuilder $users_table;

    protected function buildBaseUI(UIContainer $container, ...$params): void
    {
        $container
            ->title('Admin Dashboard')
            ->maxWidth('800px')
            ->centerHorizontal()
            ->padding('20px')
            ->shadow(2);

        $table = UIBuilder::table('users_table')
            ->title('Users Table')
            ->pagination(10)
            ->dataModel(UserApiTableModel::class)
            ->align('center')
            ->rowMinHeight(40);

        $container->add($table);
    }

      public function onChangePage(array $params): void
    {
        $page = $params['page'] ?? 1;
        $this->users_table->page($page);
    }
}
