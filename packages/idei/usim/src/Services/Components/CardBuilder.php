<?php

namespace Idei\Usim\Services\Components;

/**
 * Builder for Card UI components
 *
 * Modern and versatile card component with support for headers, content,
 * images, actions, and various styling options. Perfect for displaying
 * structured content in an attractive container.
 */
class CardBuilder extends UIComponent
{
    protected function getDefaultConfig(): array
    {
        return [
            // Core content
            'title' => null,
            'subtitle' => null,
            'description' => null,
            'content' => null, // HTML content

            // Image
            'image' => null, // Image URL
            'image_position' => 'top', // top, bottom, left, right, background
            'image_alt' => null,
            'image_fit' => 'cover', // cover, contain, fill, scale-down

            // Header
            'header' => null, // Custom header content
            'show_header' => true,

            // Footer/Actions
            'footer' => null, // Custom footer content
            'show_footer' => true,
            'actions' => [], // Array of button configurations

            // Visual style
            'style' => 'default', // default, outlined, elevated, flat, gradient
            'variant' => 'standard', // standard, compact, expanded, media
            'size' => 'medium', // small, medium, large
            'elevation' => 'medium', // none, low, medium, high

            // Layout
            'orientation' => 'vertical', // vertical, horizontal
            'content_padding' => 'medium', // none, small, medium, large
            'border_radius' => 'medium', // none, small, medium, large, round

            // Colors and theming
            'background_color' => null,
            'border_color' => null,
            'text_color' => null,
            'theme' => null, // primary, secondary, success, warning, danger, info

            // Interaction
            'clickable' => false,
            'hover_effect' => true,
            'action' => null, // Action when card is clicked
            'parameters' => [],
            'url' => null, // URL for navigation
            'target' => '_self', // Link target

            // Badge/Status
            'badge' => null,
            'badge_position' => 'top-right', // top-left, top-right, bottom-left, bottom-right
            'status' => null, // Status indicator

            // Accessibility
            'aria_label' => null,
            'role' => 'article', // article, button, link, etc.
        ];
    }

    /**
     * Set the card title
     *
     * @param string $title Card title
     * @return self
     */
    public function title(string $title): self
    {
        return $this->setConfig('title', $title);
    }

    /**
     * Set the card subtitle
     *
     * @param string $subtitle Card subtitle
     * @return self
     */
    public function subtitle(string $subtitle): self
    {
        return $this->setConfig('subtitle', $subtitle);
    }

    /**
     * Set the card description
     *
     * @param string $description Card description
     * @return self
     */
    public function description(string $description): self
    {
        return $this->setConfig('description', $description);
    }

    /**
     * Set card content (HTML)
     *
     * @param string $content HTML content
     * @return self
     */
    public function content(string $content): self
    {
        return $this->setConfig('content', $content);
    }

    /**
     * Set card image
     *
     * @param string $imageUrl Image URL
     * @param string $position Image position (top, bottom, left, right, background)
     * @param string $alt Alt text for accessibility
     * @return self
     */
    public function image(string $imageUrl, string $position = 'top', ?string $alt = null): self
    {
        return $this->setConfig('image', $imageUrl)
                   ->setConfig('image_position', $position)
                   ->setConfig('image_alt', $alt);
    }

    /**
     * Set card style
     *
     * @param string $style Style variant (default, outlined, elevated, flat, gradient)
     * @return self
     */
    public function style(string $style): self
    {
        return $this->setConfig('style', $style);
    }

    /**
     * Set card size
     *
     * @param string $size Size variant (small, medium, large)
     * @return self
     */
    public function size(string $size): self
    {
        return $this->setConfig('size', $size);
    }

    /**
     * Set card elevation/shadow
     *
     * @param string $elevation Elevation level (none, low, medium, high)
     * @return self
     */
    public function elevation(string $elevation): self
    {
        return $this->setConfig('elevation', $elevation);
    }

    /**
     * Set card theme
     *
     * @param string $theme Theme color (primary, secondary, success, warning, danger, info)
     * @return self
     */
    public function theme(string $theme): self
    {
        return $this->setConfig('theme', $theme);
    }

