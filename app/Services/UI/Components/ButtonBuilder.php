<?php

namespace App\Services\UI\Components;

/**
 * Builder for Button UI components
 * 
 * Modern and powerful button component with comprehensive styling,
 * states, loading indicators, confirmations, and accessibility features.
 */
class ButtonBuilder extends UIComponent
{
    protected function getDefaultConfig(): array
    {
        return [
            // Core functionality
            'label' => '',
            'action' => null,
            'parameters' => [],
            
            // State
            'enabled' => true,
            'loading' => false,
            'active' => false,
            
            // Visual style
            'style' => 'default', // default, primary, secondary, success, warning, danger, info, link, outline
            'variant' => 'solid', // solid, outline, ghost, link
            'size' => 'medium', // small, medium, large, xs, xl
            'shape' => 'default', // default, rounded, pill, square, circle
            'fullWidth' => false,
            
            // Icon
            'icon' => null,
            'icon_position' => 'left', // left, right, top, bottom
            'icon_only' => false,
            
            // Interaction
            'tooltip' => null,
            'confirm_message' => null, // Show confirmation dialog before action
            'keyboard_shortcut' => null, // e.g., "Ctrl+S"
            'autofocus' => false,
            
            // Loading state
            'loading_text' => null, // Text to show when loading
            'loading_icon' => 'spinner',
            
            // Badge/Counter
            'badge' => null, // Notification badge
            'badge_style' => 'danger', // Style for the badge
            
            // Accessibility
            'aria_label' => null,
            'title' => null, // HTML title attribute
            
            // Animation
            'animation' => null, // pulse, bounce, shake, etc.
            'ripple_effect' => true,
        ];
    }

    /**
     * Set the button label text
     * 
     * @param string $label The label text
     * @return $this For method chaining
     */
    public function label(string $label): self
    {
        return $this->setConfig('label', $label);
    }

    /**
     * Set the action to trigger when button is clicked
     * 
     * @param string $action The action name
     * @param array $parameters Optional parameters for the action
     * @return $this For method chaining
     */
    public function action(string $action, array $parameters = []): self
    {
        $this->setConfig('action', $action);
        $this->setConfig('parameters', $parameters);
        return $this;
    }

    /**
     * Set the button icon
     * 
     * @param string $icon The icon name
     * @return $this For method chaining
     */
    public function icon(string $icon): self
    {
        return $this->setConfig('icon', $icon);
    }

    /**
     * Set the button style
     * 
     * @param string $style The style name (default, primary, secondary, success, danger, warning, info, link)
     * @return $this For method chaining
     */
    public function style(string $style): self
    {
        return $this->setConfig('style', $style);
    }

    /**
     * Set whether the button is enabled
     * 
     * @param bool $enabled True if enabled, false if disabled
     * @return $this For method chaining
     */
    public function enabled(bool $enabled = true): self
    {
        return $this->setConfig('enabled', $enabled);
    }
    
    /**
     * Disable the button
     * 
     * @return $this For method chaining
     */
    public function disabled(): self
    {
        return $this->setConfig('enabled', false);
    }

    /**
     * Set the button tooltip text
     * 
     * @param string $tooltip The tooltip text
     * @return $this For method chaining
     */
    public function tooltip(string $tooltip): self
    {
        return $this->setConfig('tooltip', $tooltip);
    }

    /**
     * Set the button size
     * 
     * @param string $size The size (xs, small, medium, large, xl)
     * @return $this For method chaining
     */
    public function size(string $size): self
    {
        return $this->setConfig('size', $size);
    }

    /**
     * Set the button variant
     * 
     * @param string $variant The variant (solid, outline, ghost, link)
     * @return $this For method chaining
     */
    public function variant(string $variant): self
    {
        return $this->setConfig('variant', $variant);
    }

    /**
     * Set the button shape
     * 
     * @param string $shape The shape (default, rounded, pill, square, circle)
     * @return $this For method chaining
     */
    public function shape(string $shape): self
    {
        return $this->setConfig('shape', $shape);
    }

