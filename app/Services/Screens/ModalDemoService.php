<?php
namespace App\Services\Screens;

use App\Services\UI\AbstractUIService;
use App\Services\UI\Components\LabelBuilder;
use App\Services\UI\Components\UIContainer;
use App\Services\UI\Enums\DialogType;
use App\Services\UI\Enums\LayoutType;
use App\Services\UI\Enums\TimeUnit;
use App\Services\UI\Modals\ConfirmDialogService;
use App\Services\UI\UIBuilder;

/**
 * Modal Demo Service
 *
 * Demonstrates modal functionality:
 * - Opening confirmation dialogs
 * - Handling user responses from modals
 * - Modal lifecycle (open → user action → close)
 */
class ModalDemoService extends AbstractUIService
{
    protected LabelBuilder $lbl_result;
    protected LabelBuilder $lbl_instruction;

    protected function buildBaseUI(UIContainer $container, ...$params): void
    {
        $container
            ->title('Modal Component Demo')
            ->maxWidth('500px')
            ->centerHorizontal()
            ->shadow(2)
            ->padding('30px');

        $container->add(
            UIBuilder::label('lbl_instruction')
                ->text("🔔 Click the button below to open a confirmation dialog:")
                ->style('info')
        );

        $container->add(
            UIBuilder::label('lbl_result')
                ->text('')
                ->style('default')
        );

        $buttonContainer = UIBuilder::container('button_container')
            ->layout(LayoutType::HORIZONTAL)
            ->centerContent()
            ->gap("10px")
            ->shadow(false)
            ->add(
                UIBuilder::button('btn_open_modal')
                    ->label('Open Confirmation Dialog')
                    ->style('primary')
                    ->action('open_confirmation', [])
            )->add(
            UIBuilder::button('btn_error_dialog')
                ->label('Open Error Dialog')
                ->style('danger')
                ->action('show_error_dialog', [])
        )->add(
            UIBuilder::button('btn_timeout_dialog')
                ->label('Open Timeout Dialog (10 seg)')
                ->style('warning')
                ->action('show_timeout_dialog', ['duration' => 10])
        )->add(
            UIBuilder::button('btn_timeout_no_button')
                ->label('Open Timeout Dialog (no button)')
                ->style('warning')
                ->action('show_timeout_no_button', [])
        )->add(
            UIBuilder::button('btn_show_settings')
                ->label('Settings')
                ->style('secondary')
                ->icon('settings')
                ->action('show_settings_confirm', [])
        );
        $container->add($buttonContainer);
    }

    /**
     * Handle "Open Confirmation" button click
     * Opens a confirmation dialog modal
     *
     * @param array $params
     * @return array Response with modal UI
     */
    public function onOpenConfirmation(array $params): void
    {
        // Get this service's ID to receive the callback
        $serviceId = $this->getServiceComponentId();

        ConfirmDialogService::open(
            type: DialogType::CONFIRM,
            title: "Confirm Action",
            message: "Are you sure you want to proceed with this action?",
            confirmAction: 'handle_confirm',
            confirmParams: ['action_type' => 'demo_action'],
            confirmLabel: 'Yes, Proceed',
            cancelAction: 'handle_cancel',
            cancelLabel: 'No, Cancel',
            callerServiceId: $serviceId
        );
    }

    /**
     * Handle user confirmation from modal
     *
     * @param array $params
     * @return array Response to close modal and update UI
     */
    public function onHandleConfirm(array $params): void
    {
        $actionType = $params['action_type'] ?? 'unknown';

        $this->lbl_result
            ->text("✅ Action confirmed! Type: {$actionType}")
            ->style('success');

        $this->closeModal();
    }

    /**
     * Handle user cancellation from modal
     *
     * @param array $params
     * @return array Response to close modal and update UI
     */
    public function onHandleCancel(array $params): void
    {
        $this->lbl_result
            ->text("❌ Action cancelled by user")
            ->style('warning');

        $this->closeModal();
    }

    /**
     * Handler for Error dialog demo
     */
    public function onShowErrorDialog(array $params): void
    {
        // Get this service ID to receive the callback
        $serviceId = $this->getServiceComponentId();

        ConfirmDialogService::open(
            type: DialogType::ERROR,
            title: "Error de conexión",
            message: "No se pudo conectar con el servidor.\n
            Por favor, verifica tu conexión a internet e intenta nuevamente.",
            confirmAction: 'close_error_dialog',
            callerServiceId: $serviceId
        );
    }

    /**
     * Handler to close error dialog
     */
    public function onCloseErrorDialog(array $params): void
    {
        $this->closeModal();
    }

    public function onShowTimeoutDialog(array $params): void
    {
        $serviceId = $this->getServiceComponentId();
        $duration  = $params['duration'] ?? 10;

        ConfirmDialogService::open(
            type: DialogType::TIMEOUT,
            title: "Notificación Temporal",
            message: "Este mensaje se autodestruirá en:",
            timeout: $duration,
            timeUnit: TimeUnit::SECONDS,
            showCountdown: true,
            confirmAction: 'close_timeout_dialog',
            callerServiceId: $serviceId
        );
    }

    public function onShowTimeoutNoButton(array $params): void
    {
        $serviceId = $this->getServiceComponentId();

        ConfirmDialogService::open(
            type: DialogType::TIMEOUT,
            title: "Auto cierre",
            message: "Este diálogo se cerrará automáticamente en:",
            timeout: 5,
            timeUnit: TimeUnit::SECONDS,
            showCountdown: true,
            showCloseButton: false, // No mostrar botón de cerrar
            callerServiceId: $serviceId
        );
    }

    /**
     * Handler to close timeout dialog
     */
    public function onCloseTimeoutDialog(array $params): void
    {
        $this->closeModal();
    }

    public function onShowSettingsConfirm(array $params): void
    {
        // Get this service ID to receive the callback
        $serviceId = $this->getServiceComponentId();

        ConfirmDialogService::open(
            type: DialogType::WARNING,
            title: "Configuración",
            message: "¿Quieres resetear la configuración?\nEsta acción no se puede deshacer.",
            confirmAction: 'reset_settings',
            confirmParams: [],
            cancelAction: 'cancel_settings',
            callerServiceId: $serviceId
        );
    }

    /**
     * Handler for cancel button (closes modal)
     */
    public function onCancelSettings(array $params): void
    {
        $this->closeModal();
    }

    /**
     * Handler for reset button - shows success dialog
     */
    public function onResetSettings(array $params): void
    {
        // Get this service ID to receive the callback
        $serviceId = $this->getServiceComponentId();

        ConfirmDialogService::open(
            type: DialogType::SUCCESS,
            title: "¡Completado!",
            message: "La configuración ha sido reseteada correctamente.",
            confirmAction: 'close_success_dialog',
            callerServiceId: $serviceId
        );
    }

    /**
     * Handler to close success dialog
     */
    public function onCloseSuccessDialog(array $params): void
    {
        $this->closeModal();
    }
}
