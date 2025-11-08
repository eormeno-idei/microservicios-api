<?php

namespace App\Services\Screens;

use App\Services\UI\UIBuilder;
use App\Services\UI\Enums\TimeUnit;
use Illuminate\Support\Facades\Auth;
use App\Services\UI\Enums\AlignItems;
use App\Services\UI\Enums\DialogType;
use App\Services\UI\Enums\LayoutType;
use App\Services\UI\AbstractUIService;
use App\Services\UI\Contracts\UIElement;
use App\Services\UI\Enums\JustifyContent;
use App\Services\UI\Components\UIContainer;
use App\Services\UI\Modals\LoginDialogService;
use App\Services\UI\Modals\ConfirmDialogService;
use App\Services\UI\Modals\RegisterDialogService;

/**
 * Demo Menu Service
 *
 * Builds the main navigation menu for demo screens
 */
class DemoMenuService extends AbstractUIService
{
    protected function buildBaseUI(...$params): UIContainer
    {
        // OPCIÓN 1: Items juntos al inicio (por defecto)
        // No se especifica justifyContent, usa flex-start por defecto

        // OPCIÓN 2: Primer item a la izquierda, último a la derecha (JUSTIFICADO)
        // ->justifyContent(JustifyContent::SPACE_BETWEEN)

        // OPCIÓN 3: Ambos items centrados con espacio entre ellos
        // ->justifyContent(JustifyContent::CENTER)
        // ->gap('20px')

        $menu_placeholder = UIBuilder::container('_menu_placeholder')
            ->parent('menu')
            ->shadow(0)
            ->borderRadius(0)
            ->layout(LayoutType::HORIZONTAL)
            ->justifyContent(JustifyContent::SPACE_BETWEEN)  // 👈 Cambiar aquí según necesites
            ->alignItems(AlignItems::CENTER)  // Alinear verticalmente al centro
            ->gap('20px')  // Espacio entre items (opcional)
            ->padding(0);

        $menu_placeholder->add(
            $this->buildMenu()
        )->add(
            $this->buildUserMenu()
        );

        return $menu_placeholder;
    }

    private function buildMenu(): UIElement
    {
        $menu = UIBuilder::menuDropdown('main_menu')
            ->trigger()
            ->position('bottom-left')
            ->width(100);

        // Home link
        $menu->link('Home', '/', '🏠');

        $menu->separator();

        // // Demos submenu
        $menu->submenu('Demos', '🎮', function ($submenu) {
            $submenu->link('Demo UI', '/demo/demo-ui', '🎨');
            $submenu->link('Table Demo', '/demo/table-demo', '📊');
            $submenu->link('Modal Demo', '/demo/modal-demo', '🪟');
            $submenu->link('Form Demo', '/demo/form-demo', '📝');
            $submenu->link('Button Demo', '/demo/button-demo', '🔘');
            $submenu->link('Input Demo', '/demo/input-demo', '⌨️');
            $submenu->link('Select Demo', '/demo/select-demo', '📋');
            $submenu->link('Checkbox Demo', '/demo/checkbox-demo', '☑️');
        });

        $menu->separator();

        // UI Components submenu (future components)
        $menu->submenu('Components', '🧩', function ($submenu) {
            $submenu->item('Test Error Dialog', 'show_error_dialog', [], '❌');
            $submenu->item('Test Timeout (10s)', 'show_timeout_dialog', ['duration' => 10], '⏱️');
            $submenu->item('Test Timeout (5min)', 'show_timeout_minutes', [], '⏱️');
            $submenu->item('Test Timeout (no button)', 'show_timeout_no_button', [], '⏱️');
        });

        $menu->separator();

        // Settings (with WARNING dialog)
        $menu->item('Settings', 'show_settings_confirm', [], '⚙️');

        // About (with INFO dialog)
        $menu->item('About', 'show_about_info', [], 'ℹ️');

        return $menu;
    }

    private function buildUserMenu(): UIElement
    {
        $username = 'Guest';
        $authenticated = false;
        // si el usuario está autenticado, mostrar opciones de perfil y logout
        if (Auth::check()) {
            $user = Auth::user();
            $username = $user->name ?? 'User';
            $authenticated = true;
        }

        $userMenu = UIBuilder::menuDropdown('user_menu')
            ->trigger("👤 $username")
            ->position('bottom-right')  // Alinear al borde derecho para que se despliegue a la izquierda
            ->width(180);  // Ancho fijo para el dropdown

        // Authentication options
        if (!$authenticated) {
            $userMenu->link('Login', '/login', '🔑');
            $userMenu->item('Register', 'show_register_form', [], '📝');
        } else {
            $userMenu->item('Profile', 'show_profile', [], '👤');
            $userMenu->item('Logout', 'logout_user', [], '🚪');
        }

        return $userMenu;
    }

