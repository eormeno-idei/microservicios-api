// ==================== Base Component Class ====================
class UIComponent {
    constructor(id, config) {
        this.id = id;
        this.config = config;
        this.element = null;
    }

    render() {
        // Override in subclasses
        throw new Error('render() must be implemented by subclass');
    }

    mount(parentElement) {
        if (!this.element) {
            this.element = this.render();
        }
        if (parentElement) {
            parentElement.appendChild(this.element);
        }
    }

    applyCommonAttributes(element) {
        // Use internal component ID (_id) for data attribute, not JSON key
        const componentId = this.config._id || this.id;
        element.setAttribute('data-component-id', componentId);
        if (this.config.name) {
            element.id = this.config.name;
        }

        // Apply visual styling if specified
        if (this.config.box_shadow) {
            element.style.boxShadow = this.config.box_shadow;
        }
        if (this.config.border_radius) {
            element.style.borderRadius = this.config.border_radius;
        }

        // Apply layout properties with !important to override CSS
        if (this.config.justify_content) {
            element.style.setProperty('justify-content', this.config.justify_content, 'important');
        }
        if (this.config.align_items) {
            element.style.setProperty('align-items', this.config.align_items, 'important');
        }
        if (this.config.gap) {
            // Support both number (px) and string with units
            const gapValue = typeof this.config.gap === 'number' ? this.config.gap + 'px' : this.config.gap;
            element.style.setProperty('gap', gapValue, 'important');
        }

        // Apply padding
        if (this.config.padding !== undefined) {
            if (typeof this.config.padding === 'number') {
                element.style.padding = this.config.padding + 'px';
            } else {
                element.style.padding = this.config.padding;
            }
        }

        // Apply margin
        if (this.config.margin !== undefined) {
            element.style.margin = this.config.margin;
        }
        if (this.config.margin_left !== undefined) {
            element.style.setProperty('margin-left', this.config.margin_left, 'important');
        }
        if (this.config.margin_right !== undefined) {
            element.style.marginRight = this.config.margin_right;
        }
        if (this.config.margin_top !== undefined) {
            element.style.marginTop = this.config.margin_top;
        }
        if (this.config.margin_bottom !== undefined) {
            element.style.marginBottom = this.config.margin_bottom;
        }

        // Apply sizing
        if (this.config.width !== undefined) {
            element.style.width = this.config.width;
        }
        if (this.config.height !== undefined) {
            element.style.height = this.config.height;
        }
        if (this.config.max_width !== undefined) {
            element.style.maxWidth = this.config.max_width;
        }
        if (this.config.max_height !== undefined) {
            element.style.maxHeight = this.config.max_height;
        }
        if (this.config.min_width !== undefined) {
            element.style.minWidth = this.config.min_width;
        }
        if (this.config.min_height !== undefined) {
            element.style.minHeight = this.config.min_height;
        }

        // Apply font size
        if (this.config.font_size) {
            element.style.fontSize = this.config.font_size + 'px';
        }

        return element;
    }

    /**
     * Send UI event to backend
     *
     * @param {string} event - Event type (click, change, etc.)
     * @param {string} action - Action name (snake_case)
     * @param {object} parameters - Event parameters
     */
    async sendEventToBackend(event, action, parameters = {}) {
        try {
            // Get CSRF token from meta tag
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            // Use internal component ID (_id), not the JSON key
            const componentId = this.config._id || parseInt(this.id);

            // Get USIM storage from localStorage
            const usimStorage = localStorage.getItem('usim') || '';

            // console.log('Sending event:', { component_id: componentId, action, csrfToken });

            const response = await fetch('/api/ui-event', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-USIM-Storage': usimStorage,
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    component_id: componentId,
                    event: event,
                    action: action,
                    parameters: parameters,
                }),
            });

            const result = await response.json();

            // ÉXITO: response.ok = true (status 200-299)
            if (response.ok) {
                // console.log('✅ Action executed:', action, result);

                // Handle UI updates using global renderer
                if (result && Object.keys(result).length > 0) {
                    if (globalRenderer) {
                        globalRenderer.handleUIUpdate(result);
                    } else {
                        console.error('❌ Global renderer not initialized');
                    }
                }

                // Show success message if provided
                if (result.message) {
                    this.showNotification(result.message, 'success');
                }

                // Handle redirects if provided
                if (result.redirect) {
                    window.location.href = result.redirect;
                }
            } else {
                // ERROR: response.ok = false (status 400+)
                console.error('❌ Action failed:', action, result);
                this.showNotification(result.error || 'Action failed', 'error');
            }
        } catch (error) {
            console.error('❌ Network error:', error);
            this.showNotification('Network error: ' + error.message, 'error');
        }
    }

    /**
     * Show notification to user
     *
     * @param {string} message - Message to display
     * @param {string} type - Type (success, error, info, warning)
     */
    showNotification(message, type = 'info') {
        // Simple console notification for now
        // TODO: Implement proper UI notification system
        const emoji = { success: '✅', error: '❌', info: 'ℹ️', warning: '⚠️' }[type] || 'ℹ️';
        console.log(`${emoji} ${message}`);
    }
}

// ==================== Container Component ====================
class ContainerComponent extends UIComponent {
    render() {
        const container = document.createElement('div');
        container.className = `ui-container ${this.config.layout || 'vertical'}`;

        if (this.config.title) {
            const title = document.createElement('div');
            title.className = 'title';
            title.textContent = this.config.title;
            container.appendChild(title);
        }

        return this.applyCommonAttributes(container);
    }
}

// ==================== Button Component ====================
class ButtonComponent extends UIComponent {
    render() {
        const button = document.createElement('button');
        button.className = `ui-button ${this.config.style || 'primary'}`;
        button.textContent = this.config.label || 'Button';

        // Handle enabled state (default to true if not specified)
        const isEnabled = this.config.enabled !== undefined ? this.config.enabled : true;
        button.disabled = !isEnabled;

        if (this.config.action) {
            button.addEventListener('click', () => {
                // console.log('Button action:', this.config.action, this.config.parameters || {});
                this.handleAction(this.config.action, this.config.parameters);
            });
        }

        if (this.config.tooltip) {
            button.title = this.config.tooltip;
        }

        return this.applyCommonAttributes(button);
    }

    handleAction(action, parameters = {}) {
        // Collect values from inputs in the same container context
        const contextValues = this.collectContextValues();

        // Merge collected values with explicit parameters (explicit params take precedence)
        const mergedParameters = { ...contextValues, ...parameters };

        // Send POST request to backend
        this.sendEventToBackend('click', action, mergedParameters);
    }

    /**
     * Collect values from all input elements in the same container context
     *
     * @returns {object} Object with input names as keys and their values
     */
    collectContextValues() {
        const values = {};

        // Find the button element in the DOM
        const buttonElement = document.querySelector(`[data-component-id="${this.config._id}"]`);
        if (!buttonElement) {
            return values;
        }

        // Check if we're inside a modal first
        const modalElement = buttonElement.closest('#modal');
        let container;

        if (modalElement) {
            // If inside modal, use the modal as the container to collect all inputs from the entire modal
            container = modalElement;
        } else {
            // For non-modal contexts, find all parent containers from closest to furthest
            const allContainers = [];
            let currentElement = buttonElement.parentElement;

            while (currentElement && currentElement !== document.body) {
                if (currentElement.classList.contains('ui-container')) {
                    allContainers.push(currentElement);
                }
                currentElement = currentElement.parentElement;
            }

            // Try each container from closest to furthest until we find one with inputs
            for (const potentialContainer of allContainers) {
                const hasInputs = potentialContainer.querySelectorAll('input, textarea, select').length > 0;
                if (hasInputs) {
                    container = potentialContainer;
                    break;
                }
            }

            // If no container with inputs found, use the furthest container or document
            if (!container) {
                container = allContainers[allContainers.length - 1] || document;
            }
        }

        // Collect values from text inputs
        const inputs = container.querySelectorAll('input:not([type="checkbox"]):not([type="radio"]), textarea');
        inputs.forEach(input => {
            if (input.name) {
                values[input.name] = input.value;
            }
        });

        // Collect values from selects
        const selects = container.querySelectorAll('select');
        selects.forEach(select => {
            if (select.name) {
                values[select.name] = select.value;
            }
        });

        // Collect values from checkboxes
        const checkboxes = container.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            if (checkbox.name) {
                values[checkbox.name] = checkbox.checked;
            }
        });

        // Collect values from radio buttons (only checked ones)
        const radios = container.querySelectorAll('input[type="radio"]:checked');
        radios.forEach(radio => {
            if (radio.name) {
                values[radio.name] = radio.value;
            }
        });

        return values;
    }
}

// ==================== Label Component ====================
class LabelComponent extends UIComponent {
    render() {
        const label = document.createElement('span');

        // Apply base class and style
        let classes = `ui-label ${this.config.style || 'default'}`;

        // Apply text alignment class
        if (this.config.text_align) {
            classes += ` text-${this.config.text_align}`;
        }

        label.className = classes;

        // Support line breaks (\n) in text
        const text = this.config.text || '';
        if (text.includes('\n')) {
            // Replace \n with <br> tags and preserve whitespace
            label.style.whiteSpace = 'pre-line';
            label.textContent = text;
        } else {
            label.textContent = text;
        }

        return this.applyCommonAttributes(label);
    }
}

