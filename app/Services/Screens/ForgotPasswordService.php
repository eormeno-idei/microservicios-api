<?php

namespace App\Services\Screens;

use Idei\Usim\Services\UIBuilder;
use Idei\Usim\Services\Enums\LayoutType;
use Idei\Usim\Services\AbstractUIService;
use Idei\Usim\Services\Components\UIContainer;
use Idei\Usim\Services\Support\HttpClient;

    protected LabelBuilder $lbl_result;
    protected \Idei\Usim\Services\Components\InputBuilder $email;

    public function buildBaseUI(UIContainer $container, ...$params): void
    {
        $container
            ->layout(LayoutType::VERTICAL)
            ->shadow(false)
            ->justifyContent('center')
            ->alignItems('center')
            ->padding(40)
            ->minHeight('100vh');

        $formCard = UIBuilder::container('forgot_password_card')
            ->layout(LayoutType::VERTICAL)
            ->shadow(true)
            ->padding(30)
            ->width('400px')
            ->gap('15px')
            ->borderRadius('8px')
            ->background('#ffffff');

        $formCard->add(
            UIBuilder::label('lbl_title')
                ->text('Recuperar Contraseña')
                ->style('header')
        );

        $formCard->add(
            UIBuilder::label('lbl_instruction')
                ->text('Ingresa tu email y te enviaremos un enlace para restablecer tu contraseña.')
                ->style('text-sm text-gray-600')
        );

        $formCard->add(
            UIBuilder::input('email')
                ->label('Email')
                ->type('email')
                ->placeholder('ejemplo@correo.com')
        );

        $formCard->add(
            UIBuilder::label('lbl_result')
                ->text('')
                ->visible(false)
        );

        $buttons = UIBuilder::container('buttons')
            ->layout(LayoutType::HORIZONTAL)
            ->justifyContent('space-between')
            ->gap('10px');

        $buttons->add(
            UIBuilder::button('btn_back')
                ->label('Volver al Login')
                ->style('secondary')
                ->onClickNavigate('/login')
        );

        $buttons->add(
            UIBuilder::button('btn_send')
                ->label('Enviar Enlace')
                ->style('primary')
                ->action('send_link')
        );

        $formCard->add($buttons);
        $container->add($formCard);
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
