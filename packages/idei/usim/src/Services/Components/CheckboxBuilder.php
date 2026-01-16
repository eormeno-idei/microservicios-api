<?php

namespace Idei\Usim\Services\Components;

use Idei\Usim\Services\Components\UIComponent;

/**
 * Builder class for creating checkbox components
 * 
 * Provides a fluent API for configuring checkbox inputs with extensive customization options.
 * Supports single checkboxes, checkbox groups, validation, styling, and modern UX features.
 * 
 * @example
 * // Simple checkbox
 * UIBuilder::checkbox('terms')
 *     ->label('I agree to the terms and conditions')
 *     ->required();
 * 
 * // Checkbox with options (checkbox group)
 * UIBuilder::checkbox('interests')
 *     ->label('Select your interests')
 *     ->options([
 *         ['value' => 'sports', 'label' => 'Sports'],
 *         ['value' => 'music', 'label' => 'Music'],
 *         ['value' => 'tech', 'label' => 'Technology']
 *     ])
 *     ->value(['sports', 'tech']);
 */
class CheckboxBuilder extends UIComponent
{
    /**
     * Get the default configuration for a checkbox component
     * 
     * @return array
     */
    protected function getDefaultConfig(): array
    {
        return [
            // Core checkbox properties
            'checked' => false,
            'value' => null,
            'label' => null,
            'description' => null,
            
            // Checkbox group (multiple options)
            'options' => [],
            'selected_values' => [],
            
            // Layout for groups
            'layout' => 'vertical', // vertical, horizontal, grid
            'columns' => null, // for grid layout
            'gap' => 'medium', // xs, small, medium, large
            
            // Validation
            'required' => false,
            'min_selections' => null, // for groups
            'max_selections' => null, // for groups
            'error_message' => null,
            'help_text' => null,
            
            // State
            'disabled' => false,
            'readonly' => false,
            'indeterminate' => false, // for parent checkboxes in nested structures
            
            // Appearance
            'style' => 'default', // default, primary, success, danger, warning, info
            'size' => 'medium', // small, medium, large
            'variant' => 'default', // default, switch, button, card
            
            // Icons
            'checked_icon' => 'check',
            'unchecked_icon' => null,
            'indeterminate_icon' => 'minus',
            
            // Switch variant specific
            'switch_position' => 'left', // left, right (for switch variant)
            
            // Button/Card variant specific
            'icon' => null, // icon for button/card variant
            'color' => null, // custom color
            'active_color' => null, // color when checked
            
            // Behavior
            'toggle_all' => false, // for parent checkbox that controls all children
            'auto_check_parent' => false, // auto check parent when all children checked
            
            // Events
            'on_change' => null,
            
            // Accessibility
            'aria_label' => null,
            'tooltip' => null,
        ];
    }

    /**
     * Get the component type
     * 
     * @return string
     */
    public function getType(): string
    {
        return 'checkbox';
    }

    // ==================== Core Configuration ====================

    /**
     * Set the checked state
     * 
     * @param bool $checked Whether the checkbox is checked
     * @return $this
     */
    public function checked(bool $checked = true): self
    {
        $this->config['checked'] = $checked;
        return $this;
    }

    /**
     * Get the checked state
     * 
     * @return bool
     */
    public function isChecked(): bool
    {
        return $this->config['checked'] ?? false;
    }

    /**
     * Set the checkbox value
     * For single checkbox: any value to submit when checked
     * For checkbox group: array of selected values
     * 
     * @param mixed $value The value
     * @return $this
     */
    public function value($value): self
    {
        if (is_array($value)) {
            $this->config['selected_values'] = $value;
        } else {
            $this->config['value'] = $value;
        }
        return $this;
    }

    /**
     * Set the checkbox label
     * 
     * @param string $label The label text
     * @return $this
     */
    public function label(string $label): self
    {
        $this->config['label'] = $label;
        return $this;
    }

    /**
     * Set a description text (appears below the label)
     * 
     * @param string $description The description text
     * @return $this
     */
    public function description(string $description): self
    {
        $this->config['description'] = $description;
        return $this;
    }

    // ==================== Checkbox Group ====================