// ==================== Input Component ====================
class InputComponent extends UIComponent {
    render() {
        const group = document.createElement('div');
        group.className = 'ui-input-group';

        if (this.config.label) {
            const label = document.createElement('label');
            label.textContent = this.config.label;
            if (this.config.required) {
                label.className = 'required';
            }
            if (this.config.name) {
                label.setAttribute('for', this.config.name);
            }
            group.appendChild(label);
        }

        // Create input wrapper for input + error icon
        const inputWrapper = document.createElement('div');
        inputWrapper.className = 'ui-input-wrapper';

        const input = document.createElement('input');
        input.className = 'ui-input';

        // Add error class if error exists
        if (this.config.error) {
            input.classList.add('ui-input-error');
        }

        input.type = this.config.input_type || 'text';
        input.placeholder = this.config.placeholder || '';
        input.value = this.config.value || '';
        input.required = this.config.required || false;
        input.disabled = this.config.disabled || false;
        input.readonly = this.config.readonly || false;

        if (this.config.name) {
            input.name = this.config.name;
            input.id = this.config.name;
        }

        if (this.config.maxlength) input.maxLength = this.config.maxlength;
        if (this.config.minlength) input.minLength = this.config.minlength;
        if (this.config.pattern) input.pattern = this.config.pattern;

        inputWrapper.appendChild(input);

        // Add error icon with tooltip if error exists
        if (this.config.error) {
            const errorIcon = document.createElement('span');
            errorIcon.className = 'ui-input-error-icon';
            errorIcon.innerHTML = '⚠️';
            errorIcon.setAttribute('data-tooltip', this.config.error);
            errorIcon.title = this.config.error; // Fallback tooltip
            inputWrapper.appendChild(errorIcon);
        }

        group.appendChild(inputWrapper);

        return this.applyCommonAttributes(group);
    }
}

// ==================== Select Component ====================
class SelectComponent extends UIComponent {
    render() {
        const group = document.createElement('div');
        group.className = 'ui-select-group';

        if (this.config.label) {
            const label = document.createElement('label');
            label.textContent = this.config.label;
            if (this.config.required) {
                label.className = 'required';
            }
            if (this.config.name) {
                label.setAttribute('for', this.config.name);
            }
            group.appendChild(label);
        }

        const select = document.createElement('select');
        select.className = 'ui-select';
        select.required = this.config.required || false;
        select.disabled = this.config.disabled || false;

        if (this.config.name) {
            select.name = this.config.name;
            select.id = this.config.name;
        }

        // Add placeholder option if exists
        if (this.config.placeholder && !this.config.value) {
            const placeholderOption = document.createElement('option');
            placeholderOption.value = '';
            placeholderOption.textContent = this.config.placeholder;
            placeholderOption.disabled = true;
            placeholderOption.selected = true;
            select.appendChild(placeholderOption);
        }

        // Add options
        if (this.config.options) {
            // Support both formats:
            // 1. Object format: {value: label}
            // 2. Array format: [{value: 'key', label: 'text'}]

            if (Array.isArray(this.config.options)) {
                // Array format: [{value, label}]
                this.config.options.forEach(opt => {
                    const option = document.createElement('option');
                    option.value = opt.value;
                    option.textContent = opt.label;
                    if (this.config.value === opt.value) {
                        option.selected = true;
                    }
                    select.appendChild(option);
                });
            } else {
                // Object format: {value: label}
                for (const [value, label] of Object.entries(this.config.options)) {
                    const option = document.createElement('option');
                    option.value = value;
                    option.textContent = label;
                    if (this.config.value === value) {
                        option.selected = true;
                    }
                    select.appendChild(option);
                }
            }
        }

        group.appendChild(select);

        // Add change event listener if onChange action is defined
        if (this.config.on_change) {
            select.addEventListener('change', () => {
                this.handleChange(this.config.on_change, select.value);
            });
        }

        return this.applyCommonAttributes(group);
    }

    /**
     * Handle select change event
     * Sends the selected value to the backend
     *
     * @param {string} action - The action name (snake_case)
     * @param {string} value - The selected value
     */
    async handleChange(action, value) {
        console.log('Select changed:', action, value);

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            const componentId = this.config._id || parseInt(this.id);
            const usimStorage = localStorage.getItem('usim') || '';

            const response = await fetch('/api/ui-event', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-USIM-Storage': usimStorage,
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    component_id: componentId,
                    event: 'change',
                    action: action,
                    parameters: { value: value },
                }),
            });

            const result = await response.json();

            if (response.ok) {
                console.log('✅ Change event executed:', action, result);

                // Update UI with response
                if (result && Object.keys(result).length > 0) {
                    if (globalRenderer) {
                        globalRenderer.handleUIUpdate(result);
                    } else {
                        console.error('❌ Global renderer not initialized');
                    }
                }
            } else {
                console.error('❌ Change event failed:', response.status, result);
            }
        } catch (error) {
            console.error('❌ Error sending change event:', error);
        }
    }
}

// ==================== Checkbox Component ====================
class CheckboxComponent extends UIComponent {
    render() {
        const group = document.createElement('div');
        group.className = 'ui-checkbox-group';

        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.className = 'ui-checkbox';
        checkbox.checked = this.config.checked || false;
        checkbox.required = this.config.required || false;
        checkbox.disabled = this.config.disabled || false;

        if (this.config.name) {
            checkbox.name = this.config.name;
            checkbox.id = this.config.name;
        }

        if (this.config.value) {
            checkbox.value = this.config.value;
        }

        // Add change event listener if on_change action is defined
        if (this.config.on_change) {
            checkbox.addEventListener('change', async (e) => {
                // Prevent default to control the checked state from backend
                const newCheckedState = e.target.checked;

                // Revert immediately - backend will confirm the actual state
                e.target.checked = this.config.checked || false;

                // Send to backend with the attempted new state
                await this.handleChange(this.config.on_change, newCheckedState);
            });
        }

        group.appendChild(checkbox);

        if (this.config.label) {
            const label = document.createElement('label');
            label.className = 'ui-checkbox-label';
            label.textContent = this.config.label;
            if (this.config.required) {
                label.classList.add('required');
            }
            if (this.config.name) {
                label.setAttribute('for', this.config.name);
            }
            group.appendChild(label);
        }

        return this.applyCommonAttributes(group);
    }

    /**
     * Handle checkbox change event
     * Sends the attempted new state to backend for validation
     *
     * @param {string} action - The action name (snake_case)
     * @param {boolean} checked - The new checked state the user attempted
     */
    async handleChange(action, checked) {
        console.log('Checkbox change attempt:', action, checked);

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            const componentId = this.config._id || parseInt(this.id);

            const response = await fetch('/api/ui-event', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    component_id: componentId,
                    event: 'change',
                    action: action,
                    parameters: {
                        checked: checked,
                        name: this.config.name
                    }
                }),
            });

            const result = await response.json();

            if (response.ok) {
                console.log('✅ Checkbox change processed:', result);

                // Update UI with backend response (backend controls final state)
                if (result && Object.keys(result).length > 0) {
                    if (globalRenderer) {
                        globalRenderer.handleUIUpdate(result);
                    } else {
                        console.error('❌ Global renderer not initialized');
                    }
                }
            } else {
                console.error('❌ Checkbox change failed:', response.status, result);

                // Ensure checkbox reverts to original state on error
                const checkbox = document.querySelector(`[data-component-id="${componentId}"] input[type="checkbox"]`);
                if (checkbox) {
                    checkbox.checked = this.config.checked || false;
                }
            }
        } catch (error) {
            console.error('❌ Network error on checkbox change:', error);

            // Revert to original state on error
            const checkbox = document.querySelector(`[data-component-id="${this.config._id}"] input[type="checkbox"]`);
            if (checkbox) {
                checkbox.checked = this.config.checked || false;
            }
        }
    }
}

// ==================== Table Component ====================
class TableComponent extends UIComponent {
    render() {
        const tableWrapper = document.createElement('div');
        tableWrapper.className = 'ui-table-wrapper';

        // Apply alignment to wrapper
        if (this.config.align) {
            tableWrapper.classList.add(`align-${this.config.align}`);
        }

        // Add title if exists
        if (this.config.title) {
            const title = document.createElement('h3');
            title.className = 'ui-table-title';
            title.textContent = this.config.title;
            tableWrapper.appendChild(title);
        }

        // Create table element (this is where rows will be mounted)
        const table = document.createElement('table');
        table.className = 'ui-table';
        tableWrapper.appendChild(table);

        // Add pagination controls if enabled
        if (this.config.pagination) {
            const paginationDiv = this.createPaginationControls();
            tableWrapper.appendChild(paginationDiv);
        }

        // Store the wrapper as main element, but table for children
        this.tableElement = table;

        return this.applyCommonAttributes(tableWrapper);
    }

