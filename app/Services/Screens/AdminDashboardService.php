<?php
namespace App\Services\Screens;

use App\Services\UI\UIBuilder;
use App\Services\UI\Support\UIDebug;
use App\Services\UI\Enums\DialogType;
use App\Services\UI\Enums\LayoutType;
use App\Services\UI\AbstractUIService;
use App\Services\UI\Support\HttpClient;
use App\Services\UI\Components\UIContainer;
use App\Services\UI\Components\InputBuilder;
use App\Services\UI\Components\TableBuilder;
use App\Services\UI\Components\ButtonBuilder;
use App\Services\UI\DataTable\UserApiTableModel;
use App\Services\UI\Modals\ConfirmDialogService;
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
            ->placeholder('Search users...')
            ->width('300px')
            ->autocomplete('off')
            ->onInput('search_users', [])
            ->debounce(500);

        $addBtn = UIBuilder::button('add_user_btn')
            ->label('Add user')
            ->style('primary')
            ->action('add_user_clicked')
            ->icon('plus');

        $toolbar->add($search)->add($addBtn);
        $container->add($toolbar);

        $users_table = UIBuilder::table('users_table')
            ->pagination(10)
            ->dataModel(UserApiTableModel::class)
            ->rowMinHeight(60);

        $container->add($users_table);
    }

    protected function postLoadUI(): void
    {
        $search_users = $this->users_table->getSearchTerm();
        $this->search_users->value($search_users);
    }

    public function onAddUserClicked(array $params): void
    {
        RegisterDialogService::open(
            fakeData: true,
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
            $this->users_table->refresh();
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

    public function onDeleteUser(array $params): void
    {
        $user = HttpClient::get(
            "users.show",
            routeParams: ['user' => $params['user_id']]
        )['data'] ?? null;
        if (!$user) {
            $this->toast('User not found', 'error');
            return;
        }
        ConfirmDialogService::open(
            type: DialogType::WARNING,
            title: "Delete User",
            message: "Are you sure you want to delete user '{$user['name']}'?",
            confirmAction: 'confirm_delete_user',
            confirmParams: ['user_id' => $params['user_id']],
            callerServiceId: $this->getServiceComponentId()
        );
    }

    public function onConfirmDeleteUser(array $params): void
    {
        $userId = $params['user_id'] ?? null;
        if (!$userId) {
            $this->toast('User ID is required for deletion', 'error');
            return;
        }

        $response = HttpClient::delete(
            "users.destroy",
            routeParams: ['user' => $userId]
        );
        $status = $response['status'] ?? 'error';
        $message = $response['message'] ?? 'Failed to delete user';
        $this->toast($message, $status);
        $this->users_table->refresh();
        $this->closeModal();
    }

    public function onChangePage(array $params): void
    {
        $page = $params['page'] ?? 1;
        $this->users_table->page($page);
    }

    public function onSearchUsers(array $params): void
    {
        $search = $params['value'] ?? '';

        // Set search term
        $this->users_table->setSearchTerm($search);

        // Reset to page 1 when searching
        $this->users_table->page(1);
    }
}
