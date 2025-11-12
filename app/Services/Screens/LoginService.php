<?php
namespace App\Services\Screens;

use App\Events\UsimEvent;
use App\Models\User;
use App\Services\UI\AbstractUIService;
use App\Services\UI\Components\LabelBuilder;
use App\Services\UI\Components\UIContainer;
use App\Services\UI\Enums\JustifyContent;
use App\Services\UI\Enums\LayoutType;
use App\Services\UI\UIBuilder;
use Illuminate\Support\Facades\Auth;

class LoginService extends AbstractUIService
{
    protected string $store_email    = 'admin@email.com';
    protected string $store_password = '2444';
    protected string $store_token    = '';
    protected LabelBuilder $lbl_login_result;

    protected function buildBaseUI(UIContainer $container, ...$params): void
    {
        $container
            ->title('User Login')
            ->maxWidth('450px')
            ->centerHorizontal()
            ->padding('30px');

        $container->add(
            UIBuilder::input('login_email')
                ->label('Email')
                ->placeholder('Enter your email')
                ->value($this->store_email)
                ->type('email')
                ->required(true)
                ->width('100%')
        );

        $container->add(
            UIBuilder::input('login_password')
                ->label('Password')
                ->type('password')
                ->placeholder('Enter your password')
                ->value($this->store_password)
                ->required(true)
                ->width('100%')
        );

        $container->add(
            UIBuilder::label('lbl_login_result')->text('')
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
                ->action('close_login_dialog')
        );

        $buttonsContainer->add(
            UIBuilder::button('btn_submit_login')
                ->label('Login')
                ->style('primary')
                ->action('submit_login')
        );

        $container->add($buttonsContainer);
    }

    /**
     * Handler to submit login (receives email and password from form)
     */
    public function onSubmitLogin(array $params): void
    {
        $email    = $params['login_email'] ?? '';
        $password = $params['login_password'] ?? '';

        // Here I use the Auth facade for authentication
        if (Auth::attempt(['email' => $email, 'password' => $password])) {
            // Authentication passed
            $user = User::where('email', $email)->first();

            // Token con duración estándar (24 horas)
            $token = $user->createToken('auth_token', ['*'], now()->addDay())->plainTextToken;

            $this->store_token    = $token;
            $this->store_email    = $email;
            $this->store_password = $password;
            $this->lbl_login_result
                ->text("Login successful!\nWelcome, " . $user->name . "!")
                ->style('success');

            // Disparar evento - TODOS los servicios en ui-services.php lo recibirán
            event(new UsimEvent('logged_user', [
                'user'      => $user,
                'timestamp' => now(),
            ]));
        } else {
            // Authentication failed
            $this->lbl_login_result
                ->text('Invalid email or password.')
                ->style('error');
        }
    }

    public function onCloseLoginDialog(array $params): void
    {
        // Logic to handle when the login dialog is closed
    }
}
