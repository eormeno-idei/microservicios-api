<?php

namespace App\Services\Screens;

use App\Services\UI\UIBuilder;
use App\Services\UI\Enums\LayoutType;
use App\Services\UI\AbstractUIService;
use App\Services\UI\Enums\JustifyContent;
use App\Services\UI\Components\UIContainer;

class LoginService extends AbstractUIService
{
    protected string $store_email = 'admin@email.com';
    protected string $store_password = '2444';

    protected function buildBaseUI(...$params): UIContainer
    {
        // Main container for the modal
        $loginContainer = UIBuilder::container('login_dialog')
            ->parent('main')
            ->padding('30px');

        // Email input
        $loginContainer->add(
            UIBuilder::input('login_email')
                ->label('Email')
                ->placeholder('Enter your email')
                ->value($this->store_email)
                ->required(true)
        );

        // Password input
        $loginContainer->add(
            UIBuilder::input('login_password')
                ->label('Password')
                ->type('password')
                ->placeholder('Enter your password')
                ->value($this->store_password)
                ->required(true)
        );

        $buttonsContainer = UIBuilder::container('login_buttons')
            ->layout(LayoutType::HORIZONTAL)
            ->justifyContent(JustifyContent::SPACE_BETWEEN)
            ->gap('10px')
            ->shadow(false)
            ->padding('20px 0 0 0');

        $buttonsContainer->add(
            UIBuilder::button('btn_cancel_login')
                ->label('Cancel')
                ->style('secondary')
                ->action('submit_login')
        );

        $buttonsContainer->add(
            UIBuilder::button('btn_submit_login')
                ->label('Login')
                ->style('primary')
                ->action('close_login_dialog')
        );

        $loginContainer->add($buttonsContainer);

        return $loginContainer;
    }

    /**
     * Handler to submit login (receives email and password from form)
     */
    public function onSubmitLogin(array $params): void
    {
        $email = $params['login_email'] ?? '';
        $password = $params['login_password'] ?? '';

        $this->store_email = $email;
        $this->store_password = $password;

        // Here you would call the API /api/login with email and password
        // For now, just close the modal
    }
}