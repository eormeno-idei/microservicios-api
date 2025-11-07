<?php

namespace App\Services\UI\Modals;

use App\Services\UI\UIBuilder;
use App\Services\UI\Enums\LayoutType;
use App\Services\UI\Enums\JustifyContent;

/**
 * Login Dialog Service
 *
 * Provides a modal dialog with login form
 */
class LoginDialogService
{
    /**
     * Build login dialog UI
     *
     * @param string $submitAction Action to call when form is submitted
     * @param string|null $cancelAction Action to call when cancel is clicked
     * @param int|null $callerServiceId Service ID that will receive callbacks
     * @return array UI components for the modal
     */
    public function getUI(
        ?int $callerServiceId = null,
        string $submitAction = 'submit_login',
        ?string $cancelAction = 'close_login_dialog',
        string $defaultEmail = '',
        string $defaultPassword = ''
    ): array {
        // Main container for the modal
        $loginContainer = UIBuilder::container('login_dialog')
            ->parent('modal')
            ->shadow(false)
            ->padding('30px');

        // Email input
        $loginContainer->add(
            UIBuilder::input('login_email')
                ->label('Email')
                ->placeholder('Enter your email')
                ->value($defaultEmail)
                ->required(true)
        );

        // Password input
        $loginContainer->add(
            UIBuilder::input('login_password')
                ->label('Password')
                ->type('password')
                ->placeholder('Enter your password')
                ->value($defaultPassword)
                ->required(true)
        );

        // Buttons container
        $buttonsContainer = UIBuilder::container('login_buttons')
            ->layout(LayoutType::HORIZONTAL)
            ->justifyContent(JustifyContent::SPACE_BETWEEN)
            ->gap('10px')
            ->shadow(false)
            ->padding('20px 0 0 0');

        // Cancel button
        if ($cancelAction) {
            $buttonsContainer->add(
                UIBuilder::button('btn_cancel_login')
                    ->label('Cancel')
                    ->style('secondary')
                    ->action($cancelAction, [
                        '_caller_service_id' => $callerServiceId
                    ])
            );
        }

        // Submit button
        $buttonsContainer->add(
            UIBuilder::button('btn_submit_login')
                ->label('Login')
                ->style('primary')
                ->action($submitAction, [
                    '_caller_service_id' => $callerServiceId
                ])
        );

        $loginContainer->add($buttonsContainer);

        return $loginContainer->build();
    }
}
