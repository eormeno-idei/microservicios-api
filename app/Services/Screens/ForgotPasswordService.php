<?php

namespace App\Services\Screens;

use Idei\Usim\Services\UIBuilder;
use Idei\Usim\Services\Enums\LayoutType;
use Idei\Usim\Services\AbstractUIService;
use Idei\Usim\Services\Components\UIContainer;
use Idei\Usim\Services\Components\LabelBuilder;
use Idei\Usim\Services\Support\HttpClient;

class ForgotPasswordService extends AbstractUIService
{
    protected LabelBuilder $lbl_result;
    protected \Idei\Usim\Services\Components\InputBuilder $email;

    public function buildBaseUI(UIContainer $container, ...$params): void
    {
        $container
            ->layout(LayoutType::VERTICAL)
            ->justifyContent('center')
            ->alignItems('center')
            ->padding(40)
            ->minHeight('100vh');

        // Icono superior
        $container->add(
            UIBuilder::label('lock_icon')
                ->text('🔒') // O un ícono similar
                ->style('h1')
                ->center()
                ->fontSize('60px')
        );

        $formCard = UIBuilder::container('forgot_password_card')
            ->layout(LayoutType::VERTICAL)
            ->shadow(true)
            ->padding(30)
            ->width('450px')
            ->gap('20px')
            ->borderRadius('8px');
            // ->style('border-left: 4px solid #3b82f6;'); // UIContainer does not support style()

        $formCard->add(
            UIBuilder::label('lbl_title')
                ->text('Recuperar Contraseña')
                ->style('h2') // Uses h2 style from the verified screen
                ->center()
                ->color('#1f2937') // Dark grey
        );

        $formCard->add(
            UIBuilder::label('lbl_instruction')
                ->text('Ingresa tu email y te enviaremos un enlace para restablecer tu contraseña.')
                ->style('p')
                ->center()
                ->color('#6b7280')
        );

        $formCard->add(
            UIBuilder::input('email')
                ->label('Correo Electrónico')
                ->type('email')
                ->placeholder('nombre@empresa.com')
                ->width('100%')
        );

        $formCard->add(
            UIBuilder::label('lbl_result')
                ->text('')
                ->visible(false)
                ->center()
        );

        $buttons = UIBuilder::container('buttons')
            ->layout(LayoutType::HORIZONTAL)
            ->justifyContent('space-between')
            ->gap('15px')
            ->marginTop('10px');

        $buttons->add(
            UIBuilder::button('btn_back')
                ->label('Volver al Login')
                ->style('outline w-full') // Outline style matches "Volver al Inicio"
                ->action('navigate_to_login')
        );

        $buttons->add(
            UIBuilder::button('btn_send')
                ->label('Enviar Enlace')
                ->style('primary w-full') // Primary style matches "Ir al Login"
                ->action('send_link')
        );

        $formCard->add($buttons);
        $container->add($formCard);
    }

    public function onNavigateToLogin(array $params): void
    {
        $this->redirect('/login');
    }

    public function onSendLink(array $params): void
    {
        $email = $params['email'] ?? '';

        // Manually finding components since automatic injection might not be fully wired up for properties yet
        // or to ensure we have the instance if it wasn't auto-injected.
        // In a perfect USIM, properties matching ID are auto-injected.
        if (!isset($this->lbl_result)) {
             // Fallback or ensure we defined it in buildBaseUI properly with matching ID.
             // Ideally USIM reflects on properties.
             // For now, let's assume the framework injects them if they are protected properties.
        }

        if (empty($email)) {
             if (isset($this->lbl_result)) {
                $this->lbl_result->text('Por favor ingresa un email.')->style('error')->visible(true);
             }
            return;
        }

        try {
            $response = HttpClient::post('api.password.forgot', [
                'email' => $email
            ]);

            $status = $response['status'] ?? 'error';
            $message = $response['message'] ?? 'Error desconocido';

            $this->lbl_result->text($message)->style($status)->visible(true);

            if ($status === 'success') {
                $this->toast('Enlace enviado. Revisa tu correo.', 'success');
                // Opcional: limpiar input
                $this->email->value('');
            } else {
                $this->toast($message, 'error');
            }

        } catch (\Exception $e) {
            $this->lbl_result->text('Error de conexión: ' . $e->getMessage())->style('error')->visible(true);
        }
    }
}
