<?php

namespace Idei\Usim\Services\Components;

use Idei\Usim\Services\Contracts\UIElement;

/**
 * Builder for Form UI components
 * 
 * Modern form component that extends UIContainer with form-specific features:
 * - Form actions (submit, reset, cancel)
 * - Validation rules and error handling
 * - Field grouping and sections
 * - Submit behavior (AJAX, standard, custom)
 * - Progress indication
 * - Auto-save functionality
 * - Multiple submission methods (POST, GET, PUT, PATCH, DELETE)
 * - CSRF protection
 * - File upload support
 * - Form state management (pristine, dirty, submitting, submitted)
 * 
 * @method self action(?string $action) Set form action URL
 * @method self method(string $method) Set form method (GET, POST, PUT, PATCH, DELETE)
 * @method self ajax(bool $ajax = true) Enable/disable AJAX submission
 * @method self validate(bool $validate = true) Enable/disable client-side validation
 * @method self autocomplete(bool $autocomplete = true) Enable/disable autocomplete
 * @method self encoding(string $encoding) Set form encoding type
 * @method self submitButton(string $label, array $config = []) Add submit button
 * @method self resetButton(string $label, array $config = []) Add reset button
 * @method self cancelButton(string $label, array $config = []) Add cancel button
 * @method self fieldset(string $legend, callable $callback) Add fieldset group
 * @method self section(string $title, callable $callback) Add form section
 * @method self horizontalLayout() Set horizontal form layout
 * @method self verticalLayout() Set vertical form layout
 * @method self inlineLayout() Set inline form layout
 * @method self labelWidth(string $width) Set label width for horizontal forms
 * @method self fieldWidth(string $width) Set field width for horizontal forms
 * @method self showRequiredIndicator(bool $show = true) Show asterisk for required fields
 * @method self showOptionalIndicator(bool $show = true) Show (optional) for optional fields
 * @method self validationMode(string $mode) Set validation mode (onSubmit, onChange, onBlur)
 * @method self errorDisplay(string $display) Set error display mode (inline, summary, toast)
 * @method self errorSummaryTitle(string $title) Set error summary title
 * @method self autoSave(bool $autoSave = true, int $delay = 3000) Enable auto-save with delay
 * @method self onSubmit(string $handler) Set submit event handler
 * @method self onReset(string $handler) Set reset event handler
 * @method self onValidate(string $handler) Set validation event handler
 * @method self onChange(string $handler) Set change event handler
 * @method self beforeSubmit(string $handler) Set before-submit event handler
 * @method self afterSubmit(string $handler) Set after-submit event handler
 * @method self confirmBeforeSubmit(string $message) Show confirmation dialog before submit
 * @method self preventMultipleSubmit(bool $prevent = true) Prevent multiple submissions
 * @method self showProgress(bool $show = true) Show progress indicator during submission
 * @method self loadingMessage(string $message) Set loading message during submission
 * @method self successMessage(string $message) Set success message after submission
 * @method self errorMessage(string $message) Set error message on submission failure
 * @method self redirectOnSuccess(string $url) Redirect after successful submission
 * @method self resetOnSuccess(bool $reset = true) Reset form after successful submission
 * @method self focusOnError(bool $focus = true) Focus first field with error
 * @method self scrollToError(bool $scroll = true) Scroll to first error
 * @method self csrfToken(string $token) Set CSRF token
 * @method self csrfField(string $fieldName = '_token') Set CSRF field name
 * @method self honeypot(bool $honeypot = true, string $fieldName = '_gotcha') Add honeypot field
 * @method self maxFileSize(int $bytes) Set max file upload size
 * @method self allowedFileTypes(array $types) Set allowed file types for upload
 * @method self multipart(bool $multipart = true) Enable multipart/form-data encoding
 * @method self fieldSpacing(string $spacing) Set spacing between fields (xs, small, medium, large, xl)
 * @method self sectionSpacing(string $spacing) Set spacing between sections
 * @method self submitPosition(string $position) Set submit button position (left, center, right)
 * @method self submitStyle(string $style) Set submit button style
 * @method self submitSize(string $size) Set submit button size
 * @method self fullWidthButtons(bool $fullWidth = true) Make buttons full width
 * @method self condensed(bool $condensed = true) Use condensed form spacing
 * @method self bordered(bool $bordered = true) Add border around form
 * @method self shadow(bool $shadow = true) Add shadow to form
 * @method self rounded(bool $rounded = true) Add rounded corners to form
 * @method self padding(string $padding) Set form padding (xs, small, medium, large, xl)
 * @method self backgroundColor(string $color) Set form background color
 * @method self customClass(string $class) Add custom CSS class
 * @method self customStyle(string $style) Add custom inline style
 * @method self dataAttributes(array $attributes) Add custom data attributes
 * @method self ariaLabel(string $label) Set ARIA label for accessibility
 * @method self ariaDescribedBy(string $id) Set ARIA described-by for accessibility
 * @method self disabled(bool $disabled = true) Disable entire form
 * @method self readonly(bool $readonly = true) Make entire form read-only
 */
