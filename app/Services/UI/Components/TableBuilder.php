<?php

namespace App\Services\UI\Components;

use App\Services\UI\DataTable\UsersDataTableModel;
use Illuminate\Support\Facades\Log;
use App\Services\UI\Contracts\UIElement;
use App\Services\UI\DataTable\AbstractDataTableModel;

/**
 * Table Builder
 *
 * A table with fixed dimensions (rows × columns) where:
 * - Structure is defined upfront
 * - All cells are initially empty
 * - Cells are identified by (row, col) coordinates
 * - Cell names follow pattern: "{row}_{col}"
 */
class TableBuilder extends UIComponent
{
    /** @var UIContainer The rows container */
    private UIContainer $rowsContainer;

    /** @var TableHeaderRowBuilder|null The header row (optional) */
    private ?TableHeaderRowBuilder $headerRow = null;

    /** @var AbstractDataTableModel|null The data model instance */
    private ?AbstractDataTableModel $model = null;

    /** @var int Number of data rows (excluding header) */
    private int $rows;

    /** @var int Number of columns */
    private int $cols;

    /** @var array Matrix of cell builders [row][col] */
    private array $cells = [];

    /** @var array Array of row builders */
    private array $rowBuilders = [];

    /** @var array Column width configuration [col => ['min' => int, 'max' => int]] */
    private array $columnWidths = [];

    /**
     * Create a new table
     *
     * @param string|null $name Table name
     * @param int $rows Number of data rows (0 for dynamic)
     * @param int $cols Number of columns (0 for dynamic)
     */
    public function __construct(?string $name = null, int $rows = 0, int $cols = 0)
    {
        parent::__construct($name);

        // Create the rows container
        $this->rowsContainer = new UIContainer('rows');
        $this->rowsContainer->setParent($this->id);
        $this->config['rows_container'] = $this->rowsContainer->getId();

        $this->rows = $rows;
        $this->cols = $cols;

        $this->setConfig('rows', $rows);
        $this->setConfig('cols', $cols);

        // Initialize empty cells if dimensions are provided
        if ($rows > 0 && $cols > 0) {
            $this->initializeEmptyCells();
        }
    }

    protected function getDefaultConfig(): array
    {
        return [
            'title' => '',
            'header_row' => null,
            'pagination' => [
                'enabled' => true,
                'per_page' => 10,
                'current_page' => 1,
                'total_items' => 0,
                'can_next' => true,
                'can_prev' => false,
                'total_pages' => 0,
            ],
            'rows' => 0,
            'cols' => 0,
            'align' => 'left', // Alignment: left, center, right
        ];
    }

    public function page(int $page): self
    {
        $pagination = $this->config['pagination'];

        if ($page < 1) {
            $page = 1;
        } elseif ($page > $pagination['total_pages']) {
            $page = $pagination['total_pages'];
        }

        // Update current page BEFORE calling updatePaginationData
        $pagination['current_page'] = $page;
        $this->setConfig('pagination', $pagination);
        $this->updatePaginationData();

        // Update table data for the new page
        $this->updateTableData();

        return $this;
    }

    /**
     * Update table data for the current page
     * Clears existing rows and fills them with data from the current page
     */
    public function updateTableData(): void
    {
        $model = $this->getModel();
        if (!$model) {
            return;
        }

        $pagination = $this->config['pagination'];
        $currentPage = $pagination['current_page'];
        $perPage = $pagination['per_page'];

        // Ensure $this->rows is set to perPage if it's 0
        if ($this->rows === 0) {
            $this->rows = $perPage;
            $this->setConfig('rows', $this->rows);
        }

        // Clear current rows
        $this->clearRows();

        // Fetch data for current page
        $formattedData = $model->getFormattedPageData($currentPage, $perPage);

        // Fill rows with new data and track actual row count
        $row = 0;
        foreach ($formattedData as $rowData) {
            if ($row >= $this->rows) {
                break;
            }
            $rowValues = array_values($rowData);
            $this->fillRow($row, $rowValues);
            $row++;
        }

        // Update $this->rows with the actual number of rows displayed
        // This is important for the last page which may have fewer rows than per_page
        $this->rows = $row;
        $this->setConfig('rows', $this->rows);
    }

