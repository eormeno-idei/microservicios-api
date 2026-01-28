<?php

namespace App\Services\Screens;

use Idei\Usim\Events\UsimEvent;
use Idei\Usim\Services\UIBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Idei\Usim\Services\AbstractUIService;
use Idei\Usim\Services\Upload\UploadService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Password;
use Idei\Usim\Services\Components\UIContainer;
use Idei\Usim\Services\Components\InputBuilder;
use Idei\Usim\Services\Components\LabelBuilder;
use Illuminate\Auth\Notifications\VerifyEmail;
use Idei\Usim\Services\Components\UploaderBuilder;

/**
 * Profile Service
 *
 * Permite al usuario autenticado:
 * - Editar nombre y apellido
 * - Actualizar foto de perfil (1:1)
 * - Reenviar email de verificación
 * - Cambiar contraseña
 */
class ProfileService extends AbstractUIService
{
    protected InputBuilder $input_email;
    protected InputBuilder $input_name;
    protected UploaderBuilder $uploader_profile;

    protected function buildBaseUI(UIContainer $container, ...$params): void
    {
        $user = Auth::user();

        $container
            ->title('Mi Perfil')
            ->maxWidth('600px')
            ->centerHorizontal()
            ->shadow(2)
            ->padding('30px');

        // Título
        $container->add(
            UIBuilder::label('lbl_title')
                ->text("👤 Configuración de Perfil")
                ->style('primary')
                ->fontSize(20)
                ->fontWeight('bold')
        );

        // Email (readonly)
        $this->input_email = UIBuilder::input('input_email')
            ->label('Email')
            ->type('email')
            ->value($user->email)
            ->disabled(true)
            ->width('100%');

        $container->add($this->input_email);

        // Nombre
        $this->input_name = UIBuilder::input('input_name')
            ->label('Nombre Completo')
            ->type('text')
            ->placeholder('Tu nombre completo')
            ->value($user->name ?? '')
            ->required(true)
            ->width('100%');

        $container->add($this->input_name);

        // Foto de perfil
        $this->uploader_profile = UIBuilder::uploader('uploader_profile')
            ->allowedTypes(['image/*'])
            ->label('Foto de Perfil')
            ->maxFiles(1)
            ->maxSize(2)
            ->aspect('1:1')
            ->size(1);

        $container->add($this->uploader_profile);

        // Botones de acción
        $container->add(
            UIBuilder::button('btn_save_profile')
                ->label('💾 Guardar Cambios')
                ->action('save_profile')
                ->style('primary')
                ->width('100%')
        );

        $container->add(
            UIBuilder::button('btn_change_password')
                ->label('🔒 Cambiar Contraseña')
                ->action('change_password')
                ->style('secondary')
                ->width('100%')
        );
    }

    protected function postLoadUI(): void
    {
        $user = Auth::user();

        // Actualizar inputs con datos actuales del usuario
        $this->input_email->value($user->email ?? '');
        $this->input_name->value($user->name ?? '');

        if (!$user->email_verified_at) {
            $this->input_email->error('Email no verificado. Por favor verifica tu email.');
        } else {
            $this->input_email->error(null);
        }

        $imageUrl = null;

        // Actualizar uploader con imagen actual (si existe)
        if ($user->profile_image) {
            $imageUrl = UploadService::fileUrl("uploads/images/{$user->profile_image}") . '?t=' . time();
        }

        $this->uploader_profile->existingFile($imageUrl);
    }

    /**
     * Guardar cambios del perfil
     */
    public function onSaveProfile(array $params): void
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            // Obtener datos del formulario
            $name = trim($params['input_name'] ?? '');

            if (empty($name)) {
                $this->input_name->error('El nombre es requerido');
                return;
            }

            // Actualizar nombre
            $user->name = $name;

            // Procesar imagen de perfil si fue subida
            if ($filename = $this->uploader_profile->confirm($params, 'images', $user->profile_image)) {
                $user->profile_image = $filename;
            }

            // Guardar cambios
            $user->save();
            $this->input_name->error(null);

            event(new UsimEvent('updated_profile', [
                'user' => $user
            ]));

            // Mostrar éxito
            $this->toast('Perfil actualizado', 'success');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error saving profile: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            $this->toast('Error al guardar el perfil: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Reenviar email de verificación
     */
    public function onResendVerification(array $params): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->email_verified_at) {
            $this->toast('Tu email ya está verificado', 'info');
            return;
        }

        // Enviar notificación de verificación
        $user->sendEmailVerificationNotification();

        $this->toast('Email de verificación enviado. Revisa tu bandeja de entrada', 'success');
    }

    /**
     * Cambiar contraseña
     */
    public function onChangePassword(array $params): void
    {
        $user = Auth::user();

        // Enviar email de reset de contraseña
        $status = Password::sendResetLink([
            'email' => $user->email
        ]);

        if ($status === Password::RESET_LINK_SENT) {
            $this->toast('Enlace para cambiar contraseña enviado a tu email', 'success');
        } else {
            $this->toast('Error al enviar el enlace. Intenta nuevamente', 'error');
        }
    }
}
