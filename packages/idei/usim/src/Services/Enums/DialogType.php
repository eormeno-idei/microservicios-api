<?php

namespace Idei\Usim\Services\Enums;

/**
 * Dialog Type Enum
 * 
 * Defines the available types of dialogs with their characteristics
 */
enum DialogType: string
{
    /**
     * Information dialog - Single "OK" button
     * Used for: Notifications, informational messages
     */
    case INFO = 'info';

    /**
     * Confirmation dialog - "Cancel" and "Confirm" buttons
     * Used for: Confirming actions, destructive operations
     */
    case CONFIRM = 'confirm';

    /**
     * Warning dialog - "Cancel" and "Proceed" buttons
     * Used for: Warnings before potentially dangerous actions
     */
    case WARNING = 'warning';

    /**
     * Error dialog - Single "OK" button
     * Used for: Displaying error messages
     */
    case ERROR = 'error';

    /**
     * Success dialog - Single "OK" button
     * Used for: Confirming successful operations
     */
    case SUCCESS = 'success';

    /**
     * Choice dialog - Custom buttons (2 or more)
     * Used for: Presenting multiple options to user
     */
    case CHOICE = 'choice';

    /**
     * Timeout dialog - Auto-closes after specified time
     * Used for: Temporary notifications, countdown timers
     */
    case TIMEOUT = 'timeout';

    /**
     * Get default icon for this dialog type
     */
    public function getDefaultIcon(): string
    {
        return match($this) {
            self::INFO => 'ℹ️',
            self::CONFIRM => '❓',
            self::WARNING => '⚠️',
            self::ERROR => '❌',
            self::SUCCESS => '✅',
            self::CHOICE => '🤔',
            self::TIMEOUT => '⏱️',
        };
    }

    /**
     * Get default button style for confirm/primary action
     */
    public function getConfirmButtonStyle(): string
    {
        return match($this) {
            self::INFO => 'primary',
            self::CONFIRM => 'danger',
            self::WARNING => 'warning',
            self::ERROR => 'primary',
            self::SUCCESS => 'primary',
            self::CHOICE => 'primary',
            self::TIMEOUT => 'primary',
        };
    }

    /**
     * Check if this dialog type should have a cancel button
     */
    public function hasCancelButton(): bool
    {
        return match($this) {
            self::INFO, self::ERROR, self::SUCCESS, self::TIMEOUT => false,
            self::CONFIRM, self::WARNING, self::CHOICE => true,
        };
    }

    /**
     * Get default confirm button label
     */
    public function getDefaultConfirmLabel(): string
    {
        return match($this) {
            self::INFO => 'OK',
            self::CONFIRM => 'Confirmar',
            self::WARNING => 'Continuar',
            self::ERROR => 'Entendido',
            self::SUCCESS => 'OK',
            self::CHOICE => 'Aceptar',
            self::TIMEOUT => 'Cerrar',
        };
    }

    /**
     * Get default cancel button label
     */
    public function getDefaultCancelLabel(): string
    {
        return 'Cancelar';
    }
}
