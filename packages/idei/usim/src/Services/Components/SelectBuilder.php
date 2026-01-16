<?php

namespace Idei\Usim\Services\Components;

/**
 * Builder for Select UI components
 * 
 * Modern and powerful select/dropdown component with comprehensive features:
 * - Single and multiple selection
 * - Searchable/filterable options
 * - Option groups
 * - Custom rendering (badges, icons, avatars)
 * - Validation and states
 * - Accessibility support
 */
class SelectBuilder extends UIComponent
{
    protected function getDefaultConfig(): array
    {
        return [
            // Core functionality
            'options' => [], // Array of options: [['value' => '', 'label' => '', ...], ...]
            'value' => null, // Selected value(s) - string for single, array for multiple
            'placeholder' => 'Select an option',
            'label' => null,
            
            // Selection mode
            'multiple' => false, // Allow multiple selections
            'max_selections' => null, // Max number of selections (for multiple)
            
            // Search/Filter
            'searchable' => false, // Enable search/filter
            'search_placeholder' => 'Search...',
            'min_search_chars' => 0, // Minimum chars before searching
            
            // Grouping
            'groups' => [], // Option groups: [['label' => 'Group', 'options' => [...]], ...]
            'group_selectable' => false, // Can select entire groups
            
            // State
            'disabled' => false,
            'readonly' => false,
            'required' => false,
            'loading' => false,
            'clearable' => true, // Show clear button
            
            // Validation
            'error_message' => null,
            'help_text' => null,
            'validation_rules' => [], // Custom validation rules
            
            // Appearance
            'style' => 'default', // default, primary, success, danger, warning, info
            'size' => 'medium', // xs, small, medium, large, xl
            'variant' => 'outlined', // outlined, filled, underlined
            'width' => null,
            'max_width' => null,
            
            // Dropdown behavior
            'max_height' => '300px', // Max height of dropdown
            'position' => 'auto', // auto, top, bottom
            'close_on_select' => true, // Close dropdown after selection (not for multiple)
            
            // Icons
            'icon' => null, // Icon before select
            'icon_position' => 'left',
            'dropdown_icon' => 'chevron-down',
            
            // Custom rendering
            'option_template' => null, // Custom template for options
            'selected_template' => null, // Custom template for selected value
            'render_badges' => false, // Render selected items as badges (for multiple)
            'render_avatars' => false, // Show avatars in options
            
            // Remote data
            'remote_url' => null, // URL for remote data loading
            'remote_params' => [], // Parameters for remote requests
            
            // Chips/Tags (for multiple selection)
            'chip_style' => 'primary',
            'chip_removable' => true,
            
            // Empty state
            'empty_message' => 'No options available',
            'no_results_message' => 'No results found',
            
            // Advanced features
            'create_option' => false, // Allow creating new options
            'create_option_text' => 'Create "{value}"',
            'virtual_scroll' => false, // Virtual scrolling for large lists
            'autocomplete' => 'off',
            
            // Accessibility
            'aria_label' => null,
            'tooltip' => null,
            
            // Events
            'on_change' => null, // Action on change
            'on_search' => null, // Action on search
            'on_open' => null, // Action on dropdown open
            'on_close' => null, // Action on dropdown close
        ];
    }

    /**
     * Set the select options
     * 
     * @param array $options Array of options [['value' => 'val', 'label' => 'Label'], ...]
     * @return $this For method chaining
     */
    public function options(array $options): self
    {
        return $this->setConfig('options', $options);
    }

    /**
     * Add a single option
     * 
     * @param string $value The option value
     * @param string $label The option label
     * @param array $extra Extra data (icon, avatar, badge, disabled, etc.)
     * @return $this For method chaining
     */
    public function addOption(string $value, string $label, array $extra = []): self
    {
        $option = array_merge(['value' => $value, 'label' => $label], $extra);
        $options = $this->config['options'];
        $options[] = $option;
        return $this->setConfig('options', $options);
    }

    /**
     * Set option groups
     * 
     * @param array $groups Array of groups [['label' => 'Group', 'options' => [...]], ...]
     * @return $this For method chaining
     */
    public function groups(array $groups): self
    {
        return $this->setConfig('groups', $groups);
    }

    /**
     * Add an option group
     * 
     * @param string $label The group label
     * @param array $options The options in this group
     * @return $this For method chaining
     */
    public function addGroup(string $label, array $options): self
    {
        $groups = $this->config['groups'];
        $groups[] = ['label' => $label, 'options' => $options];
        return $this->setConfig('groups', $groups);
    }