    createPaginationControls() {
        const paginationDiv = document.createElement('div');
        paginationDiv.className = 'ui-pagination';
        paginationDiv.setAttribute('data-component-id', this.id);

        // Read pagination from the new nested structure
        const pagination = this.config.pagination || {};
        const currentPage = pagination.current_page || 1;
        const perPage = pagination.per_page || 10;
        const totalItems = pagination.total_items || 0;
        const totalPages = pagination.total_pages || 1;
        const canNext = pagination.can_next !== undefined ? pagination.can_next : (currentPage < totalPages);
        const canPrev = pagination.can_prev !== undefined ? pagination.can_prev : (currentPage > 1);

        // Info text
        const start = (currentPage - 1) * perPage + 1;
        const end = Math.min(currentPage * perPage, totalItems);
        const infoDiv = document.createElement('div');
        infoDiv.className = 'ui-pagination-info';
        infoDiv.textContent = `Showing ${start}-${end} of ${totalItems} items`;
        paginationDiv.appendChild(infoDiv);

        // Controls
        const controlsDiv = document.createElement('div');
        controlsDiv.className = 'ui-pagination-controls';

        // Loading indicator (hidden by default)
        const loadingDiv = document.createElement('div');
        loadingDiv.className = 'ui-pagination-loading';
        loadingDiv.style.display = 'none';
        loadingDiv.style.marginLeft = '16px';
        loadingDiv.style.alignItems = 'center';
        loadingDiv.style.gap = '8px';
        loadingDiv.innerHTML = `
            <span class="spinner" style="
                display: inline-block;
                width: 16px;
                height: 16px;
                border: 2px solid #f3f3f3;
                border-top: 2px solid #3498db;
                border-radius: 50%;
                animation: spin 1s linear infinite;
            "></span>
        `;

        // Add CSS animation if not already present
        if (!document.querySelector('#pagination-spinner-style')) {
            const style = document.createElement('style');
            style.id = 'pagination-spinner-style';
            style.textContent = `
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            `;
            document.head.appendChild(style);
        }

        controlsDiv.appendChild(loadingDiv);
        loadingDiv.style.display = 'none';
        controlsDiv.paginationLoading = loadingDiv;

        // Previous button
        const prevBtn = document.createElement('button');
        prevBtn.className = 'ui-pagination-button';
        prevBtn.textContent = '« Previous';
        prevBtn.disabled = !canPrev;
        prevBtn.addEventListener('click', () => this.changePage(currentPage - 1, paginationDiv));
        controlsDiv.appendChild(prevBtn);

        // Page numbers
        const pages = this.getPageNumbers(currentPage, totalPages);
        pages.forEach(page => {
            if (page === '...') {
                const ellipsis = document.createElement('span');
                ellipsis.textContent = '...';
                ellipsis.style.padding = '0 8px';
                controlsDiv.appendChild(ellipsis);
            } else {
                const pageBtn = document.createElement('button');
                pageBtn.className = 'ui-pagination-button';
                if (page === currentPage) {
                    pageBtn.classList.add('active');
                }
                pageBtn.textContent = page;
                pageBtn.addEventListener('click', () => this.changePage(page, paginationDiv));
                controlsDiv.appendChild(pageBtn);
            }
        });

        // Next button
        const nextBtn = document.createElement('button');
        nextBtn.className = 'ui-pagination-button';
        nextBtn.textContent = 'Next »';
        nextBtn.disabled = !canNext;
        nextBtn.addEventListener('click', () => this.changePage(currentPage + 1, paginationDiv));
        controlsDiv.appendChild(nextBtn);

        paginationDiv.appendChild(controlsDiv);

        return paginationDiv;
    }

    getPageNumbers(current, total) {
        const pages = [];
        const maxVisible = 5;

        if (total <= maxVisible + 2) {
            for (let i = 1; i <= total; i++) {
                pages.push(i);
            }
        } else {
            pages.push(1);

            if (current > 3) {
                pages.push('...');
            }

            const start = Math.max(2, current - 1);
            const end = Math.min(total - 1, current + 1);

            for (let i = start; i <= end; i++) {
                pages.push(i);
            }

            if (current < total - 2) {
                pages.push('...');
            }

            pages.push(total);
        }

        return pages;
    }

    async changePage(page, paginationDiv = null) {
        // Get the pagination div if not provided
        if (!paginationDiv) {
            paginationDiv = this.element?.querySelector('.ui-pagination');
        }

        // Show loading state
        if (paginationDiv) {
            this.setLoadingState(paginationDiv, true);
        }

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            const componentId = this.config._id || parseInt(this.id);
            const usimStorage = localStorage.getItem('usim') || '';

            const response = await fetch('/api/ui-event', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-USIM-Storage': usimStorage,
                },
                body: JSON.stringify({
                    component_id: componentId,
                    event: 'action',
                    action: 'change_page',
                    parameters: { page }
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();

            if (result) {
                // Extract table data - it's returned with component ID as key
                const tableData = result[this.id];

                if (tableData && tableData.pagination) {
                    // Update this component's config with new pagination data
                    this.config.pagination = tableData.pagination;

                    // Now re-render pagination controls with updated config
                    const oldPagination = this.element.querySelector('.ui-pagination');
                    if (oldPagination) {
                        const newPagination = this.createPaginationControls();
                        oldPagination.replaceWith(newPagination);
                    }
                } else {
                    console.log('No pagination found in response');
                }

                // Apply all other UI updates from server
                if (globalRenderer) {
                    globalRenderer.handleUIUpdate(result);
                }
            }

        } catch (error) {
            console.error('Error changing page:', error);

            // Hide loading state on error
            if (paginationDiv) {
                this.setLoadingState(paginationDiv, false);
            }
        }
    }

    setLoadingState(paginationDiv, isLoading) {
        const controlsDiv = paginationDiv.querySelector('.ui-pagination-controls');
        if (!controlsDiv) return;

        const buttons = controlsDiv.querySelectorAll('button');
        const loadingDiv = controlsDiv.querySelector('.ui-pagination-loading');

        if (isLoading) {
            // Disable all buttons
            buttons.forEach(btn => btn.disabled = true);
            // Show loading indicator
            if (loadingDiv) {
                loadingDiv.style.display = 'flex';
            }
        } else {
            // Re-enable buttons based on pagination state
            const pagination = this.config.pagination || {};
            const currentPage = pagination.current_page || 1;
            const totalPages = pagination.total_pages || 1;
            const canNext = pagination.can_next !== undefined ? pagination.can_next : (currentPage < totalPages);
            const canPrev = pagination.can_prev !== undefined ? pagination.can_prev : (currentPage > 1);

            buttons.forEach((btn, index) => {
                const btnText = btn.textContent.trim();

                if (btnText === '« Previous') {
                    btn.disabled = !canPrev;
                } else if (btnText === 'Next »') {
                    btn.disabled = !canNext;
                } else if (!isNaN(btnText)) {
                    // Page number button
                    btn.disabled = false;
                }
            });

            // Hide loading indicator
            if (loadingDiv) {
                loadingDiv.style.display = 'none';
            }
        }
    }

    mount(parentElement) {
        super.mount(parentElement);
    }
}

// ==================== Table Header Row Component ====================
class TableHeaderRowComponent extends UIComponent {
    render() {
        const headerRow = document.createElement('tr');
        headerRow.className = 'ui-table-header-row';

        return this.applyCommonAttributes(headerRow);
    }
}

// ==================== Table Row Component ====================
class TableRowComponent extends UIComponent {
    render() {
        const row = document.createElement('tr');
        row.className = 'ui-table-row';

        if (this.config.selected) {
            row.classList.add('selected');
        }

        if (this.config.style) {
            row.classList.add(this.config.style);
        }

        // Apply minimum height if specified
        // Note: For <tr> elements, we need to set the height property
        // The CSS will inherit this to <td> elements
        if (this.config.min_height) {
            row.style.height = `${this.config.min_height}px`;
            row.setAttribute('data-min-height', this.config.min_height);
        }

        return this.applyCommonAttributes(row);
    }
}

// ==================== Table Cell Component ====================
class TableCellComponent extends UIComponent {
    render() {
        const cell = document.createElement('td');
        cell.className = 'ui-table-cell';

        // Cell types are mutually exclusive (priority order: button > url_image > text)

        if (this.config.button) {
            // Button cell - check first!
            const btn = document.createElement('button');
            btn.className = `ui-button ${this.config.button.style || 'default'}`;
            btn.textContent = this.config.button.label || 'Action';

            // Handle button click
            if (this.config.button.action) {
                btn.addEventListener('click', () => {
                    this.handleButtonClick(
                        this.config.button.action,
                        this.config.button.parameters || {}
                    );
                });
            }

            cell.appendChild(btn);
        }
        else if (this.config.url_image) {
            // Image cell
            const img = document.createElement('img');
            img.src = this.config.url_image;
            img.alt = this.config.alt || '';
            img.className = 'ui-table-cell-image';
            if (this.config.image_width) img.style.width = this.config.image_width;
            if (this.config.image_height) img.style.height = this.config.image_height;
            cell.appendChild(img);
        }
        else if (this.config.text !== undefined && this.config.text !== null) {
            // Simple text cell
            cell.textContent = this.config.text;
        }

        if (this.config.align) {
            cell.style.textAlign = this.config.align;
        }

        // Apply width constraints
        // For table-layout: fixed, we use width instead of min/max
        if (this.config.min_width || this.config.max_width) {
            // Use the minimum width as the actual width for fixed layout
            const targetWidth = this.config.min_width || this.config.max_width;
            cell.style.width = `${targetWidth}px`;

            // Still apply max-width to prevent overflow
            if (this.config.max_width) {
                cell.style.maxWidth = `${this.config.max_width}px`;
            }
        }

        return this.applyCommonAttributes(cell);
    }

    /**
     * Handle button click in cell
     */
    async handleButtonClick(action, parameters) {
        console.log('Table cell button clicked:', action, parameters);

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            const componentId = this.config._id || parseInt(this.id);
            const usimStorage = localStorage.getItem('usim') || '';

            const response = await fetch('/api/ui-event', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-USIM-Storage': usimStorage,
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    component_id: componentId,
                    event: 'click',
                    action: action,
                    parameters: parameters,
                }),
            });

            const result = await response.json();

            if (response.ok) {
                console.log('✅ Cell button action executed:', action, result);

                if (result && Object.keys(result).length > 0) {
                    if (globalRenderer) {
                        globalRenderer.handleUIUpdate(result);
                    }
                }
            } else {
                console.error('❌ Cell button action failed:', response.status, result);
            }
        } catch (error) {
            console.error('❌ Error executing cell button action:', error);
        }
    }
}

