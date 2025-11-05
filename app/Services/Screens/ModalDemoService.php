<?php

namespace App\Services\Screens;

use App\Services\UI\AbstractUIService;
use App\Services\UI\Components\UIContainer;
use App\Services\UI\Enums\LayoutType;
use App\Services\UI\Enums\DialogType;
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
    /**
     * Build the modal demo UI
     */
    protected function buildBaseUI(...$params): UIContainer
    {
        $container = UIBuilder::container('main')
            ->parent('main')
            ->layout(LayoutType::VERTICAL)
            ->title('Modal Component Demo');

        // Instruction label
        $container->add(
            UIBuilder::label('lbl_instruction')
                ->text("🔔 Click the button below to open a confirmation dialog:")
                ->style('info')
        );

        // Result label (initially empty)
        $container->add(
            UIBuilder::label('lbl_result')
                ->text('')
                ->style('default')
        );

        // Button to open confirmation modal
        $container->add(
            UIBuilder::button('btn_open_modal')
                ->label('Open Confirmation Dialog')
                ->style('primary')
                ->action('open_confirmation', [])
        );

        return $container;
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

        // Simply return the modal UI - frontend will detect parent='modal' and open overlay
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

        // Get stored UI to find the result label
        $storedUI = $this->getStoredUI();
        $resultLabelId = null;

        foreach ($storedUI as $id => $component) {
            if ($component['type'] === 'label' && 
                isset($component['name']) && 
                $component['name'] === 'lbl_result') {
                $resultLabelId = $id;
                break;
            }
        }

        $updates = [];
        if ($resultLabelId) {
            $updates[$resultLabelId] = [
                'type' => 'label',
                'text' => "✅ Action confirmed! Type: {$actionType}",
                'style' => 'success',
                '_id' => $resultLabelId,
            ];
        }

        return [
            'action' => 'close_modal',
            'ui_updates' => $updates
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
        // Get stored UI to find the result label
        $storedUI = $this->getStoredUI();
        $resultLabelId = null;

        foreach ($storedUI as $id => $component) {
            if ($component['type'] === 'label' && 
                isset($component['name']) && 
                $component['name'] === 'lbl_result') {
                $resultLabelId = $id;
                break;
            }
        }

        $updates = [];
        if ($resultLabelId) {
            $updates[$resultLabelId] = [
                'type' => 'label',
                'text' => "❌ Action cancelled by user",
                'style' => 'warning',
                '_id' => $resultLabelId,
            ];
        }

        return [
            'action' => 'close_modal',
            'ui_updates' => $updates
        ];
    }
}