    /**
     * Get the data model instance
     *
     * @return AbstractDataTableModel|null
     */
    public function getModel(): ?AbstractDataTableModel
    {
        if ($this->model === null) {
            $modelClass = $this->config['data_model'] ?? null;
            if ($modelClass) {
                $this->model = new $modelClass($this);
            }
        }
        return $this->model;
    }

    /**
     * Get the current configuration
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    public function updatePaginationData(): void
    {
        $model = $this->getModel();
        $totalItems = $model->getTotalItems();
        // por compatibilidad momentánea paara testing
        $this->config['total_items'] = $totalItems;

        $pagination = $this->config['pagination'];
        $currentPage = $pagination['current_page'];
        $perPage = $pagination['per_page'];

        $pagination['total_items'] = $totalItems;
        $pagination['total_pages'] = (int)ceil($totalItems / $perPage);
        $pagination['can_next'] = $currentPage < $pagination['total_pages'];
        $pagination['can_prev'] = $currentPage > 1;

        if ($currentPage > $pagination['total_pages']) {
            $currentPage = $pagination['total_pages'];
            $pagination['current_page'] = $currentPage;
        }

        $this->config['pagination'] = $pagination;
    }

    public function connectChild(UIElement $element): void
    {
        if ($element instanceof UIContainer) {
            if ($element->getName() === 'rows') {
                $this->rowsContainer = $element;
                $this->config['rows_container'] = $element->getId();
            }
            return;
        }

        if ($element instanceof TableHeaderRowBuilder) {
            $this->headerRow = $element;
            $this->config['header_row'] = $element->getId();
            return;
        }

        if ($element instanceof TableRowBuilder) {
            $this->addRow($element);
            return;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function postConnect(): void
    {
        $this->cols = $this->config['cols'];
        // After deserialization and reconnection, rebuild the row builders array
        // and cells matrix from the rowsContainer
        $this->reconstructRowBuilders();
        $this->reconstructCellsMatrix();

        // quiero logear el contenido de la celda [4][1] para verificar (use toString())
        // Log::debug("Contenido de la celda [4][1]: " . $this->cells[4][1]->toString());
    }

    /**
     * Reconstruct the rowBuilders array from rowsContainer's children
     *
     * After deserialization, $rowBuilders is empty so we need to rebuild it
     * by iterating through the rowsContainer's children.
     *
     * @return void
     */
    private function reconstructRowBuilders(): void
    {
        $this->rowBuilders = [];
        $rowIndex = 0;

        // Get all children from rowsContainer
        $children = $this->rowsContainer->getChildren();

        foreach ($children as $child) {
            // Only process TableRowBuilder instances
            if ($child instanceof TableRowBuilder) {
                $this->rowBuilders[$rowIndex] = $child;
                $rowIndex++;
            }
        }

        $this->rows = count($this->rowBuilders);
    }

    /**
     * Reconstruct the cells matrix from the component hierarchy
     *
     * Iterates through rows stored in $rowBuilders and extracts their cells
     * to rebuild the $this->cells two-dimensional array.
     *
     * This is called after deserialization when the component tree is fully
     * reconnected, ensuring we have access to all cell components.
     *
     * @return void
     */
    private function reconstructCellsMatrix(): void
    {
        $this->cells = [];

        // Iterate through all row builders and extract their cells
        foreach ($this->rowBuilders as $rowIndex => $rowBuilder) {
            $cellsInRow = $rowBuilder->getCells();

            // Log::debug("Reconstructing cells for row $rowIndex: " . $cellsInRow[1]->toString());
            if (!empty($cellsInRow)) {
                $this->cells[$rowIndex] = $cellsInRow;
            }
        }
        // Mostrar en debug los contenidos de las celdas reconstruidas
        // for ($i = 0; $i < count($this->cells); $i++) {
        //     for ($j = 0; $j < count($this->cells[$i]); $j++) {
        //         $cell = $this->cells[$i][$j];
        //         Log::debug("Celda [$i][$j]: " . $cell->getText());
        //     }
        // }
        // Log::debug("Reconstructed cells matrix with " . $this->getCell(0, 1)->getText());
    }