// ==================== Table Header Cell Component ====================
class TableHeaderCellComponent extends UIComponent {
    render() {
        const cell = document.createElement('th');
        cell.className = 'ui-table-header-cell';

        if (this.config.text !== undefined) {
            cell.textContent = this.config.text;
        }

        if (this.config.align) {
            cell.style.textAlign = this.config.align;
        }

        // Apply width constraints
        // For table-layout: fixed, we use width instead of min/max
        if (this.config.min_width || this.config.max_width) {
            // Use the minimum width as the actual width for fixed layout
            const targetWidth = this.config.min_width || this.config.max_width;
            cell.style.width = `${targetWidth}px`;

            // Still apply max-width to prevent overflow
            if (this.config.max_width) {
                cell.style.maxWidth = `${this.config.max_width}px`;
            }
        }

        return this.applyCommonAttributes(cell);
    }
}

// ==================== Card Component ====================
class CardComponent extends UIComponent {
    render() {
        const card = document.createElement('div');
        card.className = this.getCardClasses();

        // Handle clickable cards
        if (this.config.clickable) {
            if (this.config.url) {
                // Create as link
                const link = document.createElement('a');
                link.href = this.config.url;
                link.target = this.config.target || '_self';
                link.className = card.className;
                link.style.textDecoration = 'none';
                link.style.color = 'inherit';
                card = link;
            } else if (this.config.action) {
                // Add click handler
                card.style.cursor = 'pointer';
                card.addEventListener('click', () => {
                    this.sendEventToBackend('click', this.config.action, this.config.parameters || {});
                });
            }
        }

        // Badge
        if (this.config.badge) {
            const badge = document.createElement('div');
            badge.className = `ui-card-badge ${this.config.badge_position || 'top-right'}`;
            badge.textContent = this.config.badge;
            card.appendChild(badge);
        }

        // Image
        if (this.config.image && this.config.image_position !== 'background') {
            const imageContainer = this.createImageElement();
            if (this.config.image_position === 'top' || !this.config.image_position) {
                card.appendChild(imageContainer);
            }
        }

        // Card content wrapper
        const content = document.createElement('div');
        content.className = 'ui-card-content';

        // Header
        if (this.config.show_header !== false && (this.config.title || this.config.subtitle || this.config.header)) {
            const header = this.createHeader();
            content.appendChild(header);
        }

        // Body
        const body = this.createBody();
        if (body.children.length > 0 || body.textContent.trim()) {
            content.appendChild(body);
        }

        // Footer/Actions
        if (this.config.show_footer !== false && (this.config.actions?.length > 0 || this.config.footer)) {
            const footer = this.createFooter();
            content.appendChild(footer);
        }

        card.appendChild(content);

        // Image at bottom
        if (this.config.image && this.config.image_position === 'bottom') {
            const imageContainer = this.createImageElement();
            card.appendChild(imageContainer);
        }

        // Background image
        if (this.config.image && this.config.image_position === 'background') {
            card.style.backgroundImage = `url(${this.config.image})`;
            card.style.backgroundSize = this.config.image_fit || 'cover';
            card.style.backgroundPosition = 'center';
            card.style.backgroundRepeat = 'no-repeat';
        }

        return this.applyCommonAttributes(card);
    }

    getCardClasses() {
        let classes = 'ui-card';

        if (this.config.style) classes += ` ui-card-${this.config.style}`;
        if (this.config.variant) classes += ` ui-card-${this.config.variant}`;
        if (this.config.size) classes += ` ui-card-${this.config.size}`;
        if (this.config.elevation) classes += ` ui-card-elevation-${this.config.elevation}`;
        if (this.config.theme) classes += ` ui-card-theme-${this.config.theme}`;
        if (this.config.orientation) classes += ` ui-card-${this.config.orientation}`;
        if (this.config.hover_effect !== false) classes += ` ui-card-hover`;
        if (this.config.clickable) classes += ` ui-card-clickable`;

        return classes;
    }

    createImageElement() {
        const imageContainer = document.createElement('div');
        imageContainer.className = 'ui-card-image';

        const img = document.createElement('img');
        img.src = this.config.image;
        img.alt = this.config.image_alt || this.config.title || '';
        img.style.objectFit = this.config.image_fit || 'cover';

        imageContainer.appendChild(img);
        return imageContainer;
    }

    createHeader() {
        const header = document.createElement('div');
        header.className = 'ui-card-header';

        if (this.config.header) {
            header.innerHTML = this.config.header;
        } else {
            if (this.config.title) {
                const title = document.createElement('h3');
                title.className = 'ui-card-title';
                title.textContent = this.config.title;
                header.appendChild(title);
            }

            if (this.config.subtitle) {
                const subtitle = document.createElement('p');
                subtitle.className = 'ui-card-subtitle';
                subtitle.textContent = this.config.subtitle;
                header.appendChild(subtitle);
            }
        }

        return header;
    }

    createBody() {
        const body = document.createElement('div');
        body.className = 'ui-card-body';

        if (this.config.content) {
            body.innerHTML = this.config.content;
        } else if (this.config.description) {
            const description = document.createElement('p');
            description.className = 'ui-card-description';
            description.textContent = this.config.description;
            body.appendChild(description);
        }

        return body;
    }

    createFooter() {
        const footer = document.createElement('div');
        footer.className = 'ui-card-footer';

        if (this.config.footer) {
            footer.innerHTML = this.config.footer;
        } else if (this.config.actions?.length > 0) {
            const actionsContainer = document.createElement('div');
            actionsContainer.className = 'ui-card-actions';

            this.config.actions.forEach(actionConfig => {
                const button = document.createElement('button');
                button.className = `ui-button ${actionConfig.style || 'primary'}`;
                button.textContent = actionConfig.label;

                button.addEventListener('click', (e) => {
                    e.stopPropagation(); // Prevent card click
                    this.sendEventToBackend('click', actionConfig.action, actionConfig.parameters || {});
                });

                actionsContainer.appendChild(button);
            });

            footer.appendChild(actionsContainer);
        }

        return footer;
    }
}

// ==================== Component Factory ====================
class ComponentFactory {
    static create(id, config) {
        switch (config.type) {
            case 'container':
                return new ContainerComponent(id, config);
            case 'button':
                return new ButtonComponent(id, config);
            case 'label':
                return new LabelComponent(id, config);
            case 'input':
                return new InputComponent(id, config);
            case 'select':
                return new SelectComponent(id, config);
            case 'checkbox':
                return new CheckboxComponent(id, config);
            case 'table':
                return new TableComponent(id, config);
            case 'tableheaderrow':
                return new TableHeaderRowComponent(id, config);
            case 'tablerow':
                return new TableRowComponent(id, config);
            case 'tablecell':
                return new TableCellComponent(id, config);
            case 'tableheadercell':
                return new TableHeaderCellComponent(id, config);
            case 'menudropdown':
                return new MenuDropdownComponent(id, config);
            case 'card':
                return new CardComponent(id, config);
            case 'storage':
                return new StorageComponent(id, config);
            default:
                console.warn(`Unknown component type: ${config.type}`);
                return null;
        }
    }
}

// ==================== UI Renderer ====================
class UIRenderer {
    constructor(data) {
        this.data = data;
        this.components = new Map();
    }