    /**
     * Handler for Settings confirmation dialog
     */
    public function onShowSettingsConfirm(array $params): array
    {
        // Get this service ID to receive the callback
        $serviceId = $this->getServiceComponentId();

        // Build warning dialog using ConfirmDialogService with DialogType
        $confirmService = app(ConfirmDialogService::class);
        $modalUI = $confirmService->getUI(
            type: DialogType::WARNING,
            title: "Configuración",
            message: "¿Quieres resetear la configuración? Esta acción no se puede deshacer.",
            confirmAction: 'reset_settings',
            confirmParams: [],
            cancelAction: 'cancel_settings',
            callerServiceId: $serviceId
        );

        return $modalUI;
    }

    /**
     * Handler for cancel button (closes modal)
     */
    public function onCancelSettings(array $params): array
    {
        return [
            'action' => 'close_modal',
            'modal_id' => 'confirm_dialog'
        ];
    }

    /**
     * Handler for reset button - shows success dialog
     */
    public function onResetSettings(array $params): array
    {
        // Get this service ID to receive the callback
        $serviceId = $this->getServiceComponentId();

        // First close the warning dialog, then show success dialog
        $confirmService = app(ConfirmDialogService::class);
        $modalUI = $confirmService->getUI(
            type: DialogType::SUCCESS,
            title: "¡Completado!",
            message: "La configuración ha sido reseteada correctamente.",
            confirmAction: 'close_success_dialog',
            callerServiceId: $serviceId
        );

        return $modalUI;
    }

    /**
     * Handler to close success dialog
     */
    public function onCloseSuccessDialog(array $params): array
    {
        return [
            'action' => 'close_modal',
            'modal_id' => 'confirm_dialog'
        ];
    }

    /**
     * Handler for About info dialog
     */
    public function onShowAboutInfo(array $params): array
    {
        // Get this service ID to receive the callback
        $serviceId = $this->getServiceComponentId();

        // Build info dialog
        $confirmService = app(ConfirmDialogService::class);
        $modalUI = $confirmService->getUI(
            type: DialogType::INFO,
            title: "Acerca de USIM Framework",
            message: "Sistema de componentes UI v1.0\n
            Desarrollado con Laravel y componentes modulares.\n
            Soporta: Tables, Modals, Forms, Menus y más.",
            confirmAction: 'close_about_dialog',
            callerServiceId: $serviceId
        );

        return $modalUI;
    }

    /**
     * Handler to close about dialog
     */
    public function onCloseAboutDialog(array $params): array
    {
        return [
            'action' => 'close_modal',
            'modal_id' => 'confirm_dialog'
        ];
    }

    /**
     * Handler for Error dialog demo
     */
    public function onShowErrorDialog(array $params): array
    {
        // Get this service ID to receive the callback
        $serviceId = $this->getServiceComponentId();

        // Build error dialog
        $confirmService = app(ConfirmDialogService::class);
        $modalUI = $confirmService->getUI(
            type: DialogType::ERROR,
            title: "Error de conexión",
            message: "No se pudo conectar con el servidor.\n
            Por favor, verifica tu conexión a internet e intenta nuevamente.",
            confirmAction: 'close_error_dialog',
            callerServiceId: $serviceId
        );

        return $modalUI;
    }

    /**
     * Handler to close error dialog
     */
    public function onCloseErrorDialog(array $params): array
    {
        return [
            'action' => 'close_modal',
            'modal_id' => 'confirm_dialog'
        ];
    }

    /**
     * Handler for Timeout dialog (10 seconds)
     */
    public function onShowTimeoutDialog(array $params): array
    {
        $serviceId = $this->getServiceComponentId();
        $duration = $params['duration'] ?? 10;

        // Build timeout dialog
        $confirmService = app(ConfirmDialogService::class);
        $modalUI = $confirmService->getUI(
            type: DialogType::TIMEOUT,
            title: "Notificación Temporal",
            message: "Este mensaje se autodestruirá en:",
            timeout: $duration,
            timeUnit: TimeUnit::SECONDS,
            showCountdown: true,
            confirmAction: 'close_timeout_dialog',
            callerServiceId: $serviceId
        );

        return $modalUI;
    }

    /**
     * Handler for Timeout dialog (5 minutes)
     */
    public function onShowTimeoutMinutes(array $params): array
    {
        $serviceId = $this->getServiceComponentId();

        // Build timeout dialog with minutes
        $confirmService = app(ConfirmDialogService::class);
        $modalUI = $confirmService->getUI(
            type: DialogType::TIMEOUT,
            title: "Sesión Temporal",
            message: "Tu sesión de prueba expirará en:",
            timeout: 5,
            timeUnit: TimeUnit::MINUTES,
            showCountdown: true,
            confirmAction: 'close_timeout_dialog',
            callerServiceId: $serviceId
        );

        return $modalUI;
    }