    /**
     * Set multiple checkbox options (creates a checkbox group)
     * 
     * @param array $options Array of options with 'value' and 'label' keys
     * @return $this
     */
    public function options(array $options): self
    {
        $this->config['options'] = $options;
        return $this;
    }

    /**
     * Add a single option to the checkbox group
     * 
     * @param string $value The option value
     * @param string $label The option label
     * @param array $extra Extra properties (icon, description, disabled, etc.)
     * @return $this
     */
    public function addOption(string $value, string $label, array $extra = []): self
    {
        $this->config['options'][] = array_merge([
            'value' => $value,
            'label' => $label,
        ], $extra);
        return $this;
    }

    /**
     * Set the selected values for checkbox group
     * 
     * @param array $values Array of selected values
     * @return $this
     */
    public function selectedValues(array $values): self
    {
        $this->config['selected_values'] = $values;
        return $this;
    }

    // ==================== Layout (for groups) ====================

    /**
     * Set the layout for checkbox group
     * 
     * @param string $layout Layout type: vertical, horizontal, grid
     * @param int|null $columns Number of columns for grid layout
     * @return $this
     */
    public function layout(string $layout, ?int $columns = null): self
    {
        $this->config['layout'] = $layout;
        if ($columns !== null) {
            $this->config['columns'] = $columns;
        }
        return $this;
    }

    /**
     * Set vertical layout for checkbox group
     * 
     * @return $this
     */
    public function vertical(): self
    {
        return $this->layout('vertical');
    }

    /**
     * Set horizontal layout for checkbox group
     * 
     * @return $this
     */
    public function horizontal(): self
    {
        return $this->layout('horizontal');
    }

    /**
     * Set grid layout for checkbox group
     * 
     * @param int $columns Number of columns
     * @return $this
     */
    public function grid(int $columns = 2): self
    {
        return $this->layout('grid', $columns);
    }

    /**
     * Set the gap between checkboxes in a group
     * 
     * @param string $gap Gap size: xs, small, medium, large
     * @return $this
     */
    public function gap(string $gap): self
    {
        $this->config['gap'] = $gap;
        return $this;
    }

    // ==================== Validation ====================

    /**
     * Mark the checkbox as required
     * 
     * @param bool $required Whether the checkbox is required
     * @return $this
     */
    public function required(bool $required = true): self
    {
        $this->config['required'] = $required;
        return $this;
    }

    /**
     * Set minimum selections required (for checkbox groups)
     * 
     * @param int $min Minimum number of selections
     * @return $this
     */
    public function minSelections(int $min): self
    {
        $this->config['min_selections'] = $min;
        return $this;
    }

    /**
     * Set maximum selections allowed (for checkbox groups)
     * 
     * @param int $max Maximum number of selections
     * @return $this
     */
    public function maxSelections(int $max): self
    {
        $this->config['max_selections'] = $max;
        return $this;
    }

    /**
     * Set the error message
     * 
     * @param string $message The error message
     * @return $this
     */
    public function errorMessage(string $message): self
    {
        $this->config['error_message'] = $message;
        return $this;
    }

    /**
     * Set help text
     * 
     * @param string $text The help text
     * @return $this
     */
    public function helpText(string $text): self
    {
        $this->config['help_text'] = $text;
        return $this;
    }

    // ==================== State ====================

    /**
     * Set the disabled state
     * 
     * @param bool $disabled Whether the checkbox is disabled
     * @return $this
     */
    public function disabled(bool $disabled = true): self
    {
        $this->config['disabled'] = $disabled;
        return $this;
    }

    /**
     * Set the readonly state
     * 
     * @param bool $readonly Whether the checkbox is readonly
     * @return $this
     */
    public function readonly(bool $readonly = true): self
    {
        $this->config['readonly'] = $readonly;
        return $this;
    }

    /**
     * Set the indeterminate state (for parent checkboxes)
     * 
     * @param bool $indeterminate Whether the checkbox is indeterminate
     * @return $this
     */
    public function indeterminate(bool $indeterminate = true): self
    {
        $this->config['indeterminate'] = $indeterminate;
        return $this;
    }

    // ==================== Appearance ====================