    render() {
        // console.log('🎨 Rendering UI with data:', this.data);

        // Step 1: Build a map of internal ID -> JSON key
        // Each component now has _id in its config
        const internalIdToKey = new Map();
        const componentIds = Object.keys(this.data);

        // console.log('📋 Component IDs from JSON keys:', componentIds);

        for (const key of componentIds) {
            const config = this.data[key];

            // Skip special keys that are not UI components
            if (key === 'storage' || key === 'action') {
                continue;
            }

            if (config._id !== undefined) {
                internalIdToKey.set(config._id, key);
                // console.log(`  🔗 Mapped _id ${config._id} -> JSON key "${key}"`);
            }
        }

        // Step 2: Create all component instances
        for (const id of componentIds) {
            // Skip special keys that are not UI components
            if (id === 'storage' || id === 'action') {
                continue;
            }

            const config = this.data[id];
            // console.log(`  🏗️ Creating component type="${config.type}" id="${id}"`, config);
            const component = ComponentFactory.create(id, config);
            if (component) {
                this.components.set(id, component);
                // console.log(`    ✅ Created successfully`);
            } else {
                console.log(`    ❌ Failed to create`);
            }
        }

        // console.log(`✅ Created ${this.components.size} components`);

        // Step 3: Group components by parent and sort by _order
        const childrenByParent = new Map();
        for (const id of componentIds) {
            // Skip special keys that are not UI components
            if (id === 'storage' || id === 'action') {
                continue;
            }

            const component = this.components.get(id);
            if (!component) continue;

            const parentId = component.config.parent;
            let parentKey;

            if (typeof parentId === 'string') {
                // Parent is a DOM element
                parentKey = parentId;
            } else if (typeof parentId === 'number') {
                // Parent is a component - find its key using _id
                parentKey = internalIdToKey.get(parentId);
                if (!parentKey) {
                    console.error(`Parent component with internal ID ${parentId} not found in JSON`);
                    continue;
                }
            }

            if (!childrenByParent.has(parentKey)) {
                childrenByParent.set(parentKey, []);
            }
            childrenByParent.get(parentKey).push({
                id: id,
                order: component.config._order ?? 999999
            });
        }

        // Sort children within each parent by their _order (or column for table cells, or row for table rows)
        for (const [parent, children] of childrenByParent.entries()) {
            children.sort((a, b) => {
                const compA = this.components.get(a.id);
                const compB = this.components.get(b.id);

                // If both are table rows, sort by row index
                if (compA && compB &&
                    compA.config.type === 'tablerow' &&
                    compB.config.type === 'tablerow') {
                    const rowA = compA.config.row ?? 999999;
                    const rowB = compB.config.row ?? 999999;
                    return rowA - rowB;
                }

                // If both are table cells or header cells, sort by column
                if (compA && compB &&
                    (compA.config.type === 'tablecell' || compA.config.type === 'tableheadercell') &&
                    (compB.config.type === 'tablecell' || compB.config.type === 'tableheadercell')) {
                    const colA = compA.config.column ?? 999999;
                    const colB = compB.config.column ?? 999999;
                    return colA - colB;
                }

                // Otherwise sort by _order
                return a.order - b.order;
            });
        }

        // Step 4: Mount components in hierarchical order
        const mounted = new Set();
        const maxIterations = this.components.size * 2;
        let iterations = 0;

        console.log('🚀 Starting component mounting...');

        while (mounted.size < this.components.size && iterations < maxIterations) {
            iterations++;

            // For each parent, mount its children in order
            for (const [parentKey, children] of childrenByParent.entries()) {
                for (const childInfo of children) {
                    const id = childInfo.id;
                    const component = this.components.get(id);

                    if (!component || mounted.has(id)) continue;

                    const parentId = component.config.parent;

                    // console.log(`  📍 Attempting to mount "${id}" (type: ${component.config.type}), parent: ${parentId}`);

                    if (typeof parentId === 'string') {
                        // Parent is a DOM element (always available)
                        const parentElement = document.getElementById(parentId);
                        if (parentElement) {
                            component.mount(parentElement);
                            mounted.add(id);
                            console.log(`    ✅ Mounted to DOM element "${parentId}"`);
                        } else {
                            console.error(`    ❌ Parent element not found: ${parentId}`);
                            mounted.add(id);
                        }
                    } else if (typeof parentId === 'number') {
                        // Parent is a component - find its key using _id
                        const parentComponentKey = internalIdToKey.get(parentId);
                        const parentComponent = this.components.get(parentComponentKey);

                        if (!parentComponent) {
                            console.error(`    ❌ Parent component not found for ID: ${parentId}`);
                            mounted.add(id);
                            continue;
                        }

                        // Wait for parent to be mounted first
                        if (mounted.has(parentComponentKey)) {
                            // Determine mount target
                            let mountTarget = parentComponent.element;

                            // Special case: if parent is a table, mount rows inside <table> element
                            if (parentComponent.tableElement) {
                                mountTarget = parentComponent.tableElement;

                                // Special case: if child is a container inside a table, it's probably the rows container
                                // Don't create a DOM element for it, just mark it as mounted and let its children mount to the table
                                if (component.config.type === 'container') {
                                    // Make this container "transparent" - its children will mount directly to the table
                                    component.element = mountTarget; // Point to the table
                                    mounted.add(id);
                                    console.log(`    ✅ Transparent container mounted (children will use parent table)`);
                                    continue;
                                }
                            }

                            component.mount(mountTarget);
                            mounted.add(id);
                            // console.log(`    ✅ Mounted to component "${parentComponentKey}" (_id: ${parentId})`);
                        } else {
                            // console.log(`    ⏳ Waiting for parent "${parentComponentKey}" to be mounted first`);
                        }
                    }
                }
            }
        }

        if (mounted.size < this.components.size) {
            console.warn(`⚠️ Could not mount ${this.components.size - mounted.size} components (circular dependency or missing parents)`);
        }

        console.log(`✅ UI rendering complete (${mounted.size}/${this.components.size} mounted)`);
    }

    /**
     * Handle storage updates - store variables in localStorage
     *
     * @param {object} storageData - Storage variables object
     */
    handleStorageUpdate(storageData) {

        Object.keys(storageData).forEach(key => {
            const value = storageData[key];

            // Store the value in localStorage
            // If it's an object/array, stringify it
            if (typeof value === 'object' && value !== null) {
                localStorage.setItem(key, JSON.stringify(value));
            } else {
                localStorage.setItem(key, String(value));
            }
        });
    }

    /**
     * Show toast notification
     *
     * @param {object} toastConfig - Toast configuration
     */
    showToast(toastConfig) {
        const {
            message,
            type = 'info',
            duration = 3000,
            open_effect = 'fade',
            show_effect = 'bounce',
            close_effect = 'fade',
            position = 'top-right'
        } = toastConfig;

        // Create toast container if it doesn't exist or update position
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            document.body.appendChild(toastContainer);
        }
        toastContainer.className = `toast-container toast-position-${position}`;

        // Create toast element with position-aware classes
        const toast = document.createElement('div');
        toast.className = `toast toast-${type} toast-open-${open_effect} toast-show-${show_effect} toast-position-${position}`;