    /**
     * Handler to close timeout dialog
     */
    public function onCloseTimeoutDialog(array $params): array
    {
        return [
            'action' => 'close_modal',
            'modal_id' => 'confirm_dialog'
        ];
    }

    /**
     * Handler for Timeout dialog without close button (5 seconds)
     */
    public function onShowTimeoutNoButton(array $params): array
    {
        $serviceId = $this->getServiceComponentId();

        // Build timeout dialog without close button
        $confirmService = app(ConfirmDialogService::class);
        $modalUI = $confirmService->getUI(
            type: DialogType::TIMEOUT,
            title: "Auto-cierre",
            message: "Este diálogo se cerrará automáticamente en:",
            timeout: 5,
            timeUnit: TimeUnit::SECONDS,
            showCountdown: true,
            showCloseButton: false, // No mostrar botón de cerrar
            callerServiceId: $serviceId
        );

        return $modalUI;
    }

    /**
     * Handler for Register form
     */
    public function onShowRegisterForm(array $params): array
    {
        $serviceId = $this->getServiceComponentId();

        $registerService = app(RegisterDialogService::class);
        $modalUI = $registerService->getUI(
            submitAction: 'submit_register',
            cancelAction: 'close_register_dialog',
            callerServiceId: $serviceId
        );

        return $modalUI;
    }

    /**
     * Handler to close register dialog
     */
    public function onCloseRegisterDialog(array $params): array
    {
        return [
            'action' => 'close_modal',
            'modal_id' => 'confirm_dialog'
        ];
    }

    /**
     * Handler to submit register (receives form data)
     */
    public function onSubmitRegister(array $params): array
    {
        // TODO: Validate and create user
        // For now, just show a success message
        $name = $params['register_name'] ?? '';
        $email = $params['register_email'] ?? '';
        $password = $params['register_password'] ?? '';
        $passwordConfirmation = $params['register_password_confirmation'] ?? '';

        // Here you would call the API /api/register with the data
        // For now, just close the modal
        return [
            'action' => 'close_modal',
            'modal_id' => 'confirm_dialog'
        ];
    }

    /**
     * Handler for Profile view
     */
    public function onShowProfile(array $params): array
    {
        $serviceId = $this->getServiceComponentId();

        $confirmService = app(ConfirmDialogService::class);
        $modalUI = $confirmService->getUI(
            type: DialogType::INFO,
            title: "User Profile",
            message: "Aquí se mostrará el perfil del usuario.\n(Por implementar)",
            confirmAction: 'close_profile_dialog',
            callerServiceId: $serviceId
        );

        return $modalUI;
    }

    /**
     * Handler to close profile dialog
     */
    public function onCloseProfileDialog(array $params): array
    {
        return [
            'action' => 'close_modal',
            'modal_id' => 'confirm_dialog'
        ];
    }

    /**
     * Handler for Logout
     */
    public function onLogoutUser(array $params): array
    {
        $serviceId = $this->getServiceComponentId();

        $confirmService = app(ConfirmDialogService::class);
        $modalUI = $confirmService->getUI(
            type: DialogType::WARNING,
            title: "Cerrar Sesión",
            message: "¿Estás seguro que deseas cerrar sesión?",
            confirmAction: 'confirm_logout',
            cancelAction: 'cancel_logout',
            callerServiceId: $serviceId
        );

        return $modalUI;
    }

    /**
     * Handler to confirm logout
     */
    public function onConfirmLogout(array $params): array
    {
        // TODO: Clear token from localStorage
        Auth::logout();
        
        $serviceId = $this->getServiceComponentId();

        $confirmService = app(ConfirmDialogService::class);
        $modalUI = $confirmService->getUI(
            type: DialogType::SUCCESS,
            title: "Logout Exitoso",
            message: "Has cerrado sesión correctamente.",
            confirmAction: 'close_logout_dialog',
            callerServiceId: $serviceId
        );

        return $modalUI;
    }

    /**
     * Handler to cancel logout
     */
    public function onCancelLogout(array $params): array
    {
        return [
            'action' => 'close_modal',
            'modal_id' => 'confirm_dialog'
        ];
    }

    /**
     * Handler to close logout success dialog
     */
    public function onCloseLogoutDialog(array $params): array
    {
        return [
            'action' => 'close_modal',
            'modal_id' => 'confirm_dialog'
        ];
    }
}