    /**
     * Set the selected value
     * 
     * @param mixed $value The selected value (string or array for multiple)
     * @return $this For method chaining
     */
    public function value(mixed $value): self
    {
        return $this->setConfig('value', $value);
    }

    /**
     * Set the placeholder text
     * 
     * @param string $placeholder The placeholder
     * @return $this For method chaining
     */
    public function placeholder(string $placeholder): self
    {
        return $this->setConfig('placeholder', $placeholder);
    }

    /**
     * Set the label
     * 
     * @param string $label The label text
     * @return $this For method chaining
     */
    public function label(string $label): self
    {
        return $this->setConfig('label', $label);
    }

    /**
     * Enable multiple selection
     * 
     * @param bool $multiple True to enable
     * @param int|null $maxSelections Max number of selections
     * @return $this For method chaining
     */
    public function multiple(bool $multiple = true, ?int $maxSelections = null): self
    {
        $this->setConfig('multiple', $multiple);
        if ($maxSelections !== null) {
            $this->setConfig('max_selections', $maxSelections);
        }
        return $this;
    }

    /**
     * Make the select searchable
     * 
     * @param bool $searchable True to enable search
     * @param string|null $placeholder Search placeholder
     * @return $this For method chaining
     */
    public function searchable(bool $searchable = true, ?string $placeholder = null): self
    {
        $this->setConfig('searchable', $searchable);
        if ($placeholder !== null) {
            $this->setConfig('search_placeholder', $placeholder);
        }
        return $this;
    }

    /**
     * Set minimum characters before searching
     * 
     * @param int $chars Minimum characters
     * @return $this For method chaining
     */
    public function minSearchChars(int $chars): self
    {
        return $this->setConfig('min_search_chars', $chars);
    }

    /**
     * Mark as required
     * 
     * @param bool $required True if required
     * @return $this For method chaining
     */
    public function required(bool $required = true): self
    {
        return $this->setConfig('required', $required);
    }

    /**
     * Disable the select
     * 
     * @param bool $disabled True to disable
     * @return $this For method chaining
     */
    public function disabled(bool $disabled = true): self
    {
        return $this->setConfig('disabled', $disabled);
    }

    /**
     * Make readonly
     * 
     * @param bool $readonly True for readonly
     * @return $this For method chaining
     */
    public function readonly(bool $readonly = true): self
    {
        return $this->setConfig('readonly', $readonly);
    }

    /**
     * Set loading state
     * 
     * @param bool $loading True if loading
     * @return $this For method chaining
     */
    public function loading(bool $loading = true): self
    {
        return $this->setConfig('loading', $loading);
    }

    /**
     * Enable/disable clear button
     * 
     * @param bool $clearable True to show clear button
     * @return $this For method chaining
     */
    public function clearable(bool $clearable = true): self
    {
        return $this->setConfig('clearable', $clearable);
    }

    /**
     * Set error message
     * 
     * @param string $message The error message
     * @return $this For method chaining
     */
    public function errorMessage(string $message): self
    {
        return $this->setConfig('error_message', $message);
    }

    /**
     * Set help text
     * 
     * @param string $text The help text
     * @return $this For method chaining
     */
    public function helpText(string $text): self
    {
        return $this->setConfig('help_text', $text);
    }

    /**
     * Set the style
     * 
     * @param string $style The style (default, primary, success, danger, warning, info)
     * @return $this For method chaining
     */
    public function style(string $style): self
    {
        return $this->setConfig('style', $style);
    }

    /**
     * Set the size
     * 
     * @param string $size The size (xs, small, medium, large, xl)
     * @return $this For method chaining
     */
    public function size(string $size): self
    {
        return $this->setConfig('size', $size);
    }

    /**
     * Set the variant
     * 
     * @param string $variant The variant (outlined, filled, underlined)
     * @return $this For method chaining
     */
    public function variant(string $variant): self
    {
        return $this->setConfig('variant', $variant);
    }

    /**
     * Set width
     * 
     * @param string $width The width
     * @return $this For method chaining
     */
    public function width(string $width): static
    {
        return $this->setConfig('width', $width);
    }

    /**
     * Set max width
     * 
     * @param string $maxWidth The max width
     * @return $this For method chaining
     */
    public function maxWidth(string $maxWidth): static
    {
        return $this->setConfig('max_width', $maxWidth);
    }

