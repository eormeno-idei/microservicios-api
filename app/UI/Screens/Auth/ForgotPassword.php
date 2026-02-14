<?php

namespace App\UI\Screens\Auth;

use Idei\Usim\Services\UIBuilder;
use Idei\Usim\Services\Enums\LayoutType;
use Idei\Usim\Services\AbstractUIService;
use Idei\Usim\Services\Support\HttpClient;
use Idei\Usim\Services\Components\UIContainer;
use Idei\Usim\Services\Components\LabelBuilder;

class ForgotPassword extends AbstractUIService
{
    protected LabelBuilder $lbl_result;
    protected \Idei\Usim\Services\Components\InputBuilder $email;

    public function buildBaseUI(UIContainer $container, ...$params): void
    {
        $container
            ->layout(LayoutType::VERTICAL)
            ->justifyContent('start')
            ->alignItems('center')
            ->padding(40)
            ->paddingTop('80px')
            ->minHeight('100vh');

        // Icono superior
        $container->add(
            UIBuilder::label('lock_icon')
                ->text('🔒') // O un ícono similar
                ->style('h1')
                ->center()
                ->fontSize('80px')
        );

        $container->add(
            UIBuilder::label('lbl_title')
                ->text('Recuperar Contraseña')
                ->style('h2')
                ->center()
                ->color('#3b82f6') // Blue to match theme
        );

        /*
        // Subtitle removed from outside to be inside the card as description
        $container->add(
            UIBuilder::label('lbl_instruction')
                ->text('Ingresa tu email y te enviaremos un enlace para restablecer tu contraseña.')
                ->style('p')
                ->center()
                ->color('#6b7280')
                ->marginTop('10px')
        );
        */

        // Card Container
        $formCard = UIBuilder::container('forgot_password_card')
            ->layout(LayoutType::VERTICAL)
            ->shadow(true)
            ->maxWidth('600px')
            ->width('100%')
            ->borderRadius('8px')
            ->marginTop('30px')
            ->padding(30)
            ->gap('20px')
            ->backgroundColor('white')
            ->customStyle('border-left: 5px solid #3b82f6; overflow: hidden;');

        $formCard->add(
            UIBuilder::label('card_title')
                ->text('Recuperación de Cuenta')
                ->style('h3')
                ->color('#1f2937')
                ->marginBottom('5px')
        );

        $formCard->add(
            UIBuilder::label('lbl_instruction')
                ->text('Ingresa tu email registrado y te enviaremos un enlace seguro para restablecer tu contraseña y recuperar el acceso.')
                ->style('p')
                ->color('#6b7280')
                ->marginBottom('15px')
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
            ->justifyContent('start')
            ->gap('15px')
            ->marginTop('10px');

        $buttons->add(
            UIBuilder::button('btn_send')
                ->label('Enviar Enlace')
                ->style('primary')
                ->action('send_link')
        );

        $buttons->add(
            UIBuilder::button('btn_back')
                ->label('Volver al Login')
                ->style('outline')
                ->action('navigate_to_login')
        );

        $formCard->add($buttons);

        $container->add($formCard);
    }

    public function onNavigateToLogin(array $params): void
    {
        $this->redirect('/auth/login');
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
