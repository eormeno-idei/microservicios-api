<?php

namespace Idei\Usim\Services\Components;

/**
 * Builder for Input UI components
 *
 * Modern and powerful input component with comprehensive validation,
 * help text, error messages, tooltips, and extensive customization options.
 */
class InputBuilder extends UIComponent
{
    protected function getDefaultConfig(): array
    {
        return [
            'input_type' => 'text',
            'label' => null,
            'placeholder' => null,
            'value' => null,
            'required' => false,
            'disabled' => false,
            'readonly' => false,
            'autofocus' => false,
            'autocomplete' => null,
            'maxlength' => null,
            'minlength' => null,
            'min' => null,
            'max' => null,
            'step' => null,
            'pattern' => null,
            'multiple' => false,
            'accept' => null,
            'help_text' => null,
            'error_message' => null,
            'error' => null,
            'tooltip' => null,
            'icon' => null,
            'icon_position' => 'left',
            'style' => 'default',
            'size' => 'medium',
            'on_input' => null,
            'on_change' => null,
            'on_enter' => null,
            'debounce' => null,
        ];
    }

    /**
     * Set the input type
     *
     * @param string $type The input type (text, number, email, password, tel, url, search, date, datetime-local, time, month, week, color, range, file, hidden)
     * @return self For method chaining
     */
    public function type(string $type): self
    {
        return $this->setConfig('input_type', $type);
    }

    /**
     * Set the label text
     *
     * @param string $label The label text
     * @return self For method chaining
     */
    public function label(string $label): self
    {
        return $this->setConfig('label', $label);
    }

    /**
     * Set the placeholder text
     *
     * @param string $placeholder The placeholder text
     * @return self For method chaining
     */
    public function placeholder(string $placeholder): self
    {
        return $this->setConfig('placeholder', $placeholder);
    }

    /**
     * Set the input value
     *
     * @param mixed $value The input value
     * @return self For method chaining
     */
    public function value(mixed $value): self
    {
        return $this->setConfig('value', $value);
    }

    /**
     * Mark the input as required
     *
     * @param bool $required True if required, false otherwise
     * @return self For method chaining
     */
    public function required(bool $required = true): self
    {
        return $this->setConfig('required', $required);
    }

    /**
     * Disable the input
     *
     * @param bool $disabled True to disable, false otherwise
     * @return self For method chaining
     */
    public function disabled(bool $disabled = true): self
    {
        return $this->setConfig('disabled', $disabled);
    }

    /**
     * Make the input readonly
     *
     * @param bool $readonly True for readonly, false otherwise
     * @return self For method chaining
     */
    public function readonly(bool $readonly = true): self
    {
        return $this->setConfig('readonly', $readonly);
    }

    /**
     * Enable autofocus on this input
     *
     * @param bool $autofocus True to autofocus, false otherwise
     * @return self For method chaining
     */
    public function autofocus(bool $autofocus = true): self
    {
        return $this->setConfig('autofocus', $autofocus);
    }

    /**
     * Set the autocomplete attribute
     *
     * @param string $autocomplete The autocomplete value (e.g., 'email', 'name', 'tel', 'off')
     * @return self For method chaining
     */
    public function autocomplete(string $autocomplete): self
    {
        return $this->setConfig('autocomplete', $autocomplete);
    }

    /**
     * Set the maximum length
     *
     * @param int $maxlength Maximum number of characters
     * @return self For method chaining
     */
    public function maxLength(int $maxlength): self
    {
        return $this->setConfig('maxlength', $maxlength);
    }

    /**
     * Set the minimum length
     *
     * @param int $minlength Minimum number of characters
     * @return self For method chaining
     */
    public function minLength(int $minlength): self
    {
        return $this->setConfig('minlength', $minlength);
    }

    /**
     * Set the minimum value (for number, date, time inputs)
     *
     * @param mixed $min Minimum value
     * @return self For method chaining
     */
    public function min(mixed $min): self
    {
        return $this->setConfig('min', $min);
    }

    /**
     * Set the maximum value (for number, date, time inputs)
     *
     * @param mixed $max Maximum value
     * @return self For method chaining
     */
    public function max(mixed $max): self
    {
        return $this->setConfig('max', $max);
    }

