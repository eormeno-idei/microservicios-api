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
use App\Services\UI\Modals\ConfirmDialogService;
use App\Services\UI\Modals\RegisterDialogService;
use App\Services\UI\Components\MenuDropdownBuilder;

/**
 * Demo Menu Service
 *
 * Builds the main navigation menu for demo screens
 */
class DemoMenuService extends AbstractUIService
{
    protected MenuDropdownBuilder $user_menu;

    protected function buildBaseUI(UIContainer $container, ...$params): void
    {
        $container
            ->shadow(0)
            ->borderRadius(0)
            ->layout(LayoutType::HORIZONTAL)
            ->justifyContent(JustifyContent::SPACE_BETWEEN)
            ->alignItems(AlignItems::CENTER)
            ->padding(0);

        $container->add(
            $this->buildLeftMenu()
        )->add(
            $this->buildUserMenu()
        );
    }

    private function buildLeftMenu(): UIElement
    {
        $menu = UIBuilder::menuDropdown('main_menu')
            ->trigger()
            ->position('bottom-left')
            ->width(200);

        $menu->link('Home', '/', '🏠');
        $this->buildDemosMenu($menu);
        $menu->link('Admin Dashboard', '/admin/dashboard', '🛠️');
        $menu->separator();
        $menu->item('About', 'show_about_info', [], 'ℹ️');

        return $menu;
    }

    private function buildDemosMenu(MenuDropdownBuilder $menu): void
    {
        if (env('APP_DEMO_MODE', true) === false) {
            return;
        }

        $menu->separator();
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
    }

    private function buildUserMenu(): UIElement
    {
        $this->user_menu = UIBuilder::menuDropdown('user_menu')
            ->position('bottom-right')
            ->width(180);

        if (! Auth::check()) {
            $this->user_menu->trigger("⚙️");
            $this->user_menu->link('Login', '/login', '🔑');
            $this->user_menu->item('Register', 'show_register_form', [], '📝');
        } else {
            $userName = Auth::user()->name ?? 'User';
            $this->user_menu->trigger("👤 " . $userName);
            $this->user_menu->item('Profile', 'show_profile', [], '👤');
            $this->user_menu->item('Logout', 'confirm_logout', [], '🚪');
        }

        return $this->user_menu;
    }

    public function onLoggedUser(array $params): void
    {
        $userName = $params['user']['name'] ?? 'User';
        // $this->user_menu->trigger("👤  " . $userName);
        // $this->user_menu->item('Profile', 'show_profile', [], '👤');
        // $this->user_menu->item('Logout', 'confirm_logout', [], '🚪');
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
            confirmAction: 'close_about_dialog',
            callerServiceId: $serviceId
        );
    }

    /**
     * Handler to close about dialog
     */
    public function onCloseAboutDialog(array $params): void
    {
        $this->closeModal();
    }

    /**
     * Handler for Register form
     */
    public function onShowRegisterForm(array $params): array
    {
        $serviceId = $this->getServiceComponentId();

        $registerService = app(RegisterDialogService::class);
        $modalUI         = $registerService->getUI(
            submitAction: 'submit_register',
            cancelAction: 'close_register_dialog',
            callerServiceId: $serviceId
        );

        return $modalUI;
    }

    /**
     * Handler to close register dialog
     */
    public function onCloseRegisterDialog(array $params): void
    {
        $this->closeModal();
    }

    /**
     * Handler to submit register (receives form data)
     */
    public function onSubmitRegister(array $params): void
    {
        // TODO: Validate and create user
        // For now, just show a success message
        $name                 = $params['register_name'] ?? '';
        $email                = $params['register_email'] ?? '';
        $password             = $params['register_password'] ?? '';
        $passwordConfirmation = $params['register_password_confirmation'] ?? '';

        // Here you would call the API /api/register with the data
        // For now, just close the modal
        $this->closeModal();
    }

    /**
     * Handler for Profile view
     */
    public function onShowProfile(array $params): void
    {
        $serviceId = $this->getServiceComponentId();

        ConfirmDialogService::open(
            type: DialogType::INFO,
            title: "User Profile",
            message: "Aquí se mostrará el perfil del usuario.\n(Por implementar)",
            confirmAction: 'close_profile_dialog',
            callerServiceId: $serviceId
        );
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
     * Handler to confirm logout
     */
    public function onConfirmLogout(array $params): void
    {
        // TODO: Clear token from localStorage
        Auth::logout();
        // $this->user_menu->trigger("⚙️");
        // $this->user_menu->link('Login', '/login', '🔑');
        // $this->user_menu->item('Register', 'show_register_form', [], '📝');

        // $this->closeModal();
    }

    /**
     * Handler to cancel logout
     */
    public function onCancelLogout(array $params): void
    {
        $this->closeModal();
    }
}