    /**
     * Create and return a header row for this table
     * Only one header row is allowed per table
     *
     * @param string|null $name Optional name for the header row
     * @return TableHeaderRowBuilder The header row builder
     */
    public function createHeaderRow(?string $name = null): TableHeaderRowBuilder
    {
        if ($this->headerRow !== null) {
            throw new \LogicException("Table already has a header row. Only one header row is allowed per table.");
        }

        $this->headerRow = new TableHeaderRowBuilder($this, $name ?? 'header');
        $this->headerRow->setParent($this->id);
        $this->config['header_row'] = $this->headerRow->getId();

        return $this->headerRow;
    }

    /**
     * Get the header row if it exists
     *
     * @return TableHeaderRowBuilder|null
     */
    public function getHeaderRow(): ?TableHeaderRowBuilder
    {
        return $this->headerRow;
    }

    /**
     * Create a new table row associated with this table
     * Automatically adds the row to the table
     *
     * @param string|null $name Optional name for the row
     * @return TableRowBuilder The new row builder
     */
    public function createRow(?string $name = null): TableRowBuilder
    {
        $row = new TableRowBuilder($this, $name);
        $this->addRow($row);
        return $row;
    }

    /**
     * Add a row component to this table
     *
     * @param TableRowBuilder $row The row to add
     * @return self For method chaining
     */
    public function addRow(TableRowBuilder $row): self
    {
        // Add the row to the rows container
        $this->rowsContainer->add($row);
        return $this;
    }

    /**
     * Get the rows container
     *
     * @return UIContainer
     */
    public function getRowsContainer(): UIContainer
    {
        return $this->rowsContainer;
    }

    /**
     * Initialize all cells as empty
     */
    private function initializeEmptyCells(): void
    {
        // Create header row
        $headerRow = $this->createHeaderRow('header');

        // Create empty header cells with column index
        for ($col = 0; $col < $this->cols; $col++) {
            $headerRow->createCell("header_$col")->text('')->column($col);
        }

        // Create data rows with empty cells
        for ($row = 0; $row < $this->rows; $row++) {
            $rowBuilder = $this->createRow("row_$row");
            $rowBuilder->row($row); // Set row index for ordering
            $this->rowBuilders[$row] = $rowBuilder;

            // Create empty cells for this row with column index
            $this->cells[$row] = [];
            for ($col = 0; $col < $this->cols; $col++) {
                $cellName = "{$row}_{$col}";
                $cell = $rowBuilder->createCell($cellName);
                $cell->text('')->column($col); // Empty by default with column index
                $this->cells[$row][$col] = $cell;
            }
        }
    }

    /**
     * Fill the header row with data
     *
     * @param array $data Array of header values (strings)
     * @return self
     */
    public function fillHeaderRow(array $data): self
    {
        $headerRow = $this->getHeaderRow();

        if (!$headerRow) {
            throw new \LogicException("Table dimensions must be set before filling header row");
        }

        $cells = $headerRow->getCells();

        for ($col = 0; $col < min(count($data), $this->cols); $col++) {
            if (isset($cells[$col])) {
                $cells[$col]->text($data[$col]);
            }
        }

        return $this;
    }

    /**
     * Clear all data rows (set all cells to empty strings)
     * This is useful for pagination or when reloading data
     *
     * @return self
     */
    public function clearRows(): self
    {
        for ($row = 0; $row < $this->rows; $row++) {
            for ($col = 0; $col < $this->cols; $col++) {
                $this->cells[$row][$col]->clearCell();
            }
        }

        return $this;
    }

    /**
     * Fill a data row with values
     *
     * @param int $row Row index (0-based)
     * @param array $data Array of cell data
     *                    - string: text content
     *                    - array with 'text': text content
     *                    - array with 'button': button config
     *                    - array with 'url_image': image config
     * @return self
     */
    public function fillRow(int $row, array $data): self
    {
        if ($row < 0 || $row >= $this->rows) {
            throw new \OutOfBoundsException("Row index $row is out of bounds (0-" . ($this->rows - 1) . ")");
        }

        for ($col = 0; $col < min(count($data), $this->cols); $col++) {
            $value = $data[$col];
            $cell = $this->cells[$row][$col];

            // Log::info("Filling cell at ($row, $col) with value: " . json_encode($value));

            if (is_string($value) || is_numeric($value)) {
                // Simple text (string or number)
                $cell->text((string)$value)->padding(4); // Compact padding for text cells
            } elseif (is_array($value)) {
                if (isset($value['text'])) {
                    $cell->text($value['text'])->padding(4); // Compact padding for text cells
                } elseif (isset($value['button'])) {
                    $cell->button($value['button'])->padding(2); // Even more compact for buttons
                } elseif (isset($value['url_image'])) {
                    $cell->urlImage(
                        $value['url_image'],
                        $value['alt'] ?? null,
                        $value['width'] ?? null,
                        $value['height'] ?? null
                    )->padding(2); // Compact for images
                }
            }
        }

        return $this;
    }