    /**
     * Set the step value (for number, range inputs)
     *
     * @param mixed $step The step value
     * @return self For method chaining
     */
    public function step(mixed $step): self
    {
        return $this->setConfig('step', $step);
    }

    /**
     * Set a regex pattern for validation
     *
     * @param string $pattern Regular expression pattern
     * @return self For method chaining
     */
    public function pattern(string $pattern): self
    {
        return $this->setConfig('pattern', $pattern);
    }

    /**
     * Enable multiple file selection (for file inputs)
     *
     * @param bool $multiple True to allow multiple files, false otherwise
     * @return self For method chaining
     */
    public function multiple(bool $multiple = true): self
    {
        return $this->setConfig('multiple', $multiple);
    }

    /**
     * Set accepted file types (for file inputs)
     *
     * @param string $accept Comma-separated list of file types (e.g., 'image/*', '.pdf,.doc')
     * @return self For method chaining
     */
    public function accept(string $accept): self
    {
        return $this->setConfig('accept', $accept);
    }

    /**
     * Set help text (displayed below the input)
     *
     * @param string $helpText Help text to guide the user
     * @return self For method chaining
     */
    public function helpText(string $helpText): self
    {
        return $this->setConfig('help_text', $helpText);
    }

    /**
     * Set error message (displayed when validation fails)
     *
     * @param string $errorMessage Error message text
     * @return self For method chaining
     */
    public function errorMessage(string $errorMessage): self
    {
        return $this->setConfig('error_message', $errorMessage);
    }

    /**
     * Set or clear error state with message
     * Shows an error icon with tooltip next to the input
     *
     * @param string|null $error Error message to display, or null to clear error
     * @return self For method chaining
     */
    public function error(?string $error): self
    {
        return $this->setConfig('error', $error);
    }

    /**
     * Set a tooltip
     *
     * @param string $tooltip Tooltip text
     * @return self For method chaining
     */
    public function tooltip(string $tooltip): self
    {
        return $this->setConfig('tooltip', $tooltip);
    }

    /**
     * Set an icon
     *
     * @param string $icon Icon name
     * @return self For method chaining
     */
    public function icon(string $icon): self
    {
        return $this->setConfig('icon', $icon);
    }

    /**
     * Set the icon position
     *
     * @param string $position Icon position ('left' or 'right')
     * @return self For method chaining
     */
    public function iconPosition(string $position): self
    {
        return $this->setConfig('icon_position', $position);
    }

    /**
     * Set the input style
     *
     * @param string $style Style name (default, primary, success, warning, danger)
     * @return self For method chaining
     */
    public function style(string $style): self
    {
        return $this->setConfig('style', $style);
    }

    /**
     * Set the input size
     *
     * @param string $size Size (small, medium, large)
     * @return self For method chaining
     */
    public function size(string $size): self
    {
        return $this->setConfig('size', $size);
    }

    /**
     * Set action to trigger on input event (while typing)
     *
     * @param string $action Action name to call
     * @param array $parameters Additional parameters to send
     * @return self For method chaining
     */
    public function onInput(string $action, array $parameters = []): self
    {
        return $this->setConfig('on_input', [
            'action' => $action,
            'parameters' => $parameters,
        ]);
    }

    /**
     * Set action to trigger on change event (after blur)
     *
     * @param string $action Action name to call
     * @param array $parameters Additional parameters to send
     * @return self For method chaining
     */
    public function onChange(string $action, array $parameters = []): self
    {
        return $this->setConfig('on_change', [
            'action' => $action,
            'parameters' => $parameters,
        ]);
    }

    /**
     * Set action to trigger when Enter key is pressed
     *
     * @param string $action Action name to call
     * @param array $parameters Additional parameters to send
     * @return self For method chaining
     */
    public function onEnter(string $action, array $parameters = []): self
    {
        return $this->setConfig('on_enter', [
            'action' => $action,
            'parameters' => $parameters,
        ]);
    }

    /**
     * Set debounce time (in milliseconds) for onInput event
     *
     * @param int $ms Milliseconds to wait before triggering action
     * @return self For method chaining
     */
    public function debounce(int $ms): self
    {
        return $this->setConfig('debounce', $ms);
    }
}