        // Toast icon based on type
        const icons = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        };
        const icon = icons[type] || icons.info;

        toast.innerHTML = `
            <span class="toast-icon">${icon}</span>
            <span class="toast-message">${message}</span>
            <button class="toast-close" aria-label="Close">&times;</button>
        `;

        // Add to container
        toastContainer.appendChild(toast);

        // Trigger opening animation
        requestAnimationFrame(() => {
            toast.classList.add('toast-open');
        });

        // Close button handler
        const closeBtn = toast.querySelector('.toast-close');
        const closeToast = () => {
            toast.classList.remove('toast-open');
            toast.classList.add(`toast-close-${close_effect}`);

            setTimeout(() => {
                toast.remove();

                // Remove container if empty
                if (toastContainer.children.length === 0) {
                    toastContainer.remove();
                }
            }, 300);
        };

        closeBtn.addEventListener('click', closeToast);

        // Auto close after duration
        if (duration > 0) {
            setTimeout(closeToast, duration);
        }
    }

    /**
     * Handle UI updates from backend
     *
     * @param {object} uiUpdate - UI update object (same structure as initial render)
     */
    handleUIUpdate(uiUpdate) {
        // console.log('📦 Processing UI updates:', uiUpdate);

        // Handle storage updates if present
        if (uiUpdate.storage) {
            this.handleStorageUpdate(uiUpdate.storage);
        }

        // Handle toast notifications if present (but only if no redirect)
        if (uiUpdate.toast && !uiUpdate.redirect) {
            this.showToast(uiUpdate.toast);
        }

        // Handle redirects if present
        if (uiUpdate.redirect) {
            // If there's a toast, save it to show after redirect
            if (uiUpdate.toast) {
                sessionStorage.setItem('pendingToast', JSON.stringify(uiUpdate.toast));
            }
            window.location.href = uiUpdate.redirect;
            return; // Stop processing after redirect
        }

        // Check if there are components with parent='modal' - if so, open modal
        let hasModalComponents = false;
        for (const [key, component] of Object.entries(uiUpdate)) {
            if (component.parent === 'modal') {
                hasModalComponents = true;
                break;
            }
        }

        if (hasModalComponents) {
            // Open modal with these components
            openModal(uiUpdate);
            return; // Don't process as regular updates
        }

        // Check for special actions
        if (uiUpdate.action) {
            switch (uiUpdate.action) {
                case 'show_modal':
                    if (uiUpdate.modal) {
                        openModal(uiUpdate.modal);
                    }
                    return; // Don't process as regular updates

                case 'close_modal':
                    closeModal();
                    // Process UI updates directly from root object
                    for (const [jsonKey, changes] of Object.entries(uiUpdate)) {
                        // Skip special keys
                        if (jsonKey === 'action' || jsonKey === 'modal' || jsonKey === 'storage') {
                            continue;
                        }

                        const componentId = changes._id;
                        if (!componentId) continue;

                        const element = document.querySelector(`[data-component-id="${componentId}"]`);

                        if (element) {
                            // console.log(`✏️ Updating component ${componentId}`, changes);
                            this.updateComponent(element, changes);
                        } else {
                            // console.log(`➕ Creating new component ${componentId}`, changes);
                            this.addComponent(jsonKey, changes);
                        }
                    }
                    return; // Don't continue processing
            }
        }

        // Handle UI updates (for non-modal actions)
        for (const [jsonKey, changes] of Object.entries(uiUpdate)) {
            // Skip special keys
            if (jsonKey === 'action' || jsonKey === 'modal' || jsonKey === 'storage') {
                continue;
            }

            const componentId = changes._id;
            if (!componentId) continue;

            const element = document.querySelector(`[data-component-id="${componentId}"]`);

            if (element) {
                // console.log(`✏️ Updating component ${componentId}`, changes);
                this.updateComponent(element, changes);
            } else {
                // console.log(`➕ Creating new component ${componentId}`, changes);
                this.addComponent(jsonKey, changes);
            }
        }
    }

    /**
     * Update existing component in DOM
     *
     * @param {HTMLElement} element - DOM element to update
     * @param {object} changes - Properties to update
     */
    updateComponent(element, changes) {
        try {
            // Button in table cell - needs special handling to update the button inside the cell
            if (changes.button !== undefined && element.tagName === 'TD') {
                // Clear the cell and re-render with new button
                element.innerHTML = '';

                // si el botón es null, no hacemos nada más
                if (changes.button === null) {
                    return;
                }

                const btn = document.createElement('button');
                btn.className = `ui-button ${changes.button.style || 'default'}`;
                btn.textContent = changes.button.label || 'Action';

                // Handle button click
                if (changes.button.action) {
                    btn.addEventListener('click', async () => {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                        const componentId = element.getAttribute('data-component-id');
                        const usimStorage = localStorage.getItem('usim') || '';

                        try {
                            const response = await fetch('/api/ui-event', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-USIM-Storage': usimStorage,
                                },
                                credentials: 'same-origin',
                                body: JSON.stringify({
                                    component_id: parseInt(componentId),
                                    event: 'action',
                                    action: changes.button.action,
                                    parameters: changes.button.parameters || {},
                                }),
                            });

                            const result = await response.json();

                            if (response.ok && result && globalRenderer) {
                                globalRenderer.handleUIUpdate(result);
                            }
                        } catch (error) {
                            console.error('Button click error:', error);
                        }
                    });
                }

                element.appendChild(btn);
                return;
            }

            // Text (labels)
            if (changes.text !== undefined) {
                const text = changes.text;
                if (text.includes('\n')) {
                    // Support line breaks
                    element.style.whiteSpace = 'pre-line';
                    element.textContent = text;
                } else {
                    element.textContent = text;
                }
            }

            // Label (buttons)
            if (changes.label !== undefined) {
                element.textContent = changes.label;
            }

            // Trigger (menudropdown)
            if (changes.trigger !== undefined) {
                const triggerButton = element.querySelector('.menu-dropdown-trigger');
                if (triggerButton) {
                    const triggerConfig = changes.trigger;
                    const triggerLabel = triggerConfig.label || '☰ Menu';
                    const triggerIcon = triggerConfig.icon;
                    const triggerStyle = triggerConfig.style || 'default';

                    // Update trigger style classes
                    triggerButton.className = 'menu-dropdown-trigger';
                    triggerButton.className += ` menu-trigger-${triggerStyle}`;

                    // Build trigger content
                    let triggerContent = '';
                    if (triggerIcon) {
                        triggerContent += `<span class="trigger-icon">${triggerIcon}</span>`;
                    }
                    triggerContent += `<span class="trigger-label">${triggerLabel}</span>`;

                    triggerButton.innerHTML = triggerContent;
                }
            }

            // Items (menudropdown) - DEPRECATED: items are immutable in frontend
            // Only permissions array should be updated from backend
            if (changes.items !== undefined) {
                console.warn('⚠️ Updating items[] is deprecated. Use permissions[] instead.');
            }

            // Permissions (menudropdown) - Re-render menu items with new permissions
            if (changes.permissions !== undefined) {
                const menuContainer = element;
                const component = globalRenderer?.components?.get(String(changes._id));

                if (component && component.config) {
                    // Update component config
                    component.config.permissions = changes.permissions;

                    // Re-render menu content
                    const menuContent = element.querySelector('.menu-dropdown-content');

                    if (menuContent) {
                        // Clear existing content
                        menuContent.innerHTML = '';

                        // Re-render all items with new permissions
                        const items = component.config.items || [];

                        items.forEach(item => {
                            const itemElement = component.renderMenuItem(item);
                            menuContent.appendChild(itemElement);
                        });

                        // Check if all items are hidden
                        const permissions = changes.permissions;
                        const hasVisibleItems = items.some(item => {
                            if (item.type === 'separator') {
                                return false;
                            }
                            const isVisible = component.isItemVisible(item, permissions);
                            if (item.submenu && item.submenu.length > 0) {
                                return isVisible && component.hasVisibleChildren(item.submenu, permissions);
                            }
                            return isVisible;
                });                        // Hide/show entire menu
                        menuContainer.style.display = hasVisibleItems ? '' : 'none';

                        // Close menu if it was open
                        menuContent.classList.remove('show');
                        const trigger = element.querySelector('.menu-dropdown-trigger');
                        if (trigger) {
                            trigger.classList.remove('active');
                        }
                    }
                }
            }

            // Style/classes
            if (changes.style !== undefined) {
                element.classList.remove('default', 'primary', 'secondary', 'success', 'warning', 'danger', 'info');
                element.classList.add(changes.style);
            }

            // Visibility
            if (changes.visible !== undefined) {
                element.style.display = changes.visible ? '' : 'none';
            }

            // Enabled/disabled state
            if (changes.enabled !== undefined) {
                if (element.tagName === 'BUTTON' || element.tagName === 'INPUT') {
                    element.disabled = !changes.enabled;
                }
            }

            // Disabled state (for selects and other elements)
            if (changes.disabled !== undefined) {
                const targetElement = element.querySelector('select, input, textarea, button') || element;
                targetElement.disabled = changes.disabled;
            }

            // Value (inputs)
            if (changes.value !== undefined) {
                if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
                    element.value = changes.value;
                } else {
                    const input = element.querySelector('input, textarea');
                    if (input) input.value = changes.value;
                }
            }

            // Error state (inputs)
            if (changes.error !== undefined) {
                const inputWrapper = element.querySelector('.ui-input-wrapper');
                const input = element.querySelector('input');

                if (inputWrapper && input) {
                    // Remove existing error icon if any
                    const existingIcon = inputWrapper.querySelector('.ui-input-error-icon');
                    if (existingIcon) {
                        existingIcon.remove();
                    }

                    if (changes.error) {
                        // Add error state
                        input.classList.add('ui-input-error');

                        // Create and add error icon
                        const errorIcon = document.createElement('span');
                        errorIcon.className = 'ui-input-error-icon';
                        errorIcon.innerHTML = '⚠️';
                        errorIcon.setAttribute('data-tooltip', changes.error);
                        errorIcon.title = changes.error;
                        inputWrapper.appendChild(errorIcon);
                    } else {
                        // Remove error state
                        input.classList.remove('ui-input-error');
                    }
                }
            }            // Options (selects)
            if (changes.options !== undefined) {
                const select = element.querySelector('select') || (element.tagName === 'SELECT' ? element : null);
                if (select) {
                    // Clear existing options (except placeholder if exists)
                    const placeholder = select.querySelector('option[disabled][value=""]');
                    select.innerHTML = '';

                    // Re-add placeholder if it existed
                    if (placeholder) {
                        select.appendChild(placeholder);
                    }

                    // Add new options (support both array and object formats)
                    if (Array.isArray(changes.options)) {
                        // Array format: [{value, label}]
                        changes.options.forEach(opt => {
                            const option = document.createElement('option');
                            option.value = opt.value;
                            option.textContent = opt.label;
                            select.appendChild(option);
                        });
                    } else {
                        // Object format: {value: label}
                        for (const [value, label] of Object.entries(changes.options)) {
                            const option = document.createElement('option');
                            option.value = value;
                            option.textContent = label;
                            select.appendChild(option);
                        }
                    }
                }
            }

            // Placeholder (selects)
            if (changes.placeholder !== undefined) {
                const select = element.querySelector('select') || (element.tagName === 'SELECT' ? element : null);
                if (select) {
                    let placeholder = select.querySelector('option[disabled][value=""]');
                    if (placeholder) {
                        placeholder.textContent = changes.placeholder;
                    } else {
                        // Create placeholder if it doesn't exist
                        placeholder = document.createElement('option');
                        placeholder.value = '';
                        placeholder.textContent = changes.placeholder;
                        placeholder.disabled = true;
                        placeholder.selected = true;
                        select.insertBefore(placeholder, select.firstChild);
                    }
                }
            }

            // Checked (checkboxes)
            if (changes.checked !== undefined) {
                if (element.type === 'checkbox') {
                    element.checked = changes.checked;
                } else {
                    const checkbox = element.querySelector('input[type="checkbox"]');
                    if (checkbox) checkbox.checked = changes.checked;
                }
            }

            // console.log(`✅ Component ${changes._id} updated successfully`);
        } catch (error) {
            console.error(`❌ Error updating component ${changes._id}:`, error);
        }
    }

    /**
     * Add new component to DOM
     *
     * @param {string} jsonKey - JSON key of the component
     * @param {object} config - Component configuration
     */
    addComponent(jsonKey, config) {
        try {
            const component = ComponentFactory.create(jsonKey, config);

            if (!component) {
                console.error(`❌ ComponentFactory returned null for type: ${config.type}`);
                return;
            }

            const element = component.render();

            // Find parent and append
            const parentElement = document.querySelector(`[data-component-id="${config.parent}"]`)
                || document.getElementById(config.parent);

            if (parentElement) {
                parentElement.appendChild(element);
                console.log(`➕ Component ${config._id} added to parent ${config.parent}`);
            } else {
                console.error(`❌ Parent ${config.parent} not found for component ${config._id}`);
            }
        } catch (error) {
            console.error(`❌ Error adding component:`, error);
        }
    }
}

// Global renderer instance
let globalRenderer = null;