    /**
     * Get the cell ID for a specific row and column
     *
     * Format: tableId_row_col
     * Example: 88001_0_1 (table 88001, row 0, col 1)
     *
     * @param int $row Row index (0-based)
     * @param int $col Column index (0-based)
     * @return int Cell ID
     */
    public function getCellId(int $row, int $col): int
    {
        if ($row < 0 || $row >= $this->rows) {
            throw new \OutOfBoundsException("Row index $row is out of bounds");
        }

        if ($col < 0 || $col >= $this->cols) {
            throw new \OutOfBoundsException("Column index $col is out of bounds");
        }

        return $this->cells[$row][$col]->getId();
    }

    /**
     * Get a specific cell builder
     *
     * @param int $row Row index (0-based)
     * @param int $col Column index (0-based)
     * @return TableCellBuilder
     */
    public function getCell(int $row, int $col): TableCellBuilder
    {
        // Log::debug("Getting cell at ($row, $col) {$this->rows}x{$this->cols}");
        if ($row < 0 || $row >= $this->rows) {
            throw new \OutOfBoundsException("Row index $row is out of bounds");
        }

        if ($col < 0 || $col >= $this->cols) {
            throw new \OutOfBoundsException("Column index $col is out of bounds");
        }

        return $this->cells[$row][$col];
    }

    /**
     * Edit the content of a specific cell
     *
     * This is a convenience method to quickly update a cell's text content.
     * For more complex cell modifications (buttons, images, etc.), use getCell()
     * and modify the cell builder directly.
     *
     * @param int $row Row index (0-based)
     * @param int $col Column index (0-based)
     * @param string $text New text content for the cell
     * @return self For method chaining
     */
    public function editCell(int $row, int $col, string $text): self
    {
        $cell = $this->getCell($row, $col);
        $cell->text($text);
        return $this;
    }

    /**
     * Set the table title
     *
     * @param string $title The table title
     * @return self
     */
    public function title(string $title): self
    {
        return $this->setConfig('title', $title);
    }

    /**
     * Set the table alignment within its parent container
     *
     * @param string $align Alignment: 'left', 'center', or 'right'
     * @return self
     */
    public function align(string $align): self
    {
        if (!in_array($align, ['left', 'center', 'right'])) {
            throw new \InvalidArgumentException("Invalid alignment: $align. Use 'left', 'center', or 'right'.");
        }

        return $this->setConfig('align', $align);
    }

    /**
     * Set minimum height for all rows
     *
     * @param int $height Minimum height in pixels
     * @return self
     */
    public function rowMinHeight(int $height): self
    {
        // Apply min height to all existing rows
        foreach ($this->rowBuilders as $row) {
            $row->minHeight($height);
        }

        return $this;
    }

    /**
     * Set width constraints for a specific column
     *
     * @param int $col Column index (0-based)
     * @param int|null $minWidth Minimum width in pixels (null = no min)
     * @param int|null $maxWidth Maximum width in pixels (null = no max)
     * @return self
     */
    public function columnWidth(int $col, ?int $minWidth = null, ?int $maxWidth = null): self
    {
        if ($col < 0 || $col >= $this->cols) {
            throw new \OutOfBoundsException("Column index $col is out of bounds (0-" . ($this->cols - 1) . ")");
        }

        $this->columnWidths[$col] = [
            'min' => $minWidth,
            'max' => $maxWidth,
        ];

        // Apply width to all cells in this column (header + data rows)
        if ($this->headerRow) {
            $headerCells = $this->headerRow->getCells();
            if (isset($headerCells[$col])) {
                $headerCells[$col]->widthConstraints($minWidth, $maxWidth);
            }
        }

        // Apply to all data row cells in this column
        for ($row = 0; $row < $this->rows; $row++) {
            if (isset($this->cells[$row][$col])) {
                $this->cells[$row][$col]->width($minWidth, $maxWidth);
            }
        }

        return $this;
    }

