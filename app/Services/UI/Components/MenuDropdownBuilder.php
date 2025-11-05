<?php

namespace App\Services\UI\Components;

use App\Services\UI\Support\UIIdGenerator;

/**
 * Menu Dropdown Builder
 *
 * Builds dropdown menu structures with support for nested submenus
 */
class MenuDropdownBuilder extends UIComponent
{
    // private array $config = [];
    private array $items = [];
    // private string $name;

    // public function __construct(string $name)
    // {
    //     $this->name = $name;
    //     $this->config = [
    //         'type' => 'menu_dropdown',
    //         'name' => $name,
    //         'items' => []
    //     ];
    // }

    public function getDefaultConfig(): array
    {
        return [
            'type' => 'menu_dropdown',
            'name' => $this->name,
            'items' => []
         ];
    }

    /**
     * Override toJson to ensure items are included in config
     */
    public function toJson(?int $order = null): array
    {
        // Copy items to config before rendering
        $this->config['items'] = $this->items;

        // Call parent implementation
        return parent::toJson($order);
    }

    /**
     * Add a menu item
     *
     * @param string $label Item label
     * @param string|null $action Action to trigger (optional if has submenu)
     * @param array $params Action parameters
     * @param string|null $icon Icon emoji or text
     * @param array $submenu Submenu items
     * @return self
     */
    public function item(
        string $label,
        ?string $action = null,
        array $params = [],
        ?string $icon = null,
        array $submenu = []
    ): self {
        $item = [
            'label' => $label,
            'action' => $action,
            'params' => $params,
            'icon' => $icon,
            'submenu' => $submenu
        ];

        $this->items[] = $item;
        return $this;
    }

    /**
     * Add a separator line
     *
     * @return self
     */
    public function separator(): self
    {
        $this->items[] = [
            'type' => 'separator'
        ];
        return $this;
    }

    /**
     * Add a menu item with URL navigation
     *
     * @param string $label Item label
     * @param string $url URL to navigate to
     * @param string|null $icon Icon emoji or text
     * @return self
     */
    public function link(string $label, string $url, ?string $icon = null): self
    {
        $item = [
            'label' => $label,
            'url' => $url,
            'icon' => $icon,
        ];

        $this->items[] = $item;
        return $this;
    }

    /**
     * Create a submenu structure
     *
     * @param string $label Parent item label
     * @param string|null $icon Parent icon
     * @param callable $callback Callback to build submenu items
     * @return self
     */
    public function submenu(string $label, ?string $icon = null, callable $callback): self
    {
        $submenuBuilder = new self($label . '_submenu');
        $callback($submenuBuilder);

        $item = [
            'label' => $label,
            'icon' => $icon,
            'submenu' => $submenuBuilder->items
        ];

        $this->items[] = $item;
        return $this;
    }

    /**
     * Set the parent container for this menu
     *
     * @param string $parentId Parent container ID or name
     * @return self
     */
    // public function parent(string $parentId): self
    // {
    //     $this->config['parent'] = $parentId;
    //     return $this;
    // }

    /**
     * Set the caller service ID for action callbacks
     *
     * @param string $serviceId Service component ID
     * @return self
     */
    public function callerServiceId(string $serviceId): self
    {
        $this->config['_caller_service_id'] = $serviceId;
        return $this;
    }

    /**
     * Customize the trigger button
     *
     * @param string $label Button text
     * @param string|null $icon Button icon
     * @param string $style Button style (primary, secondary, etc.)
     * @return self
     */
    public function trigger(string $label = '☰', ?string $icon = null, string $style = 'default'): self
    {
        $this->config['trigger'] = [
            'label' => $label,
            'icon' => $icon,
            'style' => $style
        ];
        return $this;
    }

    /**
     * Set menu positioning
     *
     * @param string $position 'bottom-left', 'bottom-right', 'top-left', 'top-right'
     * @return self
     */
    public function position(string $position = 'bottom-left'): self
    {
        $this->config['position'] = $position;
        return $this;
    }

    /**
     * Set menu width
     *
     * @param int $width Width in pixels
     * @return self
     */
    public function width(int $width = 240): self
    {
        $this->config['width'] = $width . 'px';
        return $this;
    }

    /**
     * Build and return the menu configuration
     *
     * @return array
     */
    // public function build(): array
    // {
    //     $this->config['items'] = $this->items;

    //     // Generate unique ID for this menu
    //     $id = UIIdGenerator::generate($this->name);
    //     $this->config['_id'] = $id;

    //     // Return as properly formatted UI component array
    //     return [
    //         $id => $this->config
    //     ];
    // }
}