class FormBuilder extends UIContainer
{
    protected string $type = 'form';
    
    /** @var array Form sections for organizing fields */
    protected array $sections = [];
    
    /** @var array Form validation rules */
    protected array $validationRules = [];
    
    /** @var array Custom error messages for validation */
    protected array $errorMessages = [];
    
    /** @var bool Whether form has file uploads */
    protected bool $hasFileUploads = false;

    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        
        $this->type = 'form';
        $this->config = array_merge($this->config, [
            'type' => 'form',
            'action' => null,
            'method' => 'POST',
            'ajax' => true,
            'validate' => true,
            'autocomplete' => true,
            'encoding' => 'application/x-www-form-urlencoded',
            'novalidate' => false,
            
            // Layout
            'form_layout' => 'vertical', // vertical, horizontal, inline
            'label_width' => '120px',
            'field_width' => 'auto',
            'label_position' => 'top', // top, left, floating
            'label_align' => 'left',
            
            // Indicators
            'show_required_indicator' => true,
            'show_optional_indicator' => false,
            'required_indicator' => '*',
            'optional_indicator' => '(optional)',
            
            // Validation
            'validation_mode' => 'onSubmit', // onSubmit, onChange, onBlur, immediate
            'error_display' => 'inline', // inline, summary, toast, none
            'error_summary_title' => 'Please fix the following errors:',
            'focus_on_error' => true,
            'scroll_to_error' => true,
            
            // Submission
            'prevent_multiple_submit' => true,
            'show_progress' => true,
            'loading_message' => 'Submitting...',
            'success_message' => null,
            'error_message' => null,
            'redirect_on_success' => null,
            'reset_on_success' => false,
            'confirm_before_submit' => null,
            
            // Auto-save
            'auto_save' => false,
            'auto_save_delay' => 3000,
            'auto_save_indicator' => true,
            
            // Security
            'csrf_token' => null,
            'csrf_field' => '_token',
            'honeypot' => false,
            'honeypot_field' => '_gotcha',
            
            // File uploads
            'max_file_size' => null,
            'allowed_file_types' => [],
            
            // Styling
            'field_spacing' => 'medium',
            'section_spacing' => 'large',
            'submit_position' => 'left', // left, center, right, space-between
            'submit_style' => 'primary',
            'submit_size' => 'medium',
            'full_width_buttons' => false,
            'condensed' => false,
            'bordered' => false,
            'shadow' => false,
            'rounded' => false,
            'padding' => 'medium',
            'background_color' => null,
            
            // Events
            'on_submit' => null,
            'on_reset' => null,
            'on_validate' => null,
            'on_change' => null,
            'before_submit' => null,
            'after_submit' => null,
            'on_error' => null,
            'on_success' => null,
            
            // State
            'disabled' => false,
            'readonly' => false,
            
            // Custom
            'custom_class' => null,
            'custom_style' => null,
            'data_attributes' => [],
            'aria_label' => null,
            'aria_describedby' => null,
            
            // Sections
            'sections' => [],
            'fieldsets' => [],
            
            // Buttons
            'buttons' => [],
        ]);
    }

    /**
     * Set form action URL
     * 
     * @param string|null $action Form action URL
     * @return self For method chaining
     */
    public function action(?string $action): self
    {
        $this->config['action'] = $action;
        return $this;
    }

    /**
     * Set form method
     * 
     * @param string $method HTTP method (GET, POST, PUT, PATCH, DELETE)
     * @return self For method chaining
     */
    public function method(string $method): self
    {
        $method = strtoupper($method);
        if (!in_array($method, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])) {
            throw new \InvalidArgumentException("Invalid form method: {$method}");
        }
        
        $this->config['method'] = $method;
        
        // For PUT, PATCH, DELETE we need to add a hidden _method field
        if (in_array($method, ['PUT', 'PATCH', 'DELETE'])) {
            $this->config['_method'] = $method;
            $this->config['method'] = 'POST'; // HTML forms only support GET/POST
        }
        
        return $this;
    }

    /**
     * Enable/disable AJAX submission
     * 
     * @param bool $ajax Whether to submit via AJAX
     * @return self For method chaining
     */
    public function ajax(bool $ajax = true): self
    {
        $this->config['ajax'] = $ajax;
        return $this;
    }

    /**
     * Enable/disable client-side validation
     * 
     * @param bool $validate Whether to validate on client
     * @return self For method chaining
     */
    public function validate(bool $validate = true): self
    {
        $this->config['validate'] = $validate;
        $this->config['novalidate'] = !$validate;
        return $this;
    }

    /**
     * Enable/disable autocomplete
     * 
     * @param bool $autocomplete Whether to enable autocomplete
     * @return self For method chaining
     */
    public function autocomplete(bool $autocomplete = true): self
    {
        $this->config['autocomplete'] = $autocomplete;
        return $this;
    }

    /**
     * Set form encoding type
     * 
     * @param string $encoding Encoding type (application/x-www-form-urlencoded, multipart/form-data, text/plain)
     * @return self For method chaining
     */
    public function encoding(string $encoding): self
    {
        $this->config['encoding'] = $encoding;
        return $this;
    }

    /**
     * Enable multipart/form-data encoding (for file uploads)
     * 
     * @param bool $multipart Whether to use multipart encoding
     * @return self For method chaining
     */
    public function multipart(bool $multipart = true): self
    {
        if ($multipart) {
            $this->config['encoding'] = 'multipart/form-data';
            $this->hasFileUploads = true;
        }
        return $this;
    }

    /**
     * Add submit button to form
     * 
     * @param string $label Button label
     * @param array $config Button configuration
     * @return self For method chaining
     */
    public function submitButton(string $label, array $config = []): self
    {
        $button = array_merge([
            'type' => 'submit',
            'label' => $label,
            'style' => $this->config['submit_style'],
            'size' => $this->config['submit_size'],
            'full_width' => $this->config['full_width_buttons'],
        ], $config);
        
        $this->config['buttons'][] = $button;
        return $this;
    }

    /**
     * Add reset button to form
     * 
     * @param string $label Button label
     * @param array $config Button configuration
     * @return self For method chaining
     */
    public function resetButton(string $label, array $config = []): self
    {
        $button = array_merge([
            'type' => 'reset',
            'label' => $label,
            'style' => 'default',
            'size' => $this->config['submit_size'],
            'full_width' => $this->config['full_width_buttons'],
        ], $config);
        
        $this->config['buttons'][] = $button;
        return $this;
    }

    /**
     * Add cancel button to form
     * 
     * @param string $label Button label
     * @param array $config Button configuration (should include 'action' or 'onclick')
     * @return self For method chaining
     */
    public function cancelButton(string $label, array $config = []): self
    {
        $button = array_merge([
            'type' => 'button',
            'label' => $label,
            'style' => 'default',
            'size' => $this->config['submit_size'],
            'full_width' => $this->config['full_width_buttons'],
        ], $config);
        
        $this->config['buttons'][] = $button;
        return $this;
    }

    /**
     * Add fieldset group
     * 
     * @param string $legend Fieldset legend
     * @param callable $callback Callback to add fields to fieldset
     * @return self For method chaining
     */
    public function fieldset(string $legend, callable $callback): self
    {
        $fieldset = new UIContainer($legend);
        $fieldset->config['type'] = 'fieldset';
        $fieldset->config['legend'] = $legend;
        
        $callback($fieldset);
        
        $this->add($fieldset);
        $this->config['fieldsets'][] = [
            'legend' => $legend,
            'id' => $fieldset->getId(),
        ];
        
        return $this;
    }

    /**
     * Add form section
     * 
     * @param string $title Section title
     * @param callable $callback Callback to add fields to section
     * @return self For method chaining
     */
    public function section(string $title, callable $callback): self
    {
        $section = new UIContainer($title);
        $section->config['type'] = 'section';
        $section->config['title'] = $title;
        
        $callback($section);
        
        $this->add($section);
        $this->config['sections'][] = [
            'title' => $title,
            'id' => $section->getId(),
        ];
        
        return $this;
    }

    /**
     * Set horizontal form layout
     * 
     * @return self For method chaining
     */
    public function horizontalLayout(): self
    {
        $this->config['form_layout'] = 'horizontal';
        $this->config['label_position'] = 'left';
        return $this;
    }

    /**
     * Set vertical form layout
     * 
     * @return self For method chaining
     */
    public function verticalLayout(): self
    {
        $this->config['form_layout'] = 'vertical';
        $this->config['label_position'] = 'top';
        return $this;
    }

    /**
     * Set inline form layout
     * 
     * @return self For method chaining
     */
    public function inlineLayout(): self
    {
        $this->config['form_layout'] = 'inline';
        $this->config['label_position'] = 'left';
        return $this;
    }

    /**
     * Set label width for horizontal forms
     * 
     * @param string $width Label width (e.g., '120px', '30%')
     * @return self For method chaining
     */
    public function labelWidth(string $width): self
    {
        $this->config['label_width'] = $width;
        return $this;
    }

    /**
     * Set field width for horizontal forms
     * 
     * @param string $width Field width (e.g., '300px', '70%', 'auto')
     * @return self For method chaining
     */
    public function fieldWidth(string $width): self
    {
        $this->config['field_width'] = $width;
        return $this;
    }

    /**
     * Show/hide required indicator (asterisk)
     * 
     * @param bool $show Whether to show required indicator
     * @return self For method chaining
     */
    public function showRequiredIndicator(bool $show = true): self
    {
        $this->config['show_required_indicator'] = $show;
        return $this;
    }

    /**
     * Show/hide optional indicator
     * 
     * @param bool $show Whether to show optional indicator
     * @return self For method chaining
     */
    public function showOptionalIndicator(bool $show = true): self
    {
        $this->config['show_optional_indicator'] = $show;
        return $this;
    }

    /**
     * Set validation mode
     * 
     * @param string $mode Validation mode (onSubmit, onChange, onBlur, immediate)
     * @return self For method chaining
     */
    public function validationMode(string $mode): self
    {
        if (!in_array($mode, ['onSubmit', 'onChange', 'onBlur', 'immediate'])) {
            throw new \InvalidArgumentException("Invalid validation mode: {$mode}");
        }
        
        $this->config['validation_mode'] = $mode;
        return $this;
    }

    /**
     * Set error display mode
     * 
     * @param string $display Error display mode (inline, summary, toast, none)
     * @return self For method chaining
     */
    public function errorDisplay(string $display): self
    {
        if (!in_array($display, ['inline', 'summary', 'toast', 'none'])) {
            throw new \InvalidArgumentException("Invalid error display mode: {$display}");
        }
        
        $this->config['error_display'] = $display;
        return $this;
    }

    /**
     * Set error summary title
     * 
     * @param string $title Error summary title
     * @return self For method chaining
     */
    public function errorSummaryTitle(string $title): self
    {
        $this->config['error_summary_title'] = $title;
        return $this;
    }

    /**
     * Enable auto-save functionality
     * 
     * @param bool $autoSave Whether to enable auto-save
     * @param int $delay Delay in milliseconds before saving
     * @return self For method chaining
     */
    public function autoSave(bool $autoSave = true, int $delay = 3000): self
    {
        $this->config['auto_save'] = $autoSave;
        $this->config['auto_save_delay'] = $delay;
        return $this;
    }

    /**
     * Set submit event handler
     * 
     * @param string $handler JavaScript function name or code
     * @return self For method chaining
     */
    public function onSubmit(string $handler): self
    {
        $this->config['on_submit'] = $handler;
        return $this;
    }

    /**
     * Set reset event handler
     * 
     * @param string $handler JavaScript function name or code
     * @return self For method chaining
     */
    public function onReset(string $handler): self
    {
        $this->config['on_reset'] = $handler;
        return $this;
    }

    /**
     * Set validation event handler
     * 
     * @param string $handler JavaScript function name or code
     * @return self For method chaining
     */
    public function onValidate(string $handler): self
    {
        $this->config['on_validate'] = $handler;
        return $this;
    }

    /**
     * Set change event handler
     * 
     * @param string $handler JavaScript function name or code
     * @return self For method chaining
     */
    public function onChange(string $handler): self
    {
        $this->config['on_change'] = $handler;
        return $this;
    }

    /**
     * Set before-submit event handler
     * 
     * @param string $handler JavaScript function name or code
     * @return self For method chaining
     */
    public function beforeSubmit(string $handler): self
    {
        $this->config['before_submit'] = $handler;
        return $this;
    }

    /**
     * Set after-submit event handler
     * 
     * @param string $handler JavaScript function name or code
     * @return self For method chaining
     */
    public function afterSubmit(string $handler): self
    {
        $this->config['after_submit'] = $handler;
        return $this;
    }

    /**
     * Show confirmation dialog before submit
     * 
     * @param string $message Confirmation message
     * @return self For method chaining
     */
    public function confirmBeforeSubmit(string $message): self
    {
        $this->config['confirm_before_submit'] = $message;
        return $this;
    }

    /**
     * Prevent multiple submissions
     * 
     * @param bool $prevent Whether to prevent multiple submissions
     * @return self For method chaining
     */
    public function preventMultipleSubmit(bool $prevent = true): self
    {
        $this->config['prevent_multiple_submit'] = $prevent;
        return $this;
    }

    /**
     * Show progress indicator during submission
     * 
     * @param bool $show Whether to show progress
     * @return self For method chaining
     */
    public function showProgress(bool $show = true): self
    {
        $this->config['show_progress'] = $show;
        return $this;
    }

    /**
     * Set loading message during submission
     * 
     * @param string $message Loading message
     * @return self For method chaining
     */
    public function loadingMessage(string $message): self
    {
        $this->config['loading_message'] = $message;
        return $this;
    }

    /**
     * Set success message after submission
     * 
     * @param string $message Success message
     * @return self For method chaining
     */
    public function successMessage(string $message): self
    {
        $this->config['success_message'] = $message;
        return $this;
    }

    /**
     * Set error message on submission failure
     * 
     * @param string $message Error message
     * @return self For method chaining
     */
    public function errorMessage(string $message): self
    {
        $this->config['error_message'] = $message;
        return $this;
    }

    /**
     * Redirect after successful submission
     * 
     * @param string $url Redirect URL
     * @return self For method chaining
     */
    public function redirectOnSuccess(string $url): self
    {
        $this->config['redirect_on_success'] = $url;
        return $this;
    }

    /**
     * Reset form after successful submission
     * 
     * @param bool $reset Whether to reset form
     * @return self For method chaining
     */
    public function resetOnSuccess(bool $reset = true): self
    {
        $this->config['reset_on_success'] = $reset;
        return $this;
    }

    /**
     * Focus first field with error
     * 
     * @param bool $focus Whether to focus on error
     * @return self For method chaining
     */
    public function focusOnError(bool $focus = true): self
    {
        $this->config['focus_on_error'] = $focus;
        return $this;
    }

    /**
     * Scroll to first error
     * 
     * @param bool $scroll Whether to scroll to error
     * @return self For method chaining
     */
    public function scrollToError(bool $scroll = true): self
    {
        $this->config['scroll_to_error'] = $scroll;
        return $this;
    }

    /**
     * Set CSRF token
     * 
     * @param string $token CSRF token value
     * @return self For method chaining
     */
    public function csrfToken(string $token): self
    {
        $this->config['csrf_token'] = $token;
        return $this;
    }

    /**
     * Set CSRF field name
     * 
     * @param string $fieldName CSRF field name
     * @return self For method chaining
     */
    public function csrfField(string $fieldName = '_token'): self
    {
        $this->config['csrf_field'] = $fieldName;
        return $this;
    }

    /**
     * Add honeypot field for spam protection
     * 
     * @param bool $honeypot Whether to add honeypot
     * @param string $fieldName Honeypot field name
     * @return self For method chaining
     */
    public function honeypot(bool $honeypot = true, string $fieldName = '_gotcha'): self
    {
        $this->config['honeypot'] = $honeypot;
        $this->config['honeypot_field'] = $fieldName;
        return $this;
    }

    /**
     * Set max file upload size
     * 
     * @param int $bytes Maximum file size in bytes
     * @return self For method chaining
     */
    public function maxFileSize(int $bytes): self
    {
        $this->config['max_file_size'] = $bytes;
        return $this;
    }

    /**
     * Set allowed file types for upload
     * 
     * @param array $types Allowed file types (extensions or MIME types)
     * @return self For method chaining
     */
    public function allowedFileTypes(array $types): self
    {
        $this->config['allowed_file_types'] = $types;
        return $this;
    }

    /**
     * Set spacing between fields
     * 
     * @param string $spacing Spacing size (xs, small, medium, large, xl)
     * @return self For method chaining
     */
    public function fieldSpacing(string $spacing): self
    {
        if (!in_array($spacing, ['xs', 'small', 'medium', 'large', 'xl'])) {
            throw new \InvalidArgumentException("Invalid field spacing: {$spacing}");
        }
        
        $this->config['field_spacing'] = $spacing;
        return $this;
    }

    /**
     * Set spacing between sections
     * 
     * @param string $spacing Spacing size (xs, small, medium, large, xl)
     * @return self For method chaining
     */
    public function sectionSpacing(string $spacing): self
    {
        if (!in_array($spacing, ['xs', 'small', 'medium', 'large', 'xl'])) {
            throw new \InvalidArgumentException("Invalid section spacing: {$spacing}");
        }
        
        $this->config['section_spacing'] = $spacing;
        return $this;
    }

    /**
     * Set submit button position
     * 
     * @param string $position Button position (left, center, right, space-between)
     * @return self For method chaining
     */
    public function submitPosition(string $position): self
    {
        if (!in_array($position, ['left', 'center', 'right', 'space-between'])) {
            throw new \InvalidArgumentException("Invalid submit position: {$position}");
        }
        
        $this->config['submit_position'] = $position;
        return $this;
    }

    /**
     * Set submit button style
     * 
     * @param string $style Button style (primary, secondary, success, danger, warning, info, default)
     * @return self For method chaining
     */
    public function submitStyle(string $style): self
    {
        $this->config['submit_style'] = $style;
        return $this;
    }

    /**
     * Set submit button size
     * 
     * @param string $size Button size (small, medium, large)
     * @return self For method chaining
     */
    public function submitSize(string $size): self
    {
        if (!in_array($size, ['small', 'medium', 'large'])) {
            throw new \InvalidArgumentException("Invalid submit size: {$size}");
        }
        
        $this->config['submit_size'] = $size;
        return $this;
    }

    /**
     * Make buttons full width
     * 
     * @param bool $fullWidth Whether buttons should be full width
     * @return self For method chaining
     */
    public function fullWidthButtons(bool $fullWidth = true): self
    {
        $this->config['full_width_buttons'] = $fullWidth;
        return $this;
    }

    /**
     * Use condensed spacing
     * 
     * @param bool $condensed Whether to use condensed spacing
     * @return self For method chaining
     */
    public function condensed(bool $condensed = true): self
    {
        $this->config['condensed'] = $condensed;
        
        if ($condensed) {
            $this->config['field_spacing'] = 'small';
            $this->config['padding'] = 'small';
        }
        
        return $this;
    }

    /**
     * Add border around form
     * 
     * @param bool $bordered Whether to add border
     * @return self For method chaining
     */
    public function bordered(bool $bordered = true): self
    {
        $this->config['bordered'] = $bordered;
        return $this;
    }

    /**
     * Add shadow to form
     * Uses parent UIContainer shadow method with predefined intensity
     * 
     * @param string|int $intensity Shadow intensity (0-3, or 'light'|'medium'|'heavy', or custom CSS)
     * @return self For method chaining
     */
    public function shadow(string|int $intensity = 1): self
    {
        // Call parent method which sets box_shadow in config
        parent::shadow($intensity);
        
        // Also set legacy 'shadow' flag for backward compatibility
        $this->config['shadow'] = true;
        return $this;
    }

    /**
     * Add rounded corners to form
     * Uses parent UIContainer rounded method
     * 
     * @param string|int $radius Radius value (default: 8)
     * @return self For method chaining
     */
    public function rounded(string|int $radius = 8): self
    {
        // Call parent method which sets border_radius in config
        parent::rounded($radius);
        
        // Also set legacy 'rounded' flag for backward compatibility
        $this->config['rounded'] = true;
        return $this;
    }

    /**
     * Set form padding (semantic sizes)
     * 
     * @param string $paddingSize Padding size (xs, small, medium, large, xl, none)
     * @return self For method chaining
     */
    public function formPadding(string $paddingSize): self
    {
        if (!in_array($paddingSize, ['xs', 'small', 'medium', 'large', 'xl', 'none'])) {
            throw new \InvalidArgumentException("Invalid padding: {$paddingSize}");
        }
        
        $this->config['padding'] = $paddingSize;
        return $this;
    }

    /**
     * Set form background color
     * 
     * @param string $color Background color (hex, rgb, named color)
     * @return self For method chaining
     */
    public function backgroundColor(string $color): self
    {
        $this->config['background_color'] = $color;
        return $this;
    }

    /**
     * Add custom CSS class
     * 
     * @param string $class CSS class name
     * @return self For method chaining
     */
    public function customClass(string $class): self
    {
        $this->config['custom_class'] = $class;
        return $this;
    }

    /**
     * Add custom inline style
     * 
     * @param string $style CSS style string
     * @return self For method chaining
     */
    public function customStyle(string $style): self
    {
        $this->config['custom_style'] = $style;
        return $this;
    }

    /**
     * Add custom data attributes
     * 
     * @param array $attributes Key-value pairs of data attributes
     * @return self For method chaining
     */
    public function dataAttributes(array $attributes): self
    {
        $this->config['data_attributes'] = $attributes;
        return $this;
    }

    /**
     * Set ARIA label for accessibility
     * 
     * @param string $label ARIA label
     * @return self For method chaining
     */
    public function ariaLabel(string $label): self
    {
        $this->config['aria_label'] = $label;
        return $this;
    }

    /**
     * Set ARIA described-by for accessibility
     * 
     * @param string $id ID of describing element
     * @return self For method chaining
     */
    public function ariaDescribedBy(string $id): self
    {
        $this->config['aria_describedby'] = $id;
        return $this;
    }

    /**
     * Disable entire form
     * 
     * @param bool $disabled Whether to disable form
     * @return self For method chaining
     */
    public function disabled(bool $disabled = true): self
    {
        $this->config['disabled'] = $disabled;
        return $this;
    }

    /**
     * Make entire form read-only
     * 
     * @param bool $readonly Whether to make form read-only
     * @return self For method chaining
     */
    public function readonly(bool $readonly = true): self
    {
        $this->config['readonly'] = $readonly;
        return $this;
    }

    /**
     * Add validation rule for a field
     * 
     * @param string $fieldName Field name
     * @param string $rule Validation rule
     * @param mixed $value Rule value (optional)
     * @param string|null $message Custom error message
     * @return self For method chaining
     */
    public function addValidationRule(string $fieldName, string $rule, $value = null, ?string $message = null): self
    {
        if (!isset($this->validationRules[$fieldName])) {
            $this->validationRules[$fieldName] = [];
        }
        
        $this->validationRules[$fieldName][] = [
            'rule' => $rule,
            'value' => $value,
            'message' => $message,
        ];
        
        return $this;
    }

    /**
     * Set custom error message for a field
     * 
     * @param string $fieldName Field name
     * @param string $message Error message
     * @return self For method chaining
     */
    public function setErrorMessage(string $fieldName, string $message): self
    {
        $this->errorMessages[$fieldName] = $message;
        return $this;
    }

    /**
     * Add input field to form
     * 
     * @param string $name Field name
     * @param string|null $label Field label
     * @return InputBuilder For method chaining
     */
    public function input(string $name, ?string $label = null): InputBuilder
    {
        $input = new InputBuilder($name);
        
        if ($label !== null) {
            $input->label($label);
        }
        
        $this->add($input);
        
        return $input;
    }

    /**
     * Add select field to form
     * 
     * @param string $name Field name
     * @param string|null $label Field label
     * @return SelectBuilder For method chaining
     */
    public function select(string $name, ?string $label = null): SelectBuilder
    {
        $select = new SelectBuilder($name);
        
        if ($label !== null) {
            $select->label($label);
        }
        
        $this->add($select);
        
        return $select;
    }

    /**
     * Add checkbox field to form
     * 
     * @param string $name Field name
     * @param string|null $label Field label
     * @return CheckboxBuilder For method chaining
     */
    public function checkbox(string $name, ?string $label = null): CheckboxBuilder
    {
        $checkbox = new CheckboxBuilder($name);
        
        if ($label !== null) {
            $checkbox->label($label);
        }
        
        $this->add($checkbox);
        
        return $checkbox;
    }

    /**
     * Get all validation rules
     * 
     * @return array Validation rules
     */
    public function getValidationRules(): array
    {
        return $this->validationRules;
    }

    /**
     * Get all error messages
     * 
     * @return array Error messages
     */
    public function getErrorMessages(): array
    {
        return $this->errorMessages;
    }

    /**
     * Check if form has file uploads
     * 
     * @return bool Whether form has file uploads
     */
    public function hasFileUploads(): bool
    {
        return $this->hasFileUploads || $this->config['encoding'] === 'multipart/form-data';
    }

    /**
     * {@inheritDoc}
     */
    /**
     * {@inheritDoc}
     */
    public function toJson(?int $order = null): array
    {
        $json = parent::toJson();
        
        // Add validation rules if present
        if (!empty($this->validationRules)) {
            $json[$this->id]['validation_rules'] = $this->validationRules;
        }
        
        // Add error messages if present
        if (!empty($this->errorMessages)) {
            $json[$this->id]['error_messages'] = $this->errorMessages;
        }
        
        return $json;
    }
}
