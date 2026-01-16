<?php

namespace Idei\Usim\Services\Components;

use Idei\Usim\Services\Enums\Align;
use Idei\Usim\Services\Enums\FontWeight;

/**
 * Builder for Table Header Cell UI components
 *
 * Represents a header cell in a table header row. This component must be associated with a TableHeaderRow.
 * Header cells can be sortable and trigger actions for sorting.
 */
class TableHeaderCellBuilder extends UIComponent
{
    /** @var TableHeaderRowBuilder|null The parent header row */
    private ?TableHeaderRowBuilder $headerRow;

    /**
     * Create a new table header cell
     *
     * @param TableHeaderRowBuilder|null $headerRow The parent header row this cell belongs to
     * @param string|null $name Optional name for the cell
     */
    public function __construct(?TableHeaderRowBuilder $headerRow = null, ?string $name = null)
    {
        $this->headerRow = $headerRow;
        parent::__construct($name);
    }

    protected function getDefaultConfig(): array
    {
        return [
            'text' => null,
            'sortable' => false,
            'sort_direction' => null,
            'align' => null,
            'width' => null,
            'action' => null,
            'parameters' => null,
            'tooltip' => null,
            'color' => null,
            'background_color' => null,
            'font_weight' => FontWeight::BOLD->value,
            'colspan' => 1,
            'column' => null,  // Column index for ordering
            'min_width' => null,  // Minimum width in pixels
            'max_width' => null,  // Maximum width in pixels
        ];
    }

    /**
     * Set the header text
     *
     * @param string $text The header text
     * @return self For method chaining
     */
    public function text(string $text): self
    {
        return $this->setConfig('text', $text);
    }

    /**
     * Make this header sortable
     *
     * @param bool $sortable True if sortable
     * @return self For method chaining
     */
    public function sortable(bool $sortable = true): self
    {
        return $this->setConfig('sortable', $sortable);
    }

    /**
     * Set the sort direction
     *
     * @param string|null $direction Sort direction ('asc', 'desc', or null)
     * @return self For method chaining
     */
    public function sortDirection(?string $direction): self
    {
        if ($direction !== null && !in_array($direction, ['asc', 'desc'])) {
            throw new \InvalidArgumentException("Sort direction must be 'asc', 'desc', or null");
        }
        return $this->setConfig('sort_direction', $direction);
    }

    /**
     * Set horizontal alignment for the header content
     *
     * @param Align $align The alignment (left, center, right)
     * @return self For method chaining
     */
    public function align(Align $align): self
    {
        return $this->setConfig('align', $align->value);
    }

    /**
     * Set the column width
     *
     * @param string $width Width value (e.g., '200px', '20%')
     * @return self For method chaining
     */
    public function width(string $width): static
    {
        return $this->setConfig('width', $width);
    }

    /**
     * Set width constraints (min and max) for the header cell
     *
     * @param int|null $minWidth Minimum width in pixels
     * @param int|null $maxWidth Maximum width in pixels
     * @return self
     */
    public function widthConstraints(?int $minWidth = null, ?int $maxWidth = null): self
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
     * Set the action to trigger when header is clicked (for sorting)
     *
     * @param string $action Action name
     * @return self For method chaining
     */
    public function action(string $action): self
    {
        return $this->setConfig('action', $action);
    }

    /**
     * Set tooltip text
     *
     * @param string $tooltip Tooltip text
     * @return self For method chaining
     */
    public function tooltip(string $tooltip): self
    {
        return $this->setConfig('tooltip', $tooltip);
    }

    /**
     * Set the column index for this cell (for ordering)
     *
     * @param int $column The column index (0-based)
     * @return self For method chaining
     */
    public function column(int $column): self
    {
        return $this->setConfig('column', $column);
    }

    /**
     * Set text color
     *
     * @param string $color Color value (e.g., '#333', 'red')
     * @return self For method chaining
     */
    public function color(string $color): self
    {
        return $this->setConfig('color', $color);
    }

    /**
     * Set background color
     *
     * @param string $backgroundColor Background color value
     * @return self For method chaining
     */
    public function backgroundColor(string $backgroundColor): self
    {
        return $this->setConfig('background_color', $backgroundColor);
    }

    /**
     * Set font weight
     *
     * @param FontWeight $fontWeight Font weight
     * @return self For method chaining
     */
    public function fontWeight(FontWeight $fontWeight): self
    {
        return $this->setConfig('font_weight', $fontWeight->value);
    }

    /**
     * Set how many columns this header cell spans
     *
     * @param int $span Number of columns to span (default: 1)
     * @return self For method chaining
     */
    public function colspan(int $span): self
    {
        if ($span < 1) {
            throw new \InvalidArgumentException("Colspan must be at least 1");
        }
        return $this->setConfig('colspan', $span);
    }

    /**
     * Get the parent header row
     *
     * @return TableHeaderRowBuilder
     */
    public function getHeaderRow(): TableHeaderRowBuilder
    {
        return $this->headerRow;
    }

    /**
     * {@inheritDoc}
     */
    /**
     * {@inheritDoc}
     */
    public function toJson(?int $order = null): array
    {
        // Get base config and filter nulls
        $config = array_filter($this->config, fn($value) => $value !== null);

        // Remove 'visible' if it's true (default value)
        if (isset($config['visible']) && $config['visible'] === true) {
            unset($config['visible']);
        }

        // Remove 'sortable' if it's false (default value)
        if (isset($config['sortable']) && $config['sortable'] === false) {
            unset($config['sortable']);
        }

        // Remove 'colspan' if it's 1 (default value)
        if (isset($config['colspan']) && $config['colspan'] === 1) {
            unset($config['colspan']);
        }

        // Remove 'font_weight' if it's 'bold' (default value)
        if (isset($config['font_weight']) && $config['font_weight'] === 'bold') {
            unset($config['font_weight']);
        }

        // Exclude additional keys
        $excludeKeys = $this->getExcludedJsonKeys();
        if (!empty($excludeKeys)) {
            $config = array_diff_key($config, array_flip($excludeKeys));
        }

        // CRITICAL: Include component ID in config for frontend lookups
        $config['_id'] = $this->id;

        return [$this->id => $config];
    }

    /**
     * Exclude 'name' from JSON output
     *
     * @return array List of keys to exclude
     */
    protected function getExcludedJsonKeys(): array
    {
        return ['name'];
    }
}
