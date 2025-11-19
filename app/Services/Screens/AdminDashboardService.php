<?php
namespace App\Services\Screens;

use App\Services\UI\UIBuilder;
use App\Services\UI\Support\UIDebug;
use App\Services\UI\Enums\LayoutType;
use App\Services\UI\AbstractUIService;
use App\Services\UI\Components\UIContainer;
use App\Services\UI\Components\InputBuilder;
use App\Services\UI\Components\TableBuilder;
use App\Services\UI\Components\ButtonBuilder;
use App\Services\UI\DataTable\UserApiTableModel;
use App\Services\UI\Modals\RegisterDialogService;

class AdminDashboardService extends AbstractUIService
{
    protected TableBuilder $users_table;
    protected InputBuilder $search_users;
    protected ButtonBuilder $add_user_btn;

    protected function buildBaseUI(UIContainer $container, ...$params): void
    {
        $container
            ->title('Admin Dashboard')
            ->maxWidth('1200px')
            ->centerHorizontal()
            ->padding('20px')
            ->shadow(2);

        $toolbar = UIBuilder::container('users_toolbar')
            ->layout(LayoutType::HORIZONTAL)
            ->fullWidth()
            ->shadow(0)
            ->gap("12px");

        $search = UIBuilder::input('search_users')
            ->placeholder('Buscar usuario...')
            ->width('300px');

        $addBtn = UIBuilder::button('add_user_btn')
            ->label('Add user')
            ->style('primary')
            ->action('add_user_clicked')
            ->icon('plus');

        $toolbar->add($search)->add($addBtn);
        $container->add($toolbar);

        $table = UIBuilder::table('users_table')
            ->pagination(10)
            ->dataModel(UserApiTableModel::class)
            ->rowMinHeight(40);

        $container->add($table);
    }

    public function onAddUserClicked(array $params): void
    {
        RegisterDialogService::open(
            callerServiceId: $this->getServiceComponentId()
        );
    }

    public function onSubmitRegister(array $params): void
    {
        UIDebug::info('New user registered', $params);
        $this->closeModal();
    }

    public function onCloseModal(array $params): void
    {
        $this->closeModal();
    }

    public function onChangePage(array $params): void
    {
        $page = $params['page'] ?? 1;
        $this->users_table->page($page);
    }
}
