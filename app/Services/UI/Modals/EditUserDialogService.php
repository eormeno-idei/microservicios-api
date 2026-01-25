<?php

namespace App\Services\UI\Modals;

use Idei\Usim\Services\Support\UIDebug;
use Idei\Usim\Services\UIBuilder;
use Idei\Usim\Services\Enums\LayoutType;
use Idei\Usim\Services\Enums\JustifyContent;
use Idei\Usim\Services\UIChangesCollector;

/**
 * Register Dialog Service
 *
 * Provides a modal dialog with registration form
 */
class EditUserDialogService
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
        string $submitAction = 'submit_update_user',
        ?string $cancelAction = 'close_modal',
        array $user = null,
        ?int $callerServiceId = null
    ): array {
        $name = $user ? $user['name'] : '';
        $email = $user ? $user['email'] : '';
        $role = $user ? $user['roles'][0]['name'] ?? 'user' : 'user';
        $emailVerified = $user ? $user['email_verified_at'] !== null : false;

        // Main container for the modal
        $registerContainer = UIBuilder::container('register_dialog')
            ->parent('modal')
            ->shadow(false)
            ->padding('20px');

        // Id input (hidden)
        $registerContainer->add(
            UIBuilder::input('user_id')
                ->type('hidden')
                ->value($user ? $user['id'] : '')
        );

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

        // Checkbox for sending reset password email
        $registerContainer->add(
            UIBuilder::checkbox('send_reset_email')
                ->label('Send password reset email to user')
                ->checked(false)
        );

        // If the email is not verified, checkbox to send verification email
        if (!$emailVerified) {
            $registerContainer->add(
                UIBuilder::checkbox('send_verification_email')
                    ->label('Send email verification to user')
                    ->checked(false)
            );
        }

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
                ->label('Update User')
                ->style('primary')
                ->action($submitAction, [
                    '_caller_service_id' => $callerServiceId
                ])
        );

        $registerContainer->add($buttonsContainer);

        return $registerContainer->toJson();
    }
}
