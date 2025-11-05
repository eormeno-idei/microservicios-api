<?php

namespace App\Services\UI\Modals;

use App\Services\UI\Components\UIContainer;
use App\Services\UI\Enums\LayoutType;
use App\Services\UI\Enums\DialogType;
use App\Services\UI\Enums\TimeUnit;
use App\Services\UI\UIBuilder;

/**
 * Dialog Service
 * 
 * Helper service to generate different types of modal dialogs.
 * Supports: info, confirm, warning, error, success, choice, and timeout dialogs.
 * Does not inherit from AbstractUIService as it's a utility service.
 */
class ConfirmDialogService
{
    /**
     * Build a dialog UI
     * 
     * @param mixed ...$params Parameters:
     *   - type: DialogType enum (INFO, CONFIRM, WARNING, ERROR, SUCCESS, CHOICE, TIMEOUT)
     *   - title: Modal title
     *   - message: Dialog message
     *   - icon: Icon emoji (optional, uses default from DialogType if not specified)
     *   - confirmAction: Action name for confirm/primary button
     *   - confirmParams: Additional parameters for confirm action
     *   - confirmLabel: Label for confirm button (optional, uses default from DialogType)
     *   - cancelAction: Action name for cancel button (default: 'close_modal')
     *   - cancelLabel: Label for cancel button (optional, uses default from DialogType)
     *   - callerServiceId: ID of the service that opened the modal
     *   - buttons: Array of custom buttons for CHOICE type (each: ['label', 'action', 'params', 'style'])
     *   
     *   TIMEOUT specific parameters:
     *   - timeout: Time value (int, required for TIMEOUT)
     *   - timeUnit: TimeUnit enum (SECONDS, MINUTES, HOURS, DAYS) - default: SECONDS
     *   - showCountdown: bool - Show countdown timer (default: true)
     *   - showCloseButton: bool - Show manual close button (default: true)
     *   - timeoutAction: Action to execute when timeout completes (default: 'close_modal')
     * 
     * @return array UI configuration array
     */
    public function getUI(...$params): array
    {
        // Extract dialog type (default to CONFIRM for backward compatibility)
        $type = $params['type'] ?? DialogType::CONFIRM;
        if (is_string($type)) {
            $type = DialogType::from($type);
        }

        // Extract parameters with defaults from DialogType
        $title = $params['title'] ?? 'Diálogo';
        $message = $params['message'] ?? '¿Está seguro?';
        $icon = $params['icon'] ?? $type->getDefaultIcon();
        $confirmAction = $params['confirmAction'] ?? 'confirm';
        $confirmParams = $params['confirmParams'] ?? [];
        $confirmLabel = $params['confirmLabel'] ?? $type->getDefaultConfirmLabel();
        $cancelAction = $params['cancelAction'] ?? 'close_modal';
        $cancelLabel = $params['cancelLabel'] ?? $type->getDefaultCancelLabel();
        $callerServiceId = $params['callerServiceId'] ?? null;
        $customButtons = $params['buttons'] ?? null;

        // TIMEOUT specific parameters
        $timeout = $params['timeout'] ?? null;
        $timeUnit = $params['timeUnit'] ?? TimeUnit::SECONDS;
        if (is_string($timeUnit)) {
            $timeUnit = TimeUnit::from($timeUnit);
        }
        $showCountdown = $params['showCountdown'] ?? true;
        $showCloseButton = $params['showCloseButton'] ?? true;
        $timeoutAction = $params['timeoutAction'] ?? 'close_modal';

        // Build container - use 'modal' as parent to indicate it should be rendered in the modal overlay
        $container = UIBuilder::container('confirm_dialog')
            ->parent('modal')
            ->layout(LayoutType::VERTICAL)
            ->shadow(0) // No shadow since modal already has shadow
            ->rounded(4) // Subtle 4px border radius
            ->padding(0) // No padding
            ->gap(8) // Space between elements
            ->centerContent(); // Center content horizontally

        // Icon
        $container->add(
            UIBuilder::label('icon')
                ->text($icon)
                ->fontSize(48) // Large emoji (48px)
        );

        // Title
        $container->add(
            UIBuilder::label('title')
                ->text($title)
                ->style('h3')
        );

        // Message
        $container->add(
            UIBuilder::label('message')
                ->text($message)
        );

        // Countdown label (only for TIMEOUT type with showCountdown enabled)
        if ($type === DialogType::TIMEOUT && $showCountdown && $timeout !== null) {
            $container->add(
                UIBuilder::label('countdown')
                    ->text($this->formatCountdown($timeout, $timeUnit))
                    ->style('h2')
            );
        }

        // Buttons container (horizontal layout)
        $buttonsContainer = UIBuilder::container('buttons')
            ->layout(LayoutType::HORIZONTAL)
            ->shadow(0) // No shadow on buttons container
            ->rounded(0) // No border radius on buttons container
            ->padding(0) // No padding
            ->gap(8) // Space between buttons
            ->centerContent(); // Center buttons horizontally

        // Build buttons based on dialog type or custom buttons
        if ($customButtons && $type === DialogType::CHOICE) {
            // Custom buttons for CHOICE type
            foreach ($customButtons as $button) {
                $buttonsContainer->add(
                    UIBuilder::button('btn_' . strtolower(str_replace(' ', '_', $button['label'])))
                        ->label($button['label'])
                        ->style($button['style'] ?? 'secondary')
                        ->action($button['action'], array_merge($button['params'] ?? [], [
                            '_caller_service_id' => $callerServiceId
                        ]))
                );
            }
        } elseif ($type === DialogType::TIMEOUT && !$showCloseButton) {
            // TIMEOUT type without close button - no buttons at all
            // Don't add any buttons, just the countdown
        } else {
            // Standard buttons based on DialogType
            
            // Cancel button (if type requires it)
            if ($type->hasCancelButton()) {
                $buttonsContainer->add(
                    UIBuilder::button('btn_cancel')
                        ->label($cancelLabel)
                        ->style('secondary')
                        ->action($cancelAction, [
                            '_caller_service_id' => $callerServiceId
                        ])
                );
            }

            // Confirm/Primary button
            $buttonsContainer->add(
                UIBuilder::button('btn_confirm')
                    ->label($confirmLabel)
                    ->style($type->getConfirmButtonStyle())
                    ->action($confirmAction, array_merge($confirmParams, [
                        '_caller_service_id' => $callerServiceId
                    ]))
            );
        }

        // Only add buttons container if it has buttons (not empty for TIMEOUT without close button)
        if (!($type === DialogType::TIMEOUT && !$showCloseButton)) {
            $container->add($buttonsContainer);
        }

        // Add timeout metadata if TIMEOUT type
        if ($type === DialogType::TIMEOUT && $timeout !== null) {
            $builtContainer = $container->build();
            
            // Get the container ID from the built structure
            $containerId = array_key_first($builtContainer);
            
            // Add timeout configuration to the container
            $builtContainer[$containerId]['_timeout'] = $timeout;
            $builtContainer[$containerId]['_time_unit'] = $timeUnit->value;
            $builtContainer[$containerId]['_time_unit_label'] = $timeUnit->getPluralLabel();
            $builtContainer[$containerId]['_show_countdown'] = $showCountdown;
            $builtContainer[$containerId]['_timeout_action'] = $timeoutAction;
            $builtContainer[$containerId]['_timeout_ms'] = $timeUnit->toMilliseconds($timeout);
            $builtContainer[$containerId]['_caller_service_id'] = $callerServiceId;
            
            return $builtContainer;
        }

        return $container->build();
    }

    /**
     * Format countdown text
     */
    private function formatCountdown(int $value, TimeUnit $unit): string
    {
        return "{$value} {$unit->getLabel($value)}";
    }

    /**
     * Get emoji character for the specified icon type (legacy support)
     * 
     * @deprecated Use DialogType->getDefaultIcon() instead
     */
    private function getIconEmoji(string $icon): string
    {
        return match($icon) {
            'question' => '❓',
            'info' => 'ℹ️',
            'warning' => '⚠️',
            'error' => '❌',
            'success' => '✅',
            default => '❓'
        };
    }
}