    /**
     * Set the icon position
     * 
     * @param string $position The position (left, right, top, bottom)
     * @return $this For method chaining
     */
    public function iconPosition(string $position): self
    {
        return $this->setConfig('icon_position', $position);
    }

    /**
     * Make this an icon-only button (no label)
     * 
     * @param bool $iconOnly True for icon-only button
     * @return $this For method chaining
     */
    public function iconOnly(bool $iconOnly = true): self
    {
        return $this->setConfig('icon_only', $iconOnly);
    }

    /**
     * Make the button full width
     * 
     * @param bool $fullWidth True for full width
     * @return $this For method chaining
     */
    public function fullWidth(bool $fullWidth = true): self
    {
        return $this->setConfig('fullWidth', $fullWidth);
    }

    /**
     * Set loading state
     * 
     * @param bool $loading True if loading
     * @param string|null $loadingText Optional text to show while loading
     * @return $this For method chaining
     */
    public function loading(bool $loading = true, ?string $loadingText = null): self
    {
        $this->setConfig('loading', $loading);
        if ($loadingText !== null) {
            $this->setConfig('loading_text', $loadingText);
        }
        return $this;
    }

    /**
     * Set active state (for toggle buttons)
     * 
     * @param bool $active True if active
     * @return $this For method chaining
     */
    public function active(bool $active = true): self
    {
        return $this->setConfig('active', $active);
    }

    /**
     * Add a confirmation message before executing action
     * 
     * @param string $message The confirmation message
     * @return $this For method chaining
     */
    public function confirm(string $message): self
    {
        return $this->setConfig('confirm_message', $message);
    }

    /**
     * Set keyboard shortcut
     * 
     * @param string $shortcut The keyboard shortcut (e.g., "Ctrl+S", "Alt+N")
     * @return $this For method chaining
     */
    public function shortcut(string $shortcut): self
    {
        return $this->setConfig('keyboard_shortcut', $shortcut);
    }

    /**
     * Set autofocus on this button
     * 
     * @param bool $autofocus True to autofocus
     * @return $this For method chaining
     */
    public function autofocus(bool $autofocus = true): self
    {
        return $this->setConfig('autofocus', $autofocus);
    }

    /**
     * Add a notification badge to the button
     * 
     * @param string|int $badge The badge content (number or text)
     * @param string $style The badge style (default, primary, success, danger, warning, info)
     * @return $this For method chaining
     */
    public function badge(string|int $badge, string $style = 'danger'): self
    {
        $this->setConfig('badge', $badge);
        $this->setConfig('badge_style', $style);
        return $this;
    }

    /**
     * Set ARIA label for accessibility
     * 
     * @param string $label The ARIA label
     * @return $this For method chaining
     */
    public function ariaLabel(string $label): self
    {
        return $this->setConfig('aria_label', $label);
    }

    /**
     * Set HTML title attribute
     * 
     * @param string $title The title text
     * @return $this For method chaining
     */
    public function title(string $title): self
    {
        return $this->setConfig('title', $title);
    }

    /**
     * Set loading icon
     * 
     * @param string $icon The loading icon name
     * @return $this For method chaining
     */
    public function loadingIcon(string $icon): self
    {
        return $this->setConfig('loading_icon', $icon);
    }

    /**
     * Set animation effect
     * 
     * @param string $animation The animation name (pulse, bounce, shake, etc.)
     * @return $this For method chaining
     */
    public function animation(string $animation): self
    {
        return $this->setConfig('animation', $animation);
    }

    /**
     * Enable/disable ripple effect
     * 
     * @param bool $ripple True to enable ripple effect
     * @return $this For method chaining
     */
    public function ripple(bool $ripple = true): self
    {
        return $this->setConfig('ripple_effect', $ripple);
    }

    /**
     * Legacy build method for backward compatibility
     * Returns array format instead of object
     * 
     * @return array
     * @deprecated Use toJson() instead
     */
    public function build(): array
    {
        return $this->toJson();
    }
}