<?php

namespace App\Services\Screens;

use App\Services\UI\UIBuilder;
use App\Services\UI\Support\UIDebug;
use App\Services\UI\Enums\LayoutType;
use App\Services\UI\AbstractUIService;
use App\Services\UI\Support\HttpClient;
use App\Services\UI\Components\UIContainer;

class EmailVerifiedService extends AbstractUIService
{
    protected string $verificationStatus = 'loading';
    protected string $errorMessage = '';

    protected function buildBaseUI(UIContainer $container, ...$params): void
    {
        $container
            ->layout(LayoutType::VERTICAL)
            ->shadow(false)
            ->justifyContent('center')
            ->alignItems('center')
            ->padding(40)
            ->minHeight('100vh');

        // Determinar qué mostrar según el estado
        switch ($this->verificationStatus) {
            case 'loading':
                $this->buildLoadingUI($container);
                break;
            case 'success':
                $this->buildSuccessUI($container);
                break;
            case 'already_verified':
                $this->buildAlreadyVerifiedUI($container);
                break;
            case 'error':
                $this->buildErrorUI($container);
                break;
        }
    }

    protected function postLoadUI(): void
    {
        // Solo verificar si aún estamos en estado de carga
        if ($this->verificationStatus !== 'loading') {
            return;
        }

        // Obtener parámetros de la URL (id y hash)
        $id = request()->route('id');
        $hash = request()->route('hash');

        if (!$id || !$hash) {
            $this->errorMessage = 'Enlace de verificación inválido. Faltan parámetros requeridos.';
            $this->verificationStatus = 'error';
            $this->container->clear();
            $this->buildBaseUI($this->container);
            return;
        }

        // Llamar a la API de verificación
        try {
            $response = HttpClient::get(
                'verification.verify',
                routeParams: ['id' => $id, 'hash' => $hash]
            );

            $status = $response['status'] ?? 'error';
            $message = $response['message'] ?? 'Error desconocido';

            if ($status === 'success') {
                if (str_contains($message, 'already verified')) {
                    $this->verificationStatus = 'already_verified';
                } else {
                    $this->verificationStatus = 'success';
                }
            } else {
                $this->errorMessage = $message;
                $this->verificationStatus = 'error';
            }
        } catch (\Exception $e) {
            $this->errorMessage = 'No se pudo verificar el email. El enlace puede haber expirado o ser inválido.';
            $this->verificationStatus = 'error';
        }

        // Reconstruir la UI con el nuevo estado
        $this->container->clear();
        $this->buildBaseUI($this->container);
    }

    /**
     * Build loading UI
     */
    private function buildLoadingUI(UIContainer $container): void
    {
        $container->add(
            UIBuilder::label('loading_message')
                ->text('⏳ Verificando su email...')
                ->style('h2')
                ->center()
                ->color('#666')
        );
    }

    /**
     * Build success UI
     */
    private function buildSuccessUI(UIContainer $container): void
    {
        $container->add(
            UIBuilder::label('success_icon')
                ->text('✅')
                ->style('h1')
                ->center()
                ->fontSize('80px')
        );

        $container->add(
            UIBuilder::label('verified_message')
                ->text('Su email ha sido verificado satisfactoriamente')
                ->style('h2')
                ->center()
                ->color('#4CAF50')
        );

        $container->add(
            UIBuilder::label('verified_subtitle')
                ->text('Ahora puede acceder a todas las funcionalidades de la plataforma')
                ->style('p')
                ->center()
                ->color('#666')
                ->marginTop('10px')
        );

        $container->add(
            UIBuilder::card('success_card')
                ->title('Verificación Completa')
                ->description('Su cuenta ha sido activada correctamente. Ya puede iniciar sesión y disfrutar de todos los servicios disponibles.')
                ->theme('success')
                ->elevation('medium')
                ->maxWidth('600px')
                ->marginTop('30px')
                ->addAction('Ir al Login', 'go_to_login', [], 'success')
                ->addAction('Volver al Inicio', 'go_to_home', [], 'outline')
        );
    }

    /**
     * Build already verified UI
     */
    private function buildAlreadyVerifiedUI(UIContainer $container): void
    {
        $container->add(
            UIBuilder::label('info_icon')
                ->text('ℹ️')
                ->style('h1')
                ->center()
                ->fontSize('80px')
        );

        $container->add(
            UIBuilder::label('already_verified_message')
                ->text('Su email ya ha sido verificado anteriormente')
                ->style('h2')
                ->center()
                ->color('#2196F3')
        );

        $container->add(
            UIBuilder::card('info_card')
                ->title('Email Verificado')
                ->description('Su cuenta ya está activa. Puede iniciar sesión normalmente.')
                ->theme('info')
                ->elevation('medium')
                ->maxWidth('600px')
                ->marginTop('30px')
                ->addAction('Ir al Login', 'go_to_login', [], 'primary')
                ->addAction('Volver al Inicio', 'go_to_home', [], 'outline')
        );
    }

    /**
     * Build error UI
     */
    private function buildErrorUI(UIContainer $container): void
    {
        $container->add(
            UIBuilder::label('error_icon')
                ->text('❌')
                ->style('h1')
                ->center()
                ->fontSize('80px')
        );

        $container->add(
            UIBuilder::label('error_message')
                ->text('Error al verificar el email')
                ->style('h2')
                ->center()
                ->color('#f44336')
        );

        $container->add(
            UIBuilder::label('error_detail')
                ->text($this->errorMessage)
                ->style('p')
                ->center()
                ->color('#666')
                ->marginTop('10px')
        );

        $container->add(
            UIBuilder::card('error_card')
                ->title('Verificación Fallida')
                ->description('El enlace de verificación puede haber expirado o ser inválido. Por favor, solicite un nuevo enlace de verificación.')
                ->theme('danger')
                ->elevation('medium')
                ->maxWidth('600px')
                ->marginTop('30px')
                ->addAction('Solicitar Nuevo Enlace', 'resend_verification', [], 'danger')
                ->addAction('Volver al Inicio', 'go_to_home', [], 'outline')
        );
    }

    /**
     * Handle login button click
     */
    public function onGoToLogin(array $params): void
    {
        $this->redirect('/login');
    }

    /**
     * Handle home button click
     */
    public function onGoToHome(array $params): void
    {
        $this->redirect('/');
    }

    /**
     * Handle resend verification email
     */
    public function onResendVerification(array $params): void
    {
        $this->toast('Por favor, inicie sesión para solicitar un nuevo enlace de verificación', 'info');
        $this->redirect('/login');
    }
}
