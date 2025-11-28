<?php

namespace App\Services\Screens;

use App\Events\UsimEvent;
use App\Services\UI\UIBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\UI\AbstractUIService;
use App\Services\Upload\UploadService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Password;
use App\Services\UI\Components\UIContainer;
use App\Services\UI\Components\InputBuilder;
use App\Services\UI\Components\LabelBuilder;
use Illuminate\Auth\Notifications\VerifyEmail;
use App\Services\UI\Components\UploaderBuilder;

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
        $container->add(
            UIBuilder::input('input_name')
                ->label('Nombre Completo')
                ->type('text')
                ->placeholder('Tu nombre completo')
                ->value($user->name ?? '')
                ->required(true)
                ->width('100%')
        );

        // Foto de perfil
        $uploaderProfile = UIBuilder::uploader('uploader_profile')
            ->allowedTypes(['image/*'])
            ->label('Foto de Perfil')
            ->maxFiles(1)
            ->maxSize(2)
            ->aspect('1:1')
            ->size(1);

        $container->add($uploaderProfile);

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

        // Actualizar uploader con imagen actual (si existe)
        if ($user->profile_image) {
            $imageUrl = UploadService::fileUrl("uploads/images/{$user->profile_image}") . '?t=' . time();
            $this->uploader_profile->existingFile($imageUrl);
        }
    }

    /**
     * Guardar cambios del perfil
     */
    public function onSaveProfile(array $params): void
    {
        $user = Auth::user();

        // Obtener datos del formulario
        $name = trim($params['input_name'] ?? '');

        // Validar nombre
        if (empty($name)) {
            $this->input_name->error('El nombre es requerido');
            $this->toast('Por favor completa el nombre', 'error');
            return;
        }

        // Actualizar nombre
        $user->name = $name;

        // Procesar imagen de perfil si fue subida
        $tempIdsJson = $params['uploader_profile_temp_ids'] ?? '[]';
        $tempIds = json_decode($tempIdsJson, true) ?: [];

        if (!empty($tempIds)) {
            // Obtener archivo temporal
            $file = DB::table('temporary_uploads')
                ->where('id', $tempIds[0])
                ->first();

            if ($file) {
                try {
                    // Eliminar imagen anterior si existe
                    if ($user->profile_image && Storage::disk('uploads')->exists("uploads/images/{$user->profile_image}")) {
                        Storage::disk('uploads')->delete("uploads/images/{$user->profile_image}");
                    }

                    // Mover de temporal a definitivo (carpeta por tipo)
                    $finalPath = "uploads/images/{$file->stored_filename}";

                    // Obtener contenido del archivo temporal
                    $content = Storage::disk('local')->get($file->path);

                    // Guardar en ubicación final
                    Storage::disk('uploads')->put($finalPath, $content);

                    // Eliminar temporal
                    Storage::disk('local')->delete($file->path);

                    // Actualizar usuario
                    $user->profile_image = $file->stored_filename;

                    // Limpiar temporal
                    DB::table('temporary_uploads')->where('id', $file->id)->delete();

                    // NO hacer nada más - postLoadUI se encargará de mostrar la nueva imagen
                    // cuando se guarde el usuario y se ejecute el toast

                } catch (\Exception $e) {
                    \Log::error('ProfileService: Error moviendo archivo', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    $this->toast('Error al guardar la imagen: ' . $e->getMessage(), 'error');
                    return;
                }
            }
        }

        // Guardar cambios
        $user->save();
        $this->input_name->error(null);

        event(new UsimEvent('updated_profile', [
            'user' => $user
        ]));

        // Mostrar éxito
        $this->toast('Perfil actualizado exitosamente', 'success');
    }

    /**
     * Reenviar email de verificación
     */
    public function onResendVerification(array $params): void
    {
        $user = Auth::user();

        if ($user->email_verified_at) {
            $this->toast('Tu email ya está verificado', 'info');
            return;
        }

        // Enviar notificación de verificación
        $user->notify(new VerifyEmail());

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
