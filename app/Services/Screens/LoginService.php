<?php
namespace App\Services\Screens;

use App\Models\User;
use Idei\Usim\Events\UsimEvent;
use Idei\Usim\Services\UIBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Idei\Usim\Services\Enums\LayoutType;
use Idei\Usim\Services\AbstractUIService;
use Idei\Usim\Services\Enums\JustifyContent;
use Idei\Usim\Services\Components\UIContainer;
use Idei\Usim\Services\Components\LabelBuilder;
use Idei\Usim\Services\Support\UIStateManager;

class LoginService extends AbstractUIService
{
    protected string $store_email = 'admin@email.com';
    protected string $store_password = '2444';
    protected string $store_token = '';
    protected LabelBuilder $lbl_login_result;

    protected function buildBaseUI(UIContainer $container, ...$params): void
    {
        $container
            ->title('User Login')
            ->maxWidth('450px')
            ->centerHorizontal()
            ->shadow(3)
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

    public function onSubmitLogin(array $params): void
    {
        $email = $params['login_email'] ?? '';
        $password = $params['login_password'] ?? '';
        $remember = $params['remember'] ?? false;

        $response = $this->httpPost('api.login', [
            'email' => $email,
            'password' => $password,
            'remember' => $remember,
        ]);

        $message = $response['message'] ?? 'Login failed.';
        $status = $response['status'] ?? 'error';
        $this->toast($message, $status);
        $this->lbl_login_result->text($message)->style($status);

        if ($response['status'] === 'error') {
            return;
        }

        $this->store_token = $response['data']['token'] ?? '';
        $this->store_email = $email;
        $this->store_password = $password;

        // Store token in UIStateManager for HttpClient
        UIStateManager::setAuthToken($this->store_token);

        $user = User::where('email', $email)->first();
        Auth::login($user);

        // Disparar evento - TODOS los servicios en ui-services.php lo recibirán
        event(new UsimEvent('logged_user', [
            'user' => $user,
            'timestamp' => now(),
        ]));

        $this->redirect();

        // UIDebug::info('Token stored:', ['token' => $this->store_token]);


        // // Here I use the Auth facade for authentication
        // if (Auth::attempt(['email' => $email, 'password' => $password])) {
        //     // Authentication passed
        //     $user = User::where('email', $email)->first();

        //     // Token con duración estándar (24 horas)
        //     $token = $user->createToken('auth_token', ['*'], now()->addDay())->plainTextToken;

        //     $this->store_token    = $token;
        //     $this->store_email    = $email;
        //     $this->store_password = $password;

        //     // Disparar evento - TODOS los servicios en ui-services.php lo recibirán
        //     event(new UsimEvent('logged_user', [
        //         'user'      => $user,
        //         'timestamp' => now(),
        //     ]));
        //     $this->toast("Login successful! Welcome, {$user->name}!", 'success');
        //     $this->redirect();
        // } else {
        //     // Authentication failed
        //     $this->lbl_login_result
        //         ->text('Invalid email or password.')
        //         ->style('error');
        // }
    }

    private function httpPost(string $route, array $data): array
    {
        $url = route($route);
        $response = Http::post($url, $data);
        return $response->json();
    }
}
