<?php

namespace App\Services\Screens;

use Idei\Usim\Services\UIBuilder;
use Idei\Usim\Services\Enums\LayoutType;
use Idei\Usim\Services\AbstractUIService;
use Idei\Usim\Services\Components\UIContainer;
use Idei\Usim\Services\Components\LabelBuilder;
use Idei\Usim\Services\Components\InputBuilder;
use Idei\Usim\Services\Support\HttpClient;

class ResetPasswordService extends AbstractUIService
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
            ->justifyContent('center')
            ->alignItems('center')
            ->padding(40)
            ->minHeight('100vh');
            
        $formCard = UIBuilder::container('reset_password_card')
            ->layout(LayoutType::VERTICAL)
            ->shadow(true)
            ->padding(30)
            ->width('400px')
            ->gap('15px')
            ->borderRadius('8px');

        $formCard->add(
            UIBuilder::label('lbl_title')
                ->text('Restablecer Contraseña')
                ->style('text-2xl font-bold text-gray-800 text-center mb-2')
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
        );

        $formCard->add(
            UIBuilder::input('password_confirmation')
                ->label('Confirmar Contraseña')
                ->type('password')
                ->placeholder('Repite la contraseña')
                ->required(true)
        );

        $formCard->add(
            UIBuilder::label('lbl_result')
                ->text('')
                ->visible(false)
        );

        $formCard->add(
            UIBuilder::button('btn_reset')
                ->label('Cambiar Contraseña')
                ->style('primary w-full') // w-full if supported by CSS class mapping
                ->action('reset_password')
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
                $this->redirect('/login');
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
