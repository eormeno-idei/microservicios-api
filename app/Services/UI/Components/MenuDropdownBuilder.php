<?php
namespace App\Services\UI\Components;

/**
 * Menu Dropdown Builder
 *
 * Builds dropdown menu structures with support for nested submenus
 */
class MenuDropdownBuilder extends UIComponent
{
    private array $items = [];

    public function getDefaultConfig(): array
    {
        return [
            'name'        => $this->name,
            'items'       => [],
            'permissions' => [],
        ];
    }

    /**
     * Override toJson to ensure items are included in config
     */
    public function toJson(?int $order = null): array
    {
        // Copy items to config before rendering
        $this->config['items'] = $this->items;

        // Ensure permissions array is in config
        if (! isset($this->config['permissions'])) {
            $this->config['permissions'] = [];
        }

        // Call parent implementation
        return parent::toJson($order);
    }

    /**
     * {@inheritDoc}
     */
    public static function deserialize(int $id, array $config): self
    {
        /** @var MenuDropdownBuilder $component */
        $component = parent::deserialize($id, $config);
        if (isset($config['items']) && is_array($config['items'])) {
            $component->items = $config['items'];
        }
        if (isset($config['permissions']) && is_array($config['permissions'])) {
            $component->config['permissions'] = $config['permissions'];
        }
        return $component;
    }

    /**
     * Add a menu item
     *
     * @param string $label Item label
     * @param string|null $action Action to trigger (optional if has submenu)
     * @param array $params Action parameters
     * @param string|null $icon Icon emoji or text
     * @param array $submenu Submenu items
     * @param string|null $permission Permission required ('auth' for authenticated, specific permission slug, or null for public)
     * @return self
     */
    public function item(
        string $label,
        ?string $action = null,
        array $params = [],
        ?string $icon = null,
        array $submenu = [],
        ?string $permission = null
    ): self {
        $item = [
            'label'      => $label,
            'action'     => $action,
            'params'     => $params,
            'icon'       => $icon,
            'submenu'    => $submenu,
            'permission' => $permission,
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
            'type' => 'separator',
        ];
        return $this;
    }

    /**
     * Add a menu item with URL navigation
     *
     * @param string $label Item label
     * @param string $url URL to navigate to
     * @param string|null $icon Icon emoji or text
     * @param string|null $permission Permission required ('auth' for authenticated, specific permission slug, or null for public)
     * @return self
     */
    public function link(string $label, string $url, ?string $icon = null, ?string $permission = null): self
    {
        $item = [
            'label'      => $label,
            'url'        => $url,
            'icon'       => $icon,
            'permission' => $permission,
        ];

        $this->items[] = $item;
        return $this;
    }

    /**
     * Add a submenu item
     *
     * @param string $label Parent item label
     * @param callable $callback Callback to build submenu items
     * @param string|null $icon Parent icon
     * @return self
     */
    public function submenu(string $label, callable $callback, ?string $icon = null): self
    {
        $submenuBuilder = new self($label . '_submenu');
        $callback($submenuBuilder);

        $item = [
            'label'   => $label,
            'icon'    => $icon,
            'submenu' => $submenuBuilder->items,
        ];

        $this->items[] = $item;
        return $this;
    }

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
            'icon'  => $icon,
            'style' => $style,
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
     * Set menu width (overrides parent to accept int or string)
     *
     * @param int|string $width Width in pixels (int) or with units (string)
     * @return static
     */
    public function width(int | string $width): static
    {
        if (is_int($width)) {
            $this->config['width'] = $width . 'px';
        } else {
            $this->config['width'] = $width;
        }
        return $this;
    }

    /**
     * Set user permissions for menu visibility control
     *
     * @param array|null $permissions Array of user permissions, or null to clear
     * @return self
     */
    public function setUserPermissions(?array $permissions = null): self
    {
        if ($permissions === null || empty($permissions)) {
            // No authenticated user - add 'no-auth' marker
            $this->config['permissions'] = ['no-auth'];
        } else {
            // Set permissions as provided (no automatic additions)
            $this->config['permissions'] = $permissions;
        }
        return $this;
    }
}