// ==================== Main Application ====================
async function loadDemoUI(demoName = null) {
    try {
        // Use demo name from window global (set by Laravel) or parameter
        const demo = demoName || window.DEMO_NAME || 'button-demo';
        const resetQuery = window.RESET_DEMO ? '?reset=true' : '';

        const usimStorage = localStorage.getItem('usim') || '';
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        console.log(`Fetching UI data from /api/${demo}${resetQuery}...`);

        const response = await fetch(`/api/${demo}${resetQuery}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'X-USIM-Storage': usimStorage,
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const uiData = await response.json();

        // Create and store global renderer
        globalRenderer = new UIRenderer(uiData);
        globalRenderer.render();

        // If reset flag was used, clear it after loading
        if (window.RESET_DEMO) {
            window.history.replaceState({}, document.title, window.location.pathname);
            window.RESET_DEMO = false;
        }

        console.log('✅ Demo UI loaded successfully');

    } catch (error) {
        console.error('Error loading demo UI:', error);
        document.getElementById('main').innerHTML = `
            <div style="padding: 20px; color: red; background: #fee; border: 1px solid #fcc; border-radius: 6px;">
                <h2>❌ Error loading UI components</h2>
                <p><strong>Message:</strong> ${error.message}</p>
                <p><strong>Check the console</strong> for more details.</p>
            </div>
        `;
    }
}

// Listen for UI actions
window.addEventListener('ui-action', (event) => {
    console.log('UI Action triggered:', event.detail);
    // Here you can handle actions globally
    // e.g., send to backend, update state, etc.
});

// ==================== Modal Functions ====================

/**
 * Open a modal with UI content
 * @param {Object} uiData - UI configuration for modal content (should have parent='modal')
 */
function openModal(uiData) {
    const overlay = document.getElementById('modal-overlay');
    const modalContainer = document.getElementById('modal');

    if (!overlay || !modalContainer) {
        console.error('Modal containers not found in DOM');
        return;
    }

    // Clear previous content and any existing timers
    modalContainer.innerHTML = '';
    if (window.modalTimeoutId) {
        clearInterval(window.modalTimeoutId);
        window.modalTimeoutId = null;
    }

    // Add ui-container class to modal container so collectContextValues() can find inputs
    // This ensures that buttons inside modals can collect form values correctly
    if (!modalContainer.classList.contains('ui-container')) {
        modalContainer.classList.add('ui-container');
    }

    // Render modal content using UIRenderer
    // The uiData should already have parent='modal' from the backend
    const modalRenderer = new UIRenderer(uiData);
    modalRenderer.render();

    // Check if this is a timeout dialog
    // Look for the container with parent='modal' that has timeout metadata
    let timeoutConfig = null;

    for (const [key, component] of Object.entries(uiData)) {
        if (component.parent === 'modal' && component._timeout && component._timeout_ms) {
            timeoutConfig = component;
            break;
        }
    }

    if (timeoutConfig) {
        const timeoutMs = timeoutConfig._timeout_ms;
        const showCountdown = timeoutConfig._show_countdown ?? true;
        const timeoutAction = timeoutConfig._timeout_action || 'close_modal';
        const callerServiceId = timeoutConfig._caller_service_id;
        const timeUnitLabel = timeoutConfig._time_unit_label || 'segundos';

        if (showCountdown) {
            startModalCountdown(timeoutMs, timeoutConfig._timeout, timeoutConfig._time_unit, timeUnitLabel, timeoutAction, callerServiceId);
        } else {
            // Just set the timeout without showing countdown
            window.modalTimeoutId = setTimeout(() => {
                executeTimeoutAction(timeoutAction, callerServiceId);
            }, timeoutMs);
        }
    }

    // Show modal
    overlay.classList.remove('hidden');
    document.body.classList.add('modal-open');

    console.log('✅ Modal opened');
}

/**
 * Close the modal
 */
function closeModal() {
    const overlay = document.getElementById('modal-overlay');
    const modalContainer = document.getElementById('modal');

    if (!overlay || !modalContainer) {
        return;
    }

    // Clear any active timeout
    if (window.modalTimeoutId) {
        clearInterval(window.modalTimeoutId);
        window.modalTimeoutId = null;
    }

    // Clear content
    modalContainer.innerHTML = '';

    // Hide modal
    overlay.classList.add('hidden');
    document.body.classList.remove('modal-open');

    console.log('✅ Modal closed');
}

/**
 * Start countdown timer for modal
 */
function startModalCountdown(totalMs, initialValue, timeUnit, timeUnitLabel, timeoutAction, callerServiceId) {

    // Wait a bit for the DOM to be fully rendered
    setTimeout(() => {
        // Try to find countdown label by ID (name property creates id attribute)
        let countdownLabel = document.getElementById('countdown');

        if (!countdownLabel) {
            // Fallback: Try by querySelector
            countdownLabel = document.querySelector('#modal .ui-label.h2');
            console.log('⚠️ Countdown not found by ID, using fallback selector');
        }

        if (!countdownLabel) {
            console.error('❌ Countdown label not found!');
            console.log('📋 Modal HTML:', document.querySelector('#modal')?.innerHTML || 'Modal not found');
            return;
        }

        const startTime = Date.now();
        const endTime = startTime + totalMs;

        let updateCount = 0;

        // Update countdown every 100ms for smooth updates
        window.modalTimeoutId = setInterval(() => {
            const remaining = endTime - Date.now();
            updateCount++;

            if (remaining <= 0) {
                clearInterval(window.modalTimeoutId);
                window.modalTimeoutId = null;
                // console.log(`⏱️ Timeout completed after ${updateCount} updates`);
                // console.log('🎬 Executing action:', timeoutAction);
                executeTimeoutAction(timeoutAction, callerServiceId);
            } else {
                // Calculate remaining time in the original unit
                const remainingValue = Math.ceil(getRemainingValue(remaining, timeUnit));
                const label = remainingValue === 1 ? getSingularLabel(timeUnit) : timeUnitLabel;
                const newText = `${remainingValue} ${label}`;

                // Update the label
                countdownLabel.textContent = newText;
            }
        }, 100);

        console.log('✅ Countdown timer started successfully!');
    }, 150); // Wait 150ms for DOM rendering
}

/**
 * Get remaining value in the specified time unit
 */
function getRemainingValue(remainingMs, timeUnit) {
    switch (timeUnit) {
        case 'seconds': return remainingMs / 1000;
        case 'minutes': return remainingMs / (60 * 1000);
        case 'hours': return remainingMs / (60 * 60 * 1000);
        case 'days': return remainingMs / (24 * 60 * 60 * 1000);
        default: return remainingMs / 1000;
    }
}

/**
 * Get singular label for time unit
 */
function getSingularLabel(timeUnit) {
    switch (timeUnit) {
        case 'seconds': return 'segundo';
        case 'minutes': return 'minuto';
        case 'hours': return 'hora';
        case 'days': return 'día';
        default: return 'segundo';
    }
}

/**
 * Execute action when timeout completes
 */
async function executeTimeoutAction(action, callerServiceId) {
    if (action === 'close_modal') {
        closeModal();
    } else {
        // Execute custom action via backend
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            const usimStorage = localStorage.getItem('usim') || '';

            const response = await fetch('/api/ui-event', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-USIM-Storage': usimStorage,
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    component_id: callerServiceId,
                    event: 'timeout',
                    action: action,
                    parameters: {},
                }),
            });

            const result = await response.json();

            if (response.ok && globalRenderer) {
                globalRenderer.handleUIUpdate(result);
            }
        } catch (error) {
            console.error('❌ Error executing timeout action:', error);
            closeModal();
        }
    }
}

// Close modal when clicking on overlay background
document.addEventListener('DOMContentLoaded', () => {
    const overlay = document.getElementById('modal-overlay');
    if (overlay) {
        overlay.addEventListener('click', (e) => {
            // Only close if clicking directly on overlay, not on modal content
            if (e.target === overlay) {
                closeModal();
            }
        });
    }
});

// Make modal functions globally available
window.openModal = openModal;
window.closeModal = closeModal;

// ==================== Menu Dropdown Component ====================
class MenuDropdownComponent extends UIComponent {
    /**
     * Check if an item is visible based on permissions
     *
     * @param {object} item - Menu item
     * @param {array} permissions - Array of user permissions
     * @returns {boolean} - True if item should be visible
     */
    isItemVisible(item, permissions) {
        const itemPermission = item.permission;

        // null/undefined = visible for all
        if (!itemPermission) {
            return true;
        }

        // 'auth' = only authenticated users
        if (itemPermission === 'auth') {
            return permissions.includes('auth');
        }

        // 'no-auth' = only non-authenticated users
        if (itemPermission === 'no-auth') {
            return permissions.includes('no-auth');
        }

        // Any other value = check if it exists in permissions array
        return permissions.includes(itemPermission);
    }

    /**
     * Check if a submenu has any visible children (recursive)
     *
     * @param {array} submenu - Array of submenu items
     * @param {array} permissions - Array of user permissions
     * @returns {boolean} - True if at least one child is visible
     */
    hasVisibleChildren(submenu, permissions) {
        if (!submenu || !Array.isArray(submenu) || submenu.length === 0) {
            return false;
        }

        return submenu.some(item => {
            // Separators don't count for visibility
            if (item.type === 'separator') {
                return false;
            }

            // If item itself is not visible, skip it
            if (!this.isItemVisible(item, permissions)) {
                return false;
            }

            // If item has submenu, check recursively
            if (item.submenu && item.submenu.length > 0) {
                return this.hasVisibleChildren(item.submenu, permissions);
            }

            // Regular visible item
            return true;
        });
    }

    render() {
        const menuContainer = document.createElement('div');
        menuContainer.className = 'menu-dropdown';

        // Trigger button with customization
        const trigger = document.createElement('button');
        trigger.className = 'menu-dropdown-trigger';

        // Custom trigger configuration
        const triggerConfig = this.config.trigger || {};
        const triggerLabel = triggerConfig.label || '☰ Menu';
        const triggerIcon = triggerConfig.icon;
        const triggerStyle = triggerConfig.style || 'default';

        trigger.className += ` menu-trigger-${triggerStyle}`;

        // Build trigger content
        let triggerContent = '';
        if (triggerIcon) {
            triggerContent += `<span class="trigger-icon">${triggerIcon}</span>`;
        }
        triggerContent += `<span class="trigger-label">${triggerLabel}</span>`;

        trigger.innerHTML = triggerContent;

        // Dropdown content with customization
        const content = document.createElement('div');
        content.className = 'menu-dropdown-content';

        // Apply position class
        const position = this.config.position || 'bottom-left';
        content.classList.add(`position-${position}`);

        // Apply custom width
        if (this.config.width) {
            content.style.minWidth = this.config.width;
        }

        // Build menu items
        if (this.config.items && this.config.items.length > 0) {
            this.config.items.forEach(item => {
                content.appendChild(this.renderMenuItem(item));
            });
        }

        // Check if all items are hidden
        const permissions = this.config.permissions || [];
        const hasVisibleItems = this.config.items && this.config.items.some(item => {
            if (item.type === 'separator') {
                return false;
            }
            const isVisible = this.isItemVisible(item, permissions);
            if (item.submenu && item.submenu.length > 0) {
                return isVisible && this.hasVisibleChildren(item.submenu, permissions);
            }
            return isVisible;
        });

        // Hide entire menu if no visible items
        if (!hasVisibleItems) {
            menuContainer.style.display = 'none';
        }

        // Toggle menu on click with improved UX
        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            const isActive = content.classList.contains('show');

            // Close all other menus first
            this.closeAllMenus();

            if (!isActive) {
                content.classList.add('show');
                trigger.classList.add('active');

                // Add smooth entrance animation
                content.style.animationDuration = '0.3s';

                // Focus management for accessibility
                const firstMenuItem = content.querySelector('.menu-item:not([disabled])');
                if (firstMenuItem) {
                    setTimeout(() => firstMenuItem.focus(), 100);
                }
            }
        });

        // Close menu when clicking outside (improved for submenus)
        document.addEventListener('click', (e) => {
            // Check if click is outside the entire menu system (including submenus)
            if (!menuContainer.contains(e.target) &&
                !e.target.closest('.submenu')) {
                this.closeMenu(content, trigger);
            }
        });

        // Close menu on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && content.classList.contains('show')) {
                this.closeMenu(content, trigger);
                trigger.focus();
            }
        });

        menuContainer.appendChild(trigger);
        menuContainer.appendChild(content);

        // Remove width from config before applying common attributes
        // because width is already applied to the dropdown content
        const originalWidth = this.config.width;
        delete this.config.width;

        const result = this.applyCommonAttributes(menuContainer);

        // Restore width to config for future use
        if (originalWidth) {
            this.config.width = originalWidth;
        }

        return result;
    }

    renderMenuItem(item) {
        // Separator
        if (item.type === 'separator') {
            const separator = document.createElement('div');
            separator.className = 'menu-separator';
            return separator;
        }

        // Get permissions from config
        const permissions = this.config.permissions || [];

        // Check if item has submenu
        const hasSubmenu = item.submenu && item.submenu.length > 0;

        // Determine visibility
        let isVisible = this.isItemVisible(item, permissions);

        // If has submenu, check if any children are visible
        if (hasSubmenu && isVisible) {
            isVisible = this.hasVisibleChildren(item.submenu, permissions);
        }

        // Regular item or submenu parent
        const menuItem = document.createElement(item.url ? 'a' : 'button');
        menuItem.className = 'menu-item';

        // Apply visibility
        if (!isVisible) {
            menuItem.style.display = 'none';
        }

        if (hasSubmenu) {
            menuItem.classList.add('has-submenu');
        }

        // Icon
        if (item.icon) {
            const icon = document.createElement('span');
            icon.className = 'icon';
            icon.textContent = item.icon;
            menuItem.appendChild(icon);
        }

        // Label
        const label = document.createElement('span');
        label.textContent = item.label;
        menuItem.appendChild(label);

        // Handle URL navigation
        if (item.url) {
            menuItem.href = item.url;
        }

        // Handle action with improved UX
        if (item.action) {
            menuItem.addEventListener('click', (e) => {
                e.preventDefault();

                // Visual feedback
                menuItem.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    menuItem.style.transform = '';
                }, 150);

                // Close all menus
                this.closeAllMenus();

                // Merge item params with caller service id from menu config
                const params = {
                    ...(item.params || {}),
                    _caller_service_id: this.config._caller_service_id
                };

                // Send event to backend
                this.sendEventToBackend('click', item.action, params);
            });

            // Keyboard navigation support
            menuItem.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    menuItem.click();
                }
            });
        }

        // Render submenu if exists
        if (hasSubmenu) {
            const submenu = document.createElement('div');
            submenu.className = 'submenu';
            submenu.style.display = 'none'; // Ensure it starts hidden

            item.submenu.forEach(subitem => {
                submenu.appendChild(this.renderMenuItem(subitem));
            });

            menuItem.appendChild(submenu);

            let hideTimeout = null;

            const showSubmenu = () => {
                if (hideTimeout) {
                    clearTimeout(hideTimeout);
                    hideTimeout = null;
                }
                submenu.style.setProperty('display', 'block', 'important');
                submenu.style.setProperty('opacity', '1', 'important');
                submenu.style.setProperty('visibility', 'visible', 'important');
                submenu.classList.add('show');
            };

            const hideSubmenu = () => {
                submenu.style.setProperty('display', 'none', 'important');
                submenu.style.setProperty('opacity', '0', 'important');
                submenu.style.setProperty('visibility', 'hidden', 'important');
                submenu.classList.remove('show');
            };

            menuItem.addEventListener('mouseenter', (e) => {
                showSubmenu();
            });

            menuItem.addEventListener('mouseleave', (e) => {
                hideTimeout = setTimeout(hideSubmenu, 200);
            });

            // Keep submenu visible when hovering over it
            submenu.addEventListener('mouseenter', () => {
                showSubmenu();
            });

            submenu.addEventListener('mouseleave', () => {
                hideTimeout = setTimeout(hideSubmenu, 200);
            });
        }

        return menuItem;
    }

    /**
     * Close all open menus
     */
    closeAllMenus() {
        document.querySelectorAll('.menu-dropdown-content.show').forEach(content => {
            content.classList.remove('show');
        });
        document.querySelectorAll('.menu-dropdown-trigger.active').forEach(trigger => {
            trigger.classList.remove('active');
        });
    }

    /**
     * Close specific menu
     */
    closeMenu(content, trigger) {
        content.classList.remove('show');
        trigger.classList.remove('active');
    }
}

// ==================== Storage Component ====================
class StorageComponent extends UIComponent {
    render() {
        // This component doesn't render anything visible
        // It just stores data in localStorage
        this.storeData();
        return document.createDocumentFragment(); // Return empty fragment
    }

    storeData() {
        // Iterate over all config properties (except internal ones)
        Object.keys(this.config).forEach(key => {
            // Skip internal properties that start with underscore
            if (key.startsWith('_') || key === 'type') {
                return;
            }

            const value = this.config[key];

            // Store the value in localStorage
            // If it's an object/array, stringify it
            if (typeof value === 'object' && value !== null) {
                localStorage.setItem(key, JSON.stringify(value));
                console.log(`💾 Stored in localStorage: "${key}" = ${JSON.stringify(value)}`);
            } else {
                localStorage.setItem(key, String(value));
                console.log(`💾 Stored in localStorage: "${key}" = ${value}`);
            }
        });
    }

    updateComponent(newConfig) {
        this.config = { ...this.config, ...newConfig };
        this.storeData();
    }
}

/**
 * Load menu UI
 */
async function loadMenuUI() {
    if (!window.MENU_SERVICE) {
        console.log('ℹ️ No MENU_SERVICE defined, skipping menu load');
        return;
    }

    try {
        const resetQuery = window.RESET_DEMO ? 'reset=true' : '';
        const usimStorage = localStorage.getItem('usim') || '';
        const parentElement = 'parent=menu';

        const response = await fetch(`/api/${window.MENU_SERVICE}?${parentElement}&${resetQuery}`,
            {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-USIM-Storage': usimStorage,
                }
            }
        );
        const uiData = await response.json();

        const menuContainer = document.getElementById('menu');
        if (!menuContainer) {
            console.error('❌ Menu container #menu not found');
            return;
        }

        // If globalRenderer exists, merge menu data and components
        if (globalRenderer) {
            // Merge menu data
            Object.assign(globalRenderer.data, uiData);

            // Sort components by _order field (preserves backend order)
            const entries = Object.entries(uiData)
                .filter(([id]) => id !== 'storage' && id !== 'action')
                .sort((a, b) => {
                    const orderA = a[1]._order || 999;
                    const orderB = b[1]._order || 999;
                    return orderA - orderB;
                });

            // Separate top-level (parent='menu') from children
            const topLevel = entries.filter(([, config]) => config.parent === 'menu');
            const children = entries.filter(([, config]) => config.parent !== 'menu');

            // Combine: top-level first (sorted by _order), then children
            const components = [...topLevel, ...children];

            // Create and add menu components to globalRenderer AND render them
            for (const [id, config] of components) {
                const component = ComponentFactory.create(id, config);
                if (component) {
                    // Add to global registry
                    globalRenderer.components.set(id, component);

                    // Render and mount component
                    const element = component.render();
                    if (element) {
                        let parentEl;

                        // Find parent element by data-component-id or by DOM id
                        if (config.parent === 'menu') {
                            parentEl = menuContainer;
                        } else {
                            // Try to find by data-component-id first
                            parentEl = document.querySelector(`[data-component-id="${config.parent}"]`);
                            // Fallback to regular id
                            if (!parentEl) {
                                parentEl = document.getElementById(config.parent);
                            }
                        }

                        if (parentEl) {
                            parentEl.appendChild(element);
                        }
                    }
                }
            }
        } else {
            // First load - create globalRenderer with menu
            globalRenderer = new UIRenderer(uiData);
            globalRenderer.render();
        }
    } catch (error) {
        console.error('❌ Error loading menu:', error);
    }
}

// Load UI on page load
document.addEventListener('DOMContentLoaded', async () => {
    await loadDemoUI();  // Load main UI first to create globalRenderer
    await loadMenuUI();  // Then load menu and merge into globalRenderer

    // Check for pending toast after page load
    const pendingToast = sessionStorage.getItem('pendingToast');
    if (pendingToast) {
        sessionStorage.removeItem('pendingToast');
        try {
            const toastConfig = JSON.parse(pendingToast);
            if (globalRenderer) {
                globalRenderer.showToast(toastConfig);
            }
        } catch (error) {
            console.error('Error showing pending toast:', error);
        }
    }
});
