<?php

namespace App\UI\Screens\Auth;

use Idei\Usim\Services\UIBuilder;
use Idei\Usim\Services\Enums\LayoutType;
use Idei\Usim\Services\AbstractUIService;
use Idei\Usim\Services\Support\HttpClient;
use Idei\Usim\Services\Components\UIContainer;
use Idei\Usim\Services\Components\InputBuilder;
use Idei\Usim\Services\Components\LabelBuilder;

class ResetPassword extends AbstractUIService
{
    protected LabelBuilder $lbl_result;
    protected InputBuilder $password;
    protected InputBuilder $password_confirmation;

    public function buildBaseUI(UIContainer $container, ...$params): void
    {
        $token = request()->query('token');
        $email = request()->query('email');

        $container
            ->layout(LayoutType::VERTICAL)
            ->justifyContent('start')
            ->alignItems('center')
            ->padding(40)
            ->paddingTop('80px')
            ->minHeight('100vh');

        // Icono superior
        $container->add(
            UIBuilder::label('key_icon')
                ->text('🔑')
                ->style('h1')
                ->center()
                ->fontSize('80px')
        );

        $container->add(
            UIBuilder::label('lbl_title')
                ->text('Restablecer Contraseña')
                ->style('h2')
                ->center()
                ->color('#10b981') // Green to match theme
        );

        // Subtitle moved inside card
        /*
        $container->add(
            UIBuilder::label('lbl_subtitle')
                ->text('Por favor ingresa tu nueva contraseña segura.')
                ->style('p')
                ->center()
                ->color('#6b7280')
                ->marginTop('10px')
        );
        */

        // Card Container
        $formCard = UIBuilder::container('reset_password_card')
            ->layout(LayoutType::VERTICAL)
            ->shadow(true)
            ->maxWidth('600px')
            ->width('100%')
            ->borderRadius('8px')
            ->marginTop('30px')
            ->padding(30)
            ->gap('20px')
            ->backgroundColor('white')
            ->customStyle('border-left: 5px solid #10b981; overflow: hidden;');

        $formCard->add(
            UIBuilder::label('card_title')
                ->text('Nueva Contraseña')
                ->style('h3')
                ->color('#1f2937')
                ->marginBottom('5px')
        );

        $formCard->add(
            UIBuilder::label('lbl_subtitle_card')
                ->text('Por favor ingresa tu nueva contraseña segura para recuperar el acceso a tu cuenta.')
                ->style('p')
                ->color('#6b7280')
                ->marginBottom('15px')
        );

        // Hidden fields for token and email
        $formCard->add(
             UIBuilder::input('reset_token')->type('hidden')->value($token ?? '')
        );
        $formCard->add(
             UIBuilder::input('reset_email')->type('hidden')->value($email ?? '')
        );

        $formCard->add(
            UIBuilder::input('password')
                ->label('Nueva Contraseña')
                ->type('password')
                ->placeholder('Mínimo 8 caracteres')
                ->required(true)
                ->width('100%')
        );

        $formCard->add(
            UIBuilder::input('password_confirmation')
                ->label('Confirmar Contraseña')
                ->type('password')
                ->placeholder('Repite la contraseña')
                ->required(true)
                ->width('100%')
        );

        $formCard->add(
            UIBuilder::label('lbl_result')
                ->text('')
                ->visible(false)
                ->center()
        );

        $formCard->add(
            UIBuilder::button('btn_reset')
                ->label('Cambiar Contraseña')
                ->style('success')
                ->action('reset_password')
                ->marginTop('10px')
        );

        $container->add($formCard);
    }

    public function onResetPassword(array $params): void
    {
        $token = $params['reset_token'] ?? '';
        $email = $params['reset_email'] ?? '';
        $password = $params['password'] ?? '';
        $passwordConfirmation = $params['password_confirmation'] ?? '';

        if (empty($token) || empty($email)) {
             $this->showError('Enlace inválido o expirado.');
             return;
        }

        if (strlen($password) < 8) {
            $this->showError('La contraseña debe tener al menos 8 caracteres.');
            return;
        }

        if ($password !== $passwordConfirmation) {
            $this->showError('Las contraseñas no coinciden.');
            return;
        }

        try {
            $response = HttpClient::post('api.password.reset', [
                'token' => $token,
                'email' => $email,
                'password' => $password,
                'password_confirmation' => $passwordConfirmation,
            ]);

            $status = $response['status'] ?? 'error';
            $message = $response['message'] ?? 'Error desconocido';

            if ($status === 'success') {
                $this->lbl_result
                    ->text('¡Contraseña actualizada! Redirigiendo...')
                    ->style('text-green-600 font-medium')
                    ->visible(true);

                $this->toast('Contraseña actualizada correctamente', 'success');

                // Redirect to login after short delay (handled by frontend if possible, or immediate)
                $this->redirect('/auth/login');
            } else {
                // Extract validation errors if any
                if (isset($response['errors']) && is_array($response['errors'])) {
                     $firstError = reset($response['errors'])[0] ?? $message;
                     $this->showError($firstError);
                } else {
                    $this->showError($message);
                }
            }

        } catch (\Exception $e) {
            $this->showError('Error de conexión: ' . $e->getMessage());
        }
    }

    private function showError(string $message): void
    {
        if (isset($this->lbl_result)) {
            $this->lbl_result->text($message)->style('text-red-500 text-sm')->visible(true);
        }
        $this->toast($message, 'error');
    }
}