    /**
     * Set width constraints for all columns at once
     *
     * @param array $widths Array of width configs: [[min, max], [min, max], ...]
     * @return self
     */
    public function columnWidths(array $widths): self
    {
        foreach ($widths as $col => $width) {
            $min = $width['min'] ?? $width[0] ?? null;
            $max = $width['max'] ?? $width[1] ?? null;
            $this->columnWidth($col, $min, $max);
        }

        return $this;
    }

    /**
     * Set pagination page size. If perPage is 0, pagination is disabled.
     *
     * @param int $perPage Number of items per page
     * @return self
     */
    public function pagination(int $perPage = 10): self
    {
        $pagination = $this->config['pagination'];
        $pagination['enabled'] = $perPage > 0;
        $pagination['per_page'] = $perPage;
        $this->setConfig('pagination', $pagination);
        return $this;
    }

    public function getPaginationData(): array
    {
        return $this->config['pagination'];
    }

    /**
     * Configure table using a data model
     * The data model should provide methods like:
     * - getColumns(): array of column definitions
     * - getPaginationInfo(): pagination information
     * - getFormattedPageData(): formatted data for current page
     *
     * @param mixed $dataModel The data model instance
     * @return self
     */
    //public function dataModel(AbstractDataTableModel $dataModel): self
    public function dataModel(string $dataModel): self
    {
        // asegura que $dataModel es una :class de tipo AbstractDataTableModel
        if (!is_subclass_of($dataModel, AbstractDataTableModel::class)) {
            throw new \InvalidArgumentException("Data model must be a subclass of AbstractDataTableModel");
        }
        // Set the data model class in config
        $this->setConfig('data_model', $dataModel);
        $this->model = new $dataModel($this);

        // Get columns and pagination configuration
        $columns = null;
        $columns = $this->model->getColumns();
        $this->cols = count($columns);
        $this->setConfig('cols', $this->cols);

        $this->updatePaginationData();
        $this->rows = $this->config['pagination']['per_page'];
        $this->setConfig('rows', $this->rows);

        // Initialize cells now that we have dimensions
        if ($this->rows > 0 && $this->cols > 0) {
            $this->initializeEmptyCells();

            // Configure column widths AFTER cells are initialized
            if ($columns) {
                $columnIndex = 0;
                foreach ($columns as $column) {
                    if (isset($column['width'])) {
                        $this->columnWidth($columnIndex, $column['width'][0], $column['width'][1]);
                    }
                    $columnIndex++;
                }
            }

            // Fill header row
            if ($columns) {
                $headers = array_column($columns, 'label');
                $this->fillHeaderRow($headers);
            }

            // Fill data rows
            $pagination = $this->config['pagination'];
            $currentPage = $pagination['current_page'];
            $perPage = $pagination['per_page'];

            $formattedData = $this->model->getFormattedPageData($currentPage, $perPage);
            $row = 0;
            foreach ($formattedData as $rowData) {
                if ($row >= $this->rows) {
                    break;
                }

                // Convert associative array to indexed array for fillRow
                $rowValues = array_values($rowData);
                $this->fillRow($row, $rowValues);
                $row++;
            }
        }

        return $this;
    }


    /**
     * Get table dimensions
     *
     * @return array ['rows' => int, 'cols' => int]
     */
    public function getDimensions(): array
    {
        return [
            'rows' => $this->rows,
            'cols' => $this->cols,
        ];
    }

    /**
     * Override toJson to include the rows container and header row in flat structure
     */
    public function toJson(?int $order = null): array
    {
        // Get the table's JSON
        $tableJson = parent::toJson();

        // Get the rows container's JSON
        $rowsContainerJson = $this->rowsContainer->toJson();

        // Start with table + rows container
        $result = $tableJson + $rowsContainerJson;

        // Add header row if it exists
        if ($this->headerRow !== null) {
            $headerRowJson = $this->headerRow->toJson();
            $result = $result + $headerRowJson;
        }

        return $result;
    }

    protected function getExcludedJsonKeys(): array
    {
        // Don't exclude 'name' - we need it for cell identification
        return parent::getExcludedJsonKeys();
    }
}