    /**
     * Make card clickable with action
     *
     * @param string $action Action to trigger
     * @param array $parameters Action parameters
     * @return self
     */
    public function action(string $action, array $parameters = []): self
    {
        return $this->setConfig('clickable', true)
                   ->setConfig('action', $action)
                   ->setConfig('parameters', $parameters);
    }

    /**
     * Make card clickable with URL navigation
     *
     * @param string $url URL to navigate to
     * @param string $target Link target (_self, _blank, etc.)
     * @return self
     */
    public function url(string $url, string $target = '_self'): self
    {
        return $this->setConfig('clickable', true)
                   ->setConfig('url', $url)
                   ->setConfig('target', $target);
    }

    /**
     * Add action buttons to card footer
     *
     * @param array $actions Array of button configurations
     * @return self
     */
    public function actions(array $actions): self
    {
        return $this->setConfig('actions', $actions);
    }

    /**
     * Add an action button to the card footer
     *
     * @param string $label Button label
     * @param string $action Action to trigger
     * @param array $parameters Action parameters
     * @param string $style Button style
     * @return self
     */
    public function addAction(string $label, string $action, array $parameters = [], string $style = 'primary'): self
    {
        // Inject caller service ID if not already present
        if (!isset($parameters['_caller_service_id'])) {
            // Get the service class that's calling this component
            $serviceClass = $this->detectCallingService();
            // Get the service ID (offset) for that class
            $serviceId = $this->getServiceIdFromClass($serviceClass);
            $parameters['_caller_service_id'] = $serviceId;
        }

        $currentActions = $this->config['actions'] ?? [];
        $currentActions[] = [
            'label' => $label,
            'action' => $action,
            'parameters' => $parameters,
            'style' => $style
        ];
        return $this->setConfig('actions', $currentActions);
    }

    /**
     * Detect the calling service class from the stack trace
     *
     * @return string Service class name
     */
    private function detectCallingService(): string
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 15);

        // Look for the first class outside UI namespace
        foreach ($trace as $frame) {
            if (
                isset($frame['class']) &&
                !str_starts_with($frame['class'], 'App\\Services\\UI\\')
            ) {
                return $frame['class'];
            }
        }

        return 'default';
    }

    /**
     * Get the base service ID for a service class
     *
     * @param string $serviceClass Full service class name
     * @return int Service base ID (offset)
     */
    private function getServiceIdFromClass(string $serviceClass): int
    {
        if ($serviceClass === 'default') {
            return 0;
        }

        // Calculate offset (same logic as UIIdGenerator::getContextOffset)
        $hash = abs(crc32($serviceClass));
        $segment = $hash % 9999; // 9999 segments (not 500)
        return $segment * 10000;
    }

    /**
     * Set card badge
     *
     * @param string $badge Badge text or icon
     * @param string $position Badge position
     * @return self
     */
    public function badge(string $badge, string $position = 'top-right'): self
    {
        return $this->setConfig('badge', $badge)
                   ->setConfig('badge_position', $position);
    }

    /**
     * Set horizontal orientation
     *
     * @return self
     */
    public function horizontal(): self
    {
        return $this->setConfig('orientation', 'horizontal');
    }

    /**
     * Set vertical orientation
     *
     * @return self
     */
    public function vertical(): self
    {
        return $this->setConfig('orientation', 'vertical');
    }

    /**
     * Enable hover effects
     *
     * @param bool $enabled Whether to enable hover effects
     * @return self
     */
    public function hover(bool $enabled = true): self
    {
        return $this->setConfig('hover_effect', $enabled);
    }

    /**
     * Set compact variant
     *
     * @return self
     */
    public function compact(): self
    {
        return $this->setConfig('variant', 'compact')
                   ->setConfig('content_padding', 'small');
    }

    /**
     * Set expanded variant
     *
     * @return self
     */
    public function expanded(): self
    {
        return $this->setConfig('variant', 'expanded')
                   ->setConfig('content_padding', 'large');
    }
}