    /**
     * Set the checkbox style
     * 
     * @param string $style Style: default, primary, success, danger, warning, info
     * @return $this
     */
    public function style(string $style): self
    {
        $this->config['style'] = $style;
        return $this;
    }

    /**
     * Set the checkbox size
     * 
     * @param string $size Size: small, medium, large
     * @return $this
     */
    public function size(string $size): self
    {
        $this->config['size'] = $size;
        return $this;
    }

    /**
     * Set the checkbox variant
     * 
     * @param string $variant Variant: default, switch, button, card
     * @return $this
     */
    public function variant(string $variant): self
    {
        $this->config['variant'] = $variant;
        return $this;
    }

    /**
     * Use switch variant
     * 
     * @param string $position Switch position: left, right
     * @return $this
     */
    public function asSwitch(string $position = 'left'): self
    {
        $this->config['variant'] = 'switch';
        $this->config['switch_position'] = $position;
        return $this;
    }

    /**
     * Use button variant (checkbox looks like a button)
     * 
     * @return $this
     */
    public function asButton(): self
    {
        $this->config['variant'] = 'button';
        return $this;
    }

    /**
     * Use card variant (checkbox as a card/tile)
     * 
     * @return $this
     */
    public function asCard(): self
    {
        $this->config['variant'] = 'card';
        return $this;
    }

    // ==================== Icons ====================

    /**
     * Set the checked icon
     * 
     * @param string $icon The icon name
     * @return $this
     */
    public function checkedIcon(string $icon): self
    {
        $this->config['checked_icon'] = $icon;
        return $this;
    }

    /**
     * Set the unchecked icon
     * 
     * @param string $icon The icon name
     * @return $this
     */
    public function uncheckedIcon(string $icon): self
    {
        $this->config['unchecked_icon'] = $icon;
        return $this;
    }

    /**
     * Set the indeterminate icon
     * 
     * @param string $icon The icon name
     * @return $this
     */
    public function indeterminateIcon(string $icon): self
    {
        $this->config['indeterminate_icon'] = $icon;
        return $this;
    }

    /**
     * Set an icon for button/card variant
     * 
     * @param string $icon The icon name
     * @return $this
     */
    public function icon(string $icon): self
    {
        $this->config['icon'] = $icon;
        return $this;
    }

    // ==================== Colors ====================

    /**
     * Set custom color
     * 
     * @param string $color The color (hex, rgb, css variable)
     * @return $this
     */
    public function color(string $color): self
    {
        $this->config['color'] = $color;
        return $this;
    }

    /**
     * Set custom active color (when checked)
     * 
     * @param string $color The color (hex, rgb, css variable)
     * @return $this
     */
    public function activeColor(string $color): self
    {
        $this->config['active_color'] = $color;
        return $this;
    }

    // ==================== Behavior ====================

    /**
     * Enable toggle all behavior (checkbox controls all children)
     * 
     * @param bool $toggle Whether to enable toggle all
     * @return $this
     */
    public function toggleAll(bool $toggle = true): self
    {
        $this->config['toggle_all'] = $toggle;
        return $this;
    }

    /**
     * Enable auto check parent behavior
     * 
     * @param bool $autoCheck Whether to auto check parent
     * @return $this
     */
    public function autoCheckParent(bool $autoCheck = true): self
    {
        $this->config['auto_check_parent'] = $autoCheck;
        return $this;
    }

    // ==================== Events ====================

    /**
     * Set the onChange event handler
     * 
     * @param string $handler The event handler name
     * @return $this
     */
    public function onChange(string $handler): self
    {
        $this->config['on_change'] = $handler;
        return $this;
    }

    // ==================== Accessibility ====================

    /**
     * Set ARIA label for accessibility
     * 
     * @param string $label The ARIA label
     * @return $this
     */
    public function ariaLabel(string $label): self
    {
        $this->config['aria_label'] = $label;
        return $this;
    }

    /**
     * Set tooltip text
     * 
     * @param string $text The tooltip text
     * @return $this
     */
    public function tooltip(string $text): self
    {
        $this->config['tooltip'] = $text;
        return $this;
    }
}