    /**
     * Set max height for dropdown
     * 
     * @param string $height The max height
     * @return $this For method chaining
     */
    public function maxHeight(string $height): static
    {
        return $this->setConfig('max_height', $height);
    }

    /**
     * Set dropdown position
     * 
     * @param string $position The position (auto, top, bottom)
     * @return $this For method chaining
     */
    public function position(string $position): self
    {
        return $this->setConfig('position', $position);
    }

    /**
     * Set close on select behavior
     * 
     * @param bool $close True to close after selection
     * @return $this For method chaining
     */
    public function closeOnSelect(bool $close = true): self
    {
        return $this->setConfig('close_on_select', $close);
    }

    /**
     * Set icon
     * 
     * @param string $icon The icon name
     * @param string $position The position (left, right)
     * @return $this For method chaining
     */
    public function icon(string $icon, string $position = 'left'): self
    {
        $this->setConfig('icon', $icon);
        $this->setConfig('icon_position', $position);
        return $this;
    }

    /**
     * Set dropdown icon
     * 
     * @param string $icon The dropdown icon
     * @return $this For method chaining
     */
    public function dropdownIcon(string $icon): self
    {
        return $this->setConfig('dropdown_icon', $icon);
    }

    /**
     * Enable badge rendering for multiple selections
     * 
     * @param bool $render True to render as badges
     * @param string $style Badge style
     * @return $this For method chaining
     */
    public function renderBadges(bool $render = true, string $style = 'primary'): self
    {
        $this->setConfig('render_badges', $render);
        $this->setConfig('chip_style', $style);
        return $this;
    }

    /**
     * Enable avatar rendering in options
     * 
     * @param bool $render True to show avatars
     * @return $this For method chaining
     */
    public function renderAvatars(bool $render = true): self
    {
        return $this->setConfig('render_avatars', $render);
    }

    /**
     * Set remote data URL
     * 
     * @param string $url The remote URL
     * @param array $params Additional parameters
     * @return $this For method chaining
     */
    public function remote(string $url, array $params = []): self
    {
        $this->setConfig('remote_url', $url);
        $this->setConfig('remote_params', $params);
        return $this;
    }

    /**
     * Set empty message
     * 
     * @param string $message The message to show when no options
     * @return $this For method chaining
     */
    public function emptyMessage(string $message): self
    {
        return $this->setConfig('empty_message', $message);
    }

    /**
     * Set no results message
     * 
     * @param string $message The message for no search results
     * @return $this For method chaining
     */
    public function noResultsMessage(string $message): self
    {
        return $this->setConfig('no_results_message', $message);
    }

    /**
     * Enable creating new options
     * 
     * @param bool $create True to enable
     * @param string|null $text Template text for create option
     * @return $this For method chaining
     */
    public function createOption(bool $create = true, ?string $text = null): self
    {
        $this->setConfig('create_option', $create);
        if ($text !== null) {
            $this->setConfig('create_option_text', $text);
        }
        return $this;
    }

    /**
     * Enable virtual scrolling
     * 
     * @param bool $virtual True to enable
     * @return $this For method chaining
     */
    public function virtualScroll(bool $virtual = true): self
    {
        return $this->setConfig('virtual_scroll', $virtual);
    }

    /**
     * Set autocomplete
     * 
     * @param string $autocomplete The autocomplete value
     * @return $this For method chaining
     */
    public function autocomplete(string $autocomplete): self
    {
        return $this->setConfig('autocomplete', $autocomplete);
    }

    /**
     * Set ARIA label
     * 
     * @param string $label The ARIA label
     * @return $this For method chaining
     */
    public function ariaLabel(string $label): self
    {
        return $this->setConfig('aria_label', $label);
    }

    /**
     * Set tooltip
     * 
     * @param string $tooltip The tooltip text
     * @return $this For method chaining
     */
    public function tooltip(string $tooltip): self
    {
        return $this->setConfig('tooltip', $tooltip);
    }

    /**
     * Set onChange action
     * 
     * @param string $action The action to trigger
     * @return $this For method chaining
     */
    public function onChange(string $action): self
    {
        return $this->setConfig('on_change', $action);
    }

    /**
     * Set onSearch action
     * 
     * @param string $action The action to trigger
     * @return $this For method chaining
     */
    public function onSearch(string $action): self
    {
        return $this->setConfig('on_search', $action);
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
