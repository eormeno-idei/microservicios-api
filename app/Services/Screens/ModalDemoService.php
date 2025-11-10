<?php

namespace App\Services\Screens;

use App\Services\UI\UIBuilder;
use App\Services\UI\Enums\DialogType;
use App\Services\UI\AbstractUIService;
use App\Services\UI\Components\UIContainer;
use App\Services\UI\Components\LabelBuilder;
use App\Services\UI\Modals\ConfirmDialogService;

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
        $container->title('Modal Component Demo');

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

        $container->add(
            UIBuilder::button('btn_open_modal')
                ->label('Open Confirmation Dialog')
                ->style('primary')
                ->action('open_confirmation', [])
        );
    }

    /**
     * Handle "Open Confirmation" button click
     * Opens a confirmation dialog modal
     * 
     * @param array $params
     * @return array Response with modal UI
     */
    public function onOpenConfirmation(array $params): array
    {
        // Get this service's ID to receive the callback
        $serviceId = $this->getServiceComponentId();

        // Build confirmation dialog using DialogType
        $confirmService = app(ConfirmDialogService::class);
        $modalUI = $confirmService->getUI(
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

        return $modalUI;
    }

    /**
     * Handle user confirmation from modal
     * 
     * @param array $params
     * @return array Response to close modal and update UI
     */
    public function onHandleConfirm(array $params): array
    {
        $actionType = $params['action_type'] ?? 'unknown';

        $this->lbl_result
            ->text("✅ Action confirmed! Type: {$actionType}")
            ->style('success');

        return [
            'action' => 'close_modal',
        ];
    }

    /**
     * Handle user cancellation from modal
     * 
     * @param array $params
     * @return array Response to close modal and update UI
     */
    public function onHandleCancel(array $params): array
    {
        $this->lbl_result
            ->text("❌ Action cancelled by user")
            ->style('warning');

        return [
            'action' => 'close_modal',
        ];
    }
}
