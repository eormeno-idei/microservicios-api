<?php

namespace Idei\Usim\Services\Components;

use Illuminate\Support\Facades\Log;
use Idei\Usim\Services\Contracts\UIElement;

/**
 * Builder for Table Row UI components
 * 
 * Represents a row in a table. This component must be associated with a Table
 * and will be automatically added to the table's rows container.
 */
class TableRowBuilder extends UIComponent
{
    /** @var TableBuilder|null The parent table */
    private ?TableBuilder $table;

    /** @var array<TableCellBuilder> Array of cells in this row */
    private array $cellComponents = [];

    /**
     * Create a new table row
     * 
     * @param TableBuilder|null $table The parent table this row belongs to
     * @param string|null $name Optional name for the row
     */
    public function __construct(?TableBuilder $table = null, ?string $name = null)
    {
        $this->table = $table;
        parent::__construct($name);
    }

    protected function getDefaultConfig(): array
    {
        return [
            'cells' => [],
            'selected' => false,
            'style' => 'default',
            'empty' => null,
            'row' => null, // Row index for ordering
            'min_height' => null, // Minimum height in pixels
        ];
    }

    public function connectChild(UIElement $element): void
    {
        if ($element instanceof TableCellBuilder) {
            $this->addCell($element);
        }
    }

    /**
     * Set the row index (for ordering)
     * 
     * @param int $row Row index (0-based)
     * @return self
     */
    public function row(int $row): self
    {
        $this->setConfig('row', $row);
        return $this;
    }

    /**
     * Set minimum height for the row
     * 
     * @param int|string $height Minimum height in pixels or with units
     * @return self
     */
    public function minHeight(int|string $height): static
    {
        $this->setConfig('min_height', is_int($height) ? $height : $height);
        return $this;
    }

    /**
     * Get the row configuration (public accessor for cells)
     * 
     * @return array The row configuration
     */
    public function getRowConfig(): array
    {
        return $this->getConfig();
    }

    /**
     * Create and add a new cell to this row
     * 
     * @param string|null $name Optional name for the cell
     * @return TableCellBuilder The created cell
     */
    public function createCell(?string $name = null): TableCellBuilder
    {
        $cell = new TableCellBuilder($this, $name);
        $cell->setParent($this->id);
        $this->cellComponents[] = $cell;
        return $cell;
    }

    /**
     * Add an existing cell to this row
     * 
     * @param TableCellBuilder $cell The cell to add
     * @return self For method chaining
     */
    public function addCell(TableCellBuilder $cell): self
    {
        $cell->setParent($this->id);
        $this->cellComponents[] = $cell;
        return $this;
    }

    /**
     * Get all cell components
     * 
     * @return array<TableCellBuilder>
     */
    public function getCells(): array
    {
        return $this->cellComponents;
    }

    /**
     * Set the cells data for this row
     * Creates TableCellBuilder components automatically from the array
     * 
     * @param array $cells Array of cell values (strings, numbers, arrays, or UIComponent instances)
     * @return self For method chaining
     */
    public function cells(array $cells): self
    {
        // Store the raw data for backward compatibility
        $this->setConfig('cells', $cells);
        
        // Auto-create TableCellBuilder components from the array
        foreach ($cells as $index => $value) {
            $cell = $this->createCell("cell_$index");
            
            if ($value instanceof UIComponent && !($value instanceof UIContainer)) {
                // If it's a leaf component (not a container), add it as a child
                $cell->addChild($value);
            } elseif (is_array($value)) {
                // If it's an array (like build() output), store as raw data
                // For now, just store as text representation
                // In the future, this could be handled differently by the client
                $cell->text(json_encode($value));
            } else {
                // Otherwise, treat it as text (string, number, etc)
                $cell->text($value);
            }
        }
        
        return $this;
    }

    /**
     * Set a specific cell value by index
     * 
     * @param int $index The cell index (0-based)
     * @param mixed $value The cell value
     * @return self For method chaining
     */
    public function setCell(int $index, mixed $value): self
    {
        $this->config['cells'][$index] = $value;
        return $this;
    }

    /**
     * Mark this row as selected
     * 
     * @param bool $selected True to select, false otherwise
     * @return self For method chaining
     */
    public function selected(bool $selected = true): self
    {
        return $this->setConfig('selected', $selected);
    }

    /**
     * Set the row style
     * 
     * @param string $style The style name (default, primary, success, warning, danger, etc.)
     * @return self For method chaining
     */
    public function style(string $style): self
    {
        return $this->setConfig('style', $style);
    }

    /**
     * Mark this row as empty
     * Useful for placeholder rows or rows filled to meet minRows requirement
     * 
     * @param bool $empty True if the row is empty, false otherwise
     * @return self For method chaining
     */
    public function empty(bool $empty = true): self
    {
        return $this->setConfig('empty', $empty);
    }

    /**
     * Get the parent table
     * 
     * @return TableBuilder
     */
    public function getTable(): TableBuilder
    {
        return $this->table;
    }

    /**
     * {@inheritDoc}
     * 
     * Includes all cell components in the flat JSON structure
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

        // Exclude additional keys
        $excludeKeys = $this->getExcludedJsonKeys();
        if (!empty($excludeKeys)) {
            $config = array_diff_key($config, array_flip($excludeKeys));
        }

        // CRITICAL: Include component ID in config for frontend lookups
        $config['_id'] = $this->id;

        // Start with this row
        $result = [$this->id => $config];

        // Add all cell components
        foreach ($this->cellComponents as $cell) {
            $cellJson = $cell->toJson();
            $result = $result + $cellJson;
        }

        return $result;
    }

    /**
     * Exclude 'name' and 'cells' from JSON output
     * 
     * @return array List of keys to exclude
     */
    protected function getExcludedJsonKeys(): array
    {
        return ['name', 'cells'];
    }
}
