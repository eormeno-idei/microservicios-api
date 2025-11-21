<?php

namespace App\Services\UI\Modals;

use App\Services\UI\UIBuilder;
use App\Services\UI\Enums\LayoutType;
use App\Services\UI\Enums\JustifyContent;
use App\Services\UI\UIChangesCollector;
use App\Services\UI\Support\FakeDataHelper;

/**
 * Register Dialog Service
 *
 * Provides a modal dialog with registration form
 */
class RegisterDialogService
{

    public static function open(...$params): void
    {
        $dialog = new self();
        $format = $dialog->getUI(...$params);
        $uiChanges = app(UIChangesCollector::class);
        $uiChanges->add($format);
    }

    /**
     * Build register dialog UI
     *
     * @param string $submitAction Action to call when form is submitted
     * @param string|null $cancelAction Action to call when cancel is clicked
     * @param int|null $callerServiceId Service ID that will receive callbacks
     * @return array UI components for the modal
     */
    public function getUI(
        string $submitAction = 'submit_register',
        ?string $cancelAction = 'close_modal',
        bool $fakeData = false,
        ?int $callerServiceId = null
    ): array {
        $name = '';
        $email = '';
        $password = '';
        $password_confirmation = '';
        $role = 'user';
        if ($fakeData) {
            $userData = FakeDataHelper::userData(['user', 'admin']);
            $name = $userData['name'];
            $email = $userData['email'];
            $password = $userData['password'];
            $password_confirmation = $userData['password_confirmation'];
            $role = $userData['role'];
        }
        // Main container for the modal
        $registerContainer = UIBuilder::container('register_dialog')
            ->parent('modal')
            ->shadow(false)
            ->padding('20px');

        // Name input
        $registerContainer->add(
            UIBuilder::input('name')
                ->label('Full Name')
                ->placeholder('Enter your full name')
                ->required(true)
                ->value($name)
                ->autocomplete('off')
        );

        // Email input
        $registerContainer->add(
            UIBuilder::input('email')
                ->label('Email')
                ->placeholder('Enter your email')
                ->required(true)
                ->value($email)
                ->autocomplete('off')
        );

        // Password input
        $registerContainer->add(
            UIBuilder::input('password')
                ->label('Password')
                ->type('password')
                ->placeholder('Enter your password (min 8 characters)')
                ->required(true)
                ->value($password)
                ->autocomplete('new-password')
        );

        // Password confirmation
        $registerContainer->add(
            UIBuilder::input('password_confirmation')
                ->label('Confirm Password')
                ->type('password')
                ->placeholder('Confirm your password')
                ->required(true)
                ->value($password_confirmation)
                ->autocomplete('new-password')
        );

        // Role select
        $registerContainer->add(
            UIBuilder::select('roles')
                ->label('Role')
                ->options([
                    ['value' => 'user', 'label' => 'User'],
                    ['value' => 'admin', 'label' => 'Admin'],
                ])
                ->value($role)
                ->required(true)
        );

        // Buttons container
        $buttonsContainer = UIBuilder::container('register_buttons')
            ->layout(LayoutType::HORIZONTAL)
            ->justifyContent(JustifyContent::SPACE_BETWEEN)
            ->shadow(false)
            ->gap('10px')
            ->padding('10px 0 0 0');

        // Cancel button
        if ($cancelAction) {
            $buttonsContainer->add(
                UIBuilder::button('btn_cancel_register')
                    ->label('Cancel')
                    ->style('secondary')
                    ->action($cancelAction, [
                        '_caller_service_id' => $callerServiceId
                    ])
            );
        }

        // Submit button
        $buttonsContainer->add(
            UIBuilder::button('btn_submit_register')
                ->label('Register')
                ->style('primary')
                ->action($submitAction, [
                    '_caller_service_id' => $callerServiceId
                ])
        );

        $registerContainer->add($buttonsContainer);

        return $registerContainer->toJson();
    }
}
