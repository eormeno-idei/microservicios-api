<?php
namespace App\Services\Screens;

use Idei\Usim\Services\UIBuilder;
use Idei\Usim\Services\Support\UIDebug;
use Illuminate\Support\Facades\Auth;
use Idei\Usim\Services\Enums\AlignItems;
use Idei\Usim\Services\Enums\DialogType;
use Idei\Usim\Services\Enums\LayoutType;
use Idei\Usim\Services\AbstractUIService;
use Idei\Usim\Services\Upload\UploadService;
use Idei\Usim\Services\Support\HttpClient;
use Idei\Usim\Services\Enums\JustifyContent;
use Idei\Usim\Services\Components\UIContainer;
use Idei\Usim\Services\Modals\ConfirmDialogService;
use App\Services\UI\Modals\RegisterDialogService;
use Idei\Usim\Services\Components\MenuDropdownBuilder;

/**
 * Demo Menu Service
 *
 * Builds the main navigation menu for demo screens
 */
class DemoMenuService extends AbstractUIService
{
    protected MenuDropdownBuilder $main_menu;
    protected MenuDropdownBuilder $user_menu;
    protected string $store_token = '';
    protected string $store_password = '';

    protected function buildBaseUI(UIContainer $container, ...$params): void
    {
        $container
            ->parent('menu') // Important to set parent!
            ->shadow(0)
            ->borderRadius(2)
            ->layout(LayoutType::HORIZONTAL)
            ->justifyContent(JustifyContent::SPACE_BETWEEN)
            ->alignItems(AlignItems::CENTER)
            ->padding(0);

        $this->main_menu = $this->buildLeftMenu();
        $this->user_menu = $this->buildUserMenu();

        $container->add($this->main_menu);
        $container->add($this->user_menu);
    }

    protected function postLoadUI(): void
    {
        if (Auth::check()) {
            $user = Auth::user();
            $this->updateUserMenuTrigger($user);
            $this->main_menu->setUserPermissions(['auth']);
            $this->user_menu->setUserPermissions(['auth']);
        } else {
            // Caso 1: Usuario no autenticado - trigger con icono de configuración
            $this->user_menu->trigger("⚙️");
            $this->user_menu->setUserPermissions(['no-auth']);
            $this->main_menu->setUserPermissions(['no-auth']);
        }
    }

    /**
     * Actualizar el trigger del menú de usuario según el estado del perfil
     */
    private function updateUserMenuTrigger($user): void
    {
        if ($user->profile_image) {
            // Caso 3: Usuario con imagen de perfil
            $imageUrl = UploadService::fileUrl("uploads/images/$user->profile_image");
            $this->user_menu->triggerImage(
                imageUrl: $imageUrl,
                alt: $user->name,
                label: null  // Solo imagen, sin texto
            );
        } else {
            // Caso 2: Usuario sin imagen de perfil - icono + nombre
            $this->user_menu->trigger("👤 $user->name");
        }
    }

    private function buildLeftMenu(): MenuDropdownBuilder
    {
        $main_menu = UIBuilder::menuDropdown('main_menu')
            ->trigger()
            ->position('bottom-left')
            ->width(200);

        $main_menu->link('Home', '/', '🏠');
        $this->buildDemosMenu($main_menu);
        $main_menu->link('Admin Dashboard', '/admin/dashboard', '🛠️', permission: 'auth');
        $main_menu->separator();
        $main_menu->item('About', 'show_about_info', [], 'ℹ️');
        return $main_menu;
    }

    private function buildDemosMenu(MenuDropdownBuilder $menu): void
    {
        if (env('APP_DEMO_MODE', true) === false) {
            return;
        }

        $menu->separator();
        $menu->submenu('Demos', function ($submenu) {
            $submenu->link('Demo UI', '/demo/demo-ui', '🎨');
            $submenu->link('Table Demo', '/demo/table-demo', '📊');
            $submenu->link('Modal Demo', '/demo/modal-demo', '🪟');
            $submenu->link('Form Demo', '/demo/form-demo', '📝');
            $submenu->link('Button Demo', '/demo/button-demo', '🔘');
            $submenu->link('Input Demo', '/demo/input-demo', '⌨️');
            $submenu->link('Select Demo', '/demo/select-demo', '📋');
            $submenu->link('Checkbox Demo', '/demo/checkbox-demo', '☑️');
            $submenu->link('Uploader Demo', '/demo/uploader-demo', '📤');
            $submenu->link('Calendar Demo', '/demo/calendar-demo', '📅');
        }, '🎮');
    }

