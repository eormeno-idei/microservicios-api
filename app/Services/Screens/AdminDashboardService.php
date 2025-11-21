<?php
namespace App\Services\Screens;

use App\Services\UI\UIBuilder;
use App\Services\UI\Enums\LayoutType;
use App\Services\UI\AbstractUIService;
use App\Services\UI\Support\HttpClient;
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
            ->maxWidth('1200px')
            ->centerHorizontal()
            ->padding('10px')
            ->shadow(0);

        $toolbar = UIBuilder::container('users_toolbar')
            ->layout(LayoutType::HORIZONTAL)
            ->fullWidth()
            ->shadow(0)
            ->gap("12px");

        $search = UIBuilder::input('search_users')
            ->placeholder('Buscar usuario...')
            ->width('300px')
            ->autocomplete('off');

        $addBtn = UIBuilder::button('add_user_btn')
            ->label('Add user')
            ->style('primary')
            ->action('add_user_clicked')
            ->icon('plus');

        $toolbar->add($search)->add($addBtn);
        $container->add($toolbar);

        $table = UIBuilder::table('users_table')
            ->pagination(7)
            ->dataModel(UserApiTableModel::class)
            ->rowMinHeight(50);

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
        $params['roles'] = [$params['roles']];
        $response = HttpClient::post('users.store', $params);
        $status = $response['status'] ?? 'success';
        $message = $response['message'] ?? 'User registered successfully';

        if ($status === 'success') {
            $this->toast($message, 'success');
            $this->users_table->updateTableData();
            $this->closeModal();
        } else {
            $this->toast($message, 'error');

            // Update modal inputs with validation errors
            $errors = $response['errors'] ?? [];

            if (!empty($errors)) {
                $modalUpdates = [];

                foreach ($errors as $fieldName => $messages) {
                    // Concatenate all error messages for the field
                    $modalUpdates[$fieldName] = [
                        'error' => implode(' ', $messages)
                    ];
                }

                $this->updateModal($modalUpdates);
            }
        }
    }

    public function onChangePage(array $params): void
    {
        $page = $params['page'] ?? 1;
        $this->users_table->page($page);
    }
}
