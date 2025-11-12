<?php

namespace App\Services\UI\Components;

use App\Services\UI\Enums\Align;

/**
 * Builder for Table Cell UI components
 * 
 * Represents a cell in a table row. This component must be associated with a TableRow
 * and can contain either simple text or a single child component.
 */
class TableCellBuilder extends UIComponent
{
    /** @var TableRowBuilder|null The parent row */
    private ?TableRowBuilder $row;

    /** @var UIComponent|null Optional child component */
    private ?UIComponent $child = null;

    /**
     * Create a new table cell
     * 
     * @param TableRowBuilder $row The parent row this cell belongs to
     * @param string|null $name Optional name for the cell
     */
    public function __construct(?TableRowBuilder $row = null, ?string $name = null)
    {
        $this->row = $row;
        parent::__construct($name);
    }

    protected function getDefaultConfig(): array
    {
        return [
            'text' => null,
            'align' => null,
            'url_image' => null,
            'button' => null,
            'column' => null,  // Column index for ordering
            'min_width' => null,  // Minimum width in pixels
            'max_width' => null,  // Maximum width in pixels
            'min_height' => null,  // Minimum height in pixels, inherited from row
            'padding' => null,  // Padding in pixels for more compact cells
        ];
    }

    public function toString(): string
    {
        return "TableCell(id={$this->id}, "  .
            //", name={$this->name}, text=" .
            ", column=" . ($this->config['column'] ?? 'null') .
            ", text=" . ($this->config['text'] ?? 'null') .
            ")";
    }

    public function clearCell(): self
    {
        $this->setConfig('text', '');
        $this->setConfig('url_image', null);
        $this->setConfig('button', null);
        $this->child = null;
        return $this;
    }

    /**
     * Set the column index (for ordering)
     * 
     * @param int $column Column index (0-based)
     * @return self
     */
    public function column(int $column): self
    {
        $this->setConfig('column', $column);
        return $this;
    }

    /**
     * Set width constraints for the cell
     * 
     * @param int|null $minWidth Minimum width in pixels
     * @param int|null $maxWidth Maximum width in pixels
     * @return self
     */
    public function widthConstraints(?int $minWidth = null, ?int $maxWidth = null): static
    {
        if ($minWidth !== null) {
            $this->setConfig('min_width', $minWidth);
        }
        if ($maxWidth !== null) {
            $this->setConfig('max_width', $maxWidth);
        }
        return $this;
    }

    /**
     * Set padding for the cell
     * 
     * @param int $padding Padding in pixels for more compact cells
     * @return self For method chaining
     */
    public function padding(int $padding): self
    {
        $this->setConfig('padding', $padding);
        return $this;
    }

    /**
     * Set simple text content for the cell
     * 
     * @param string|int|float|null $text The text content
     * @return self For method chaining
     */
    public function text(string|int|float|null $text): self
    {
        return $this->setConfig('text', $text);
    }

    /**
     * Get simple text content of the cell
     * @return string|null
     */
    public function getText(): ?string
    {
        return $this->config['text'];
    }

    /**
     * Set horizontal alignment for the cell content
     * 
     * @param Align $align The alignment (left, center, right)
     * @return self For method chaining
     */
    public function align(Align $align): self
    {
        return $this->setConfig('align', $align->value);
    }

    /**
     * Set button configuration for the cell
     * 
     * @param array $button Button configuration with keys: label, action, style, parameters
     * @return self For method chaining
     */
    public function button(array $button): self
    {
        return $this->setConfig('button', $button);
    }

    /**
     * Set image URL for the cell
     * 
     * @param string $url Image URL
     * @param string|null $alt Alt text for the image
     * @param string|null $width Image width
     * @param string|null $height Image height
     * @return self For method chaining
     */
    public function urlImage(string $url, ?string $alt = null, ?string $width = null, ?string $height = null): self
    {
        $this->setConfig('url_image', $url);
        if ($alt !== null) {
            $this->setConfig('alt', $alt);
        }
        if ($width !== null) {
            $this->setConfig('image_width', $width);
        }
        if ($height !== null) {
            $this->setConfig('image_height', $height);
        }
        return $this;
    }

    /**
     * Add a child component to this cell
     * Only one child component is allowed per cell
     * Note: Containers are not allowed as children to prevent recursion issues
     * 
     * @param UIComponent $component The component to add
     * @return self For method chaining
     */
    public function addChild(UIComponent $component): self
    {
        if ($this->child !== null) {
            throw new \LogicException("TableCell can only contain one child component. Use a container if you need multiple components.");
        }

        // Prevent adding containers to avoid recursion/loops
        if ($component instanceof UIContainer) {
            throw new \LogicException("TableCell cannot contain a UIContainer. Containers should be outside the table structure.");
        }

        $this->child = $component;
        $component->setParent($this->id);
        return $this;
    }

    /**
     * Get the parent row
     * 
     * @return TableRowBuilder
     */
    public function getRow(): TableRowBuilder
    {
        return $this->row;
    }

    /**
     * Get the child component if any
     * 
     * @return UIComponent|null
     */
    public function getChild(): ?UIComponent
    {
        return $this->child;
    }

    /**
     * {@inheritDoc}
     * 
     * Includes the child component in the flat JSON structure
     */
    /**
     * {@inheritDoc}
     */
    public function toJson(?int $order = null): array
    {
        // Get base config and filter nulls
        $config = array_filter($this->config, fn($value) => $value !== null);

        // Inherit min_height from parent row if not set
        if (!isset($config['min_height']) || $config['min_height'] === null) {
            $rowConfig = $this->row->getRowConfig();
            if (isset($rowConfig['min_height']) && $rowConfig['min_height'] !== null) {
                $config['min_height'] = $rowConfig['min_height'];
            }
        }

        // Remove 'visible' if it's true (default value)
        if (isset($config['visible']) && $config['visible'] === true) {
            unset($config['visible']);
        }

        // Remove 'align' if it's 'left' (default value)
        if (isset($config['align']) && $config['align'] === 'left') {
            unset($config['align']);
        }

        // Exclude additional keys
        $excludeKeys = $this->getExcludedJsonKeys();
        if (!empty($excludeKeys)) {
            $config = array_diff_key($config, array_flip($excludeKeys));
        }

        // CRITICAL: Include component ID in config for frontend lookups
        $config['_id'] = $this->id;

        // Start with this cell
        $result = [$this->id => $config];

        // Add child component if present
        if ($this->child !== null) {
            $childJson = $this->child->toJson();
            $result = $result + $childJson;
        }

        return $result;
    }

    /**
     * Exclude keys from JSON output
     * 
     * @return array List of keys to exclude
     */
    protected function getExcludedJsonKeys(): array
    {
        // Don't exclude 'name' - we need it for matrix cell identification
        return [];
    }
}