    private function buildUserMenu(): MenuDropdownBuilder
    {
        $user_menu = UIBuilder::menuDropdown('user_menu')
            ->position('bottom-right')
            ->width(180);
        $user_menu->trigger("⚙️");
        $user_menu->link('Login', '/login', '🔑', permission: 'no-auth');
        $user_menu->item('Register', 'show_register_form', [], '📝', permission: 'no-auth');
        $user_menu->link('Profile', '/profile', '👤', permission: 'auth');
        $user_menu->item('Logout', 'confirm_logout', [], '🚪', permission: 'auth');
        return $user_menu;
    }

    public function onLoggedUser(array $params): void
    {
        $user = Auth::user();
        if ($user) {
            $this->updateUserMenuTrigger($user);
        }
        $this->main_menu->setUserPermissions(['auth']);
        $this->user_menu->setUserPermissions(['auth']);
    }

    public function onUpdatedProfile(array $params): void
    {
        $user = $params['user'] ?? null;
        if ($user) {
            $this->updateUserMenuTrigger($user);
        }
    }    /**
         * Handler to confirm logout
         */
    public function onConfirmLogout(array $params): void
    {
        // Delete Sanctum token if user is authenticated
        $user = request()->user();
        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }
        Auth::logout();

        // Clear storage variables
        $this->store_token = '';
        $this->store_password = '';

        // Update menu permissions
        $this->user_menu->trigger("⚙️");
        $this->user_menu->setUserPermissions(['no-auth']);
        $this->main_menu->setUserPermissions(['no-auth']);
        $this->toast('You have been logged out successfully.');
        $this->redirect();
    }

    /**
     * Handler for About info dialog
     */
    public function onShowAboutInfo(array $params): void
    {
        // Get this service ID to receive the callback
        $serviceId = $this->getServiceComponentId();

        ConfirmDialogService::open(
            type: DialogType::INFO,
            title: "Acerca de USIM Framework",
            message: "Sistema de componentes UI v1.0\n
            Desarrollado con Laravel y componentes modulares.\n
            Soporta: Tables, Modals, Forms, Menus y más.",
            callerServiceId: $serviceId
        );
    }

    /**
     * Handler for Register form
     */
    public function onShowRegisterForm(array $params): void
    {
        RegisterDialogService::open(
            submitAction: 'submit_register',
            fakeData: true,
            callerServiceId: $this->getServiceComponentId()
        );
    }

    /**
     * Handler to submit register (receives form data)
     */
    public function onSubmitRegister(array $params): void
    {
        $params['roles'] = ['user'];
        $params['send_verification_email'] = true;
        $response = HttpClient::post('api.register', $params);
        $status = $response['status'];
        $message = $response['message'];

        if ($status === 'success') {
            $this->toast($message, 'success');
            $this->closeModal();
        } else {
            $this->toast($message, 'error');

            // Update modal inputs with validation errors
            $errors = $response['errors'] ?? [];

            if (!empty($errors)) {
                $modalUpdates = [];

                foreach ($errors as $fieldName => $messages) {
                    // Concatenate all error messages for the field
                    $modalUpdates[$fieldName] = [
                        'error' => implode(' ', $messages)
                    ];
                }

                $this->updateModal($modalUpdates);
            }
        }
    }

    /**
     * Handler to close profile dialog
     */
    public function onCloseProfileDialog(array $params): void
    {
        $this->closeModal();
    }

    /**
     * Handler for Logout
     */
    public function onLogoutUser(array $params): void
    {
        $serviceId = $this->getServiceComponentId();

        ConfirmDialogService::open(
            type: DialogType::CONFIRM,
            title: "Cerrar Sesión",
            message: "¿Estás seguro que deseas cerrar sesión?",
            confirmAction: 'confirm_logout',
            cancelAction: 'cancel_logout',
            callerServiceId: $serviceId
        );

        // TODO: Los eventos de los modales no persisten los cambios
    }

    /**
     * Handler to cancel logout
     */
    public function onCancelLogout(array $params): void
    {
        $this->closeModal();
    }
}
