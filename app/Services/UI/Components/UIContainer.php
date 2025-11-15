<?php

namespace App\Services\UI\Components;

use App\Services\UI\Enums\AlignItems;
use App\Services\UI\Enums\LayoutType;
use App\Services\UI\Contracts\UIElement;
use App\Services\UI\Enums\JustifyContent;
use App\Services\UI\Support\UIIdGenerator;

/**
 * Composite UI Container that can hold and manage child UI elements
 *
 * This class implements the Composite pattern, allowing UI elements to be
 * organized in a tree structure. It provides methods to add, remove, update,
 * and find child elements, as well as recursive JSON serialization.
 */
class UIContainer implements UIElement
{
    protected int $id;
    protected string $type = 'container';
    protected ?string $name = null;
    protected int|string|null $parent = null;
    protected array $config = [];

    /** @var array<string, UIElement> Map of element ID to UIElement instance */
    protected array $children = [];

    /** @var array|null Legacy elements array for backward compatibility */
    public ?array $legacyElements = null;

    public function __construct(?string $name = null, ?string $context = null)
    {
        $this->name = $name;

        if ($context === null) {
            // Detectar automáticamente el contexto desde la clase que invoca
            $context = $this->detectCallingContext();
        }

        // Generar ID según si tiene nombre o no
        if ($this->name !== null) {
            // ID DETERMINÍSTICO: Basado en contexto + nombre
            $this->id = $this->generateDeterministicId($context, $this->name);
        } else {
            // ID AUTO-INCREMENT: Para contenedores temporales
            $this->id = UIIdGenerator::generate($context);
        }

        $this->config = [
            'type' => $this->type,
            'name' => $this->name,
            'visible' => true,
            'layout' => LayoutType::VERTICAL->value,
            'parent' => null,
            'title' => null,
            'root' => false,

            // Flexbox properties
            'flex_direction' => null,
            'justify_content' => null,
            'align_items' => null,
            'align_content' => null,
            'flex_wrap' => null,
            'flex_grow' => null,
            'flex_shrink' => null,
            'flex_basis' => null,
            'order' => null,

            // Grid properties
            'grid_template_columns' => null,
            'grid_template_rows' => null,
            'grid_template_areas' => null,
            'grid_auto_columns' => null,
            'grid_auto_rows' => null,
            'grid_auto_flow' => null,
            'grid_column' => null,
            'grid_row' => null,
            'grid_area' => null,

            // Gap/Spacing
            'gap' => null,
            'row_gap' => null,
            'column_gap' => null,

            // Padding
            'padding' => null,
            'padding_top' => null,
            'padding_right' => null,
            'padding_bottom' => null,
            'padding_left' => null,

            // Margin
            'margin' => null,
            'margin_top' => null,
            'margin_right' => null,
            'margin_bottom' => null,
            'margin_left' => null,

            // Sizing
            'width' => null,
            'height' => null,
            'min_width' => null,
            'min_height' => null,
            'max_width' => null,
            'max_height' => null,

            // Visual styling
            'background_color' => null,
            'background_image' => null,
            'background_size' => null,
            'background_position' => null,
            'border' => null,
            'border_radius' => null,
            'box_shadow' => null,
            'opacity' => null,

            // Position
            'position' => null,
            'top' => null,
            'right' => null,
            'bottom' => null,
            'left' => null,
            'z_index' => null,

            // Overflow & Scroll
            'overflow' => null,
            'overflow_x' => null,
            'overflow_y' => null,
            'scroll_behavior' => null,

            // Display
            'display' => null,

            // Responsive
            'responsive' => [],
            'breakpoints' => [],
            'hide_on' => [],
            'show_on' => [],

            // Custom
            'custom_class' => null,
            'custom_style' => null,
            'data_attributes' => [],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function isContainer(): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public static function deserialize(int $id, array $data): UIContainer
    {
        $container = new self();
        $container->id = $id;
        $container->type = $data['type'] ?? 'container';
        $container->name = $data['name'] ?? null;
        $container->parent = $data['parent'] ?? null;
        $container->config = array_merge($container->config, $data);
        return $container;
    }

    /**
     * {@inheritDoc}
     */
    public function connectChild(UIElement $element): void
    {
        $this->add($element);
    }

    /**
     * {@inheritDoc}
     */
    public function postConnect(): void
    {
        // No-op for container
    }

    public function toString(): string
    {
        return "UIContainer(id={$this->id}, name={$this->name})";
    }

    /**
     * {@inheritDoc}
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the container name
     *
     * @return string|null Container name or null if not set
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * {@inheritDoc}
     */
    public function isVisible(): bool
    {
        return $this->config['visible'] ?? true;
    }

    /**
     * {@inheritDoc}
     */
    public function setVisible(bool $visible): self
    {
        $this->config['visible'] = $visible;
        return $this;
    }

    /**
     * Fluent API for setting visibility
     */
    public function visible(bool $visible = true): self
    {
        return $this->setVisible($visible);
    }

    /**
     * Set the root flag for this container
     *
     * @param bool $root True if this is the root container, false otherwise
     * @return self For method chaining
     */
    public function root(bool $root = true): self
    {
        $this->config['root'] = $root;
        return $this;
    }

    /**
     * Check if this container is marked as root
     *
     * @return bool True if root, false otherwise
     */
    public function isRoot(): bool
    {
        return $this->config['root'] ?? false;
    }

    /**
     * Set the name for this container
     *
     * @param string|null $name The container name
     * @return self For method chaining
     */
    public function name(?string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Set the title for this container
     *
     * @param string|null $title The container title
     * @return self For method chaining
     */
    public function setName(string|null $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getParent(): int|string|null
    {
        return $this->parent;
    }

    /**
     * {@inheritDoc}
     */
    public function setParent(int|string|null $parent): self
    {
        $this->parent = $parent;
        $this->config['parent'] = $parent;
        return $this;
    }

    /**
     * Set the parent reference for this container
     *
     * @param int|string|null $parent The parent (int = parent ID, string = parent name, null = delete)
     * @return self For method chaining
     */
    public function parent(int|string|null $parent): self
    {
        return $this->setParent($parent);
    }

    /**
     * Set the layout type for this container
     *
     * @param LayoutType $layout The layout type (VERTICAL or HORIZONTAL)
     * @return self For method chaining
     */
    public function layout(LayoutType $layout): self
    {
        $this->config['layout'] = $layout->value;
        return $this;
    }

    /**
     * Set the title for this container
     *
     * @param string $title The container title
     * @return self For method chaining
     */
    public function title(string $title): self
    {
        $this->config['title'] = $title;
        return $this;
    }

    /**
     * Add a child element to this container
     *
     * @param UIElement $element The element to add
     * @return self For method chaining
     * @throws \InvalidArgumentException If element with same ID already exists
     */
    public function add(UIElement $element): self
    {
        $elementId = $element->getId();

        if (isset($this->children[$elementId])) {
            throw new \InvalidArgumentException(
                "Element with ID '{$elementId}' already exists in container '{$this->id}'"
            );
        }

        // Automatically set the child's parent to this container's ID
        $element->setParent($this->id);

        $this->children[$elementId] = $element;
        return $this;
    }

    /**
     * Add multiple child elements to this container
     *
     * @param array<UIElement> $elements Array of elements to add
     * @return self For method chaining
     */
    public function addMany(array $elements): self
    {
        foreach ($elements as $element) {
            if ($element instanceof UIElement) {
                $this->add($element);
            }
        }
        return $this;
    }

    /**
     * Remove a child element from this container by ID
     *
     * @param int|string $elementId The ID of the element to remove
     * @return self For method chaining
     * @throws \InvalidArgumentException If element not found
     */
    public function remove(int|string $elementId): self
    {
        $elementId = (string)$elementId;
        if (!isset($this->children[$elementId])) {
            throw new \InvalidArgumentException(
                "Element with ID '{$elementId}' not found in container '{$this->id}'"
            );
        }

        // Mark the element for deletion by setting parent to null
        $this->children[$elementId]->setParent(null);

        unset($this->children[$elementId]);
        return $this;
    }

    /**
     * Remove a child element from this container by ID (silent version)
     * Returns true if element was removed, false if not found
     *
     * @param int|string $elementId The ID of the element to remove
     * @return bool True if removed, false if not found
     */
    public function tryRemove(int|string $elementId): bool
    {
        $elementId = (string)$elementId;
        if (isset($this->children[$elementId])) {
            // Mark the element for deletion by setting parent to null
            $this->children[$elementId]->setParent(null);

            unset($this->children[$elementId]);
            return true;
        }
        return false;
    }

    /**
     * Update a child element by replacing it with a new element
     *
     * @param int|string $elementId The ID of the element to update
     * @param UIElement $newElement The new element to replace with
     * @return self For method chaining
     * @throws \InvalidArgumentException If element not found or IDs don't match
     */
    public function update(int|string $elementId, UIElement $newElement): self
    {
        $elementId = (string)$elementId;
        if (!isset($this->children[$elementId])) {
            throw new \InvalidArgumentException(
                "Element with ID '{$elementId}' not found in container '{$this->id}'"
            );
        }

        if ((string)$newElement->getId() !== $elementId) {
            throw new \InvalidArgumentException(
                "New element ID '{$newElement->getId()}' does not match target ID '{$elementId}'"
            );
        }

        $this->children[$elementId] = $newElement;
        return $this;
    }

    /**
     * Find a child element by ID (searches recursively through the tree)
     *
     * @param int|string $elementId The ID of the element to find
     * @return UIElement|null The found element, or null if not found
     */
    public function find(int|string $elementId): ?UIElement
    {
        $elementId = (string)$elementId;
        // Check direct children first
        if (isset($this->children[$elementId])) {
            return $this->children[$elementId];
        }

        // Search recursively in child containers
        foreach ($this->children as $child) {
            if ($child instanceof UIContainer) {
                $found = $child->find($elementId);
                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }

    /**
     * Check if this container has a specific child element
     *
     * @param int|string $elementId The ID of the element to check
     * @return bool True if element exists as direct child, false otherwise
     */
    public function has(int|string $elementId): bool
    {
        $elementId = (string)$elementId;
        return isset($this->children[$elementId]);
    }

    /**
     * Get all direct child elements
     *
     * @return array<UIElement> Array of child elements
     */
    public function getChildren(): array
    {
        return array_values($this->children);
    }

    /**
     * Get the number of direct children
     *
     * @return int The number of children
     */
    public function count(): int
    {
        return count($this->children);
    }

    /**
     * Remove all child elements
     *
     * @return self For method chaining
     */
    public function clear(): self
    {
        $this->children = [];
        return $this;
    }

    // ========================================================================
    // FLEXBOX METHODS
    // ========================================================================

    /**
     * Set flex direction
     *
     * @param string $direction Direction: row, row-reverse, column, column-reverse
     * @return self For method chaining
     */
    public function flexDirection(string $direction): self
    {
        $this->config['flex_direction'] = $direction;
        return $this;
    }

    /**
     * Set justify content (main axis alignment)
     *
     * @param JustifyContent|string $justify Alignment value
     * @return self For method chaining
     */
    public function justifyContent(JustifyContent|string $justify): self
    {
        $this->config['justify_content'] = $justify instanceof JustifyContent
            ? $justify->value
            : $justify;
        return $this;
    }

    /**
     * Set align items (cross axis alignment)
     *
     * @param AlignItems|string $align Alignment value
     * @return self For method chaining
     */
    public function alignItems(AlignItems|string $align): self
    {
        $this->config['align_items'] = $align instanceof AlignItems
            ? $align->value
            : $align;
        return $this;
    }

    /**
     * Set align content (multi-line alignment)
     *
     * @param string $align Values: flex-start, flex-end, center, space-between, space-around, stretch
     * @return self For method chaining
     */
    public function alignContent(string $align): self
    {
        $this->config['align_content'] = $align;
        return $this;
    }

    /**
     * Set flex wrap
     *
     * @param string $wrap Values: nowrap, wrap, wrap-reverse
     * @return self For method chaining
     */
    public function flexWrap(string $wrap): self
    {
        $this->config['flex_wrap'] = $wrap;
        return $this;
    }

    /**
     * Set flex grow factor
     *
     * @param int|float $grow Grow factor (typically 0-1)
     * @return self For method chaining
     */
    public function flexGrow(int|float $grow): self
    {
        $this->config['flex_grow'] = $grow;
        return $this;
    }

    /**
     * Set flex shrink factor
     *
     * @param int|float $shrink Shrink factor (typically 0-1)
     * @return self For method chaining
     */
    public function flexShrink(int|float $shrink): self
    {
        $this->config['flex_shrink'] = $shrink;
        return $this;
    }

    /**
     * Set flex basis (initial size)
     *
     * @param string $basis Size value (px, %, auto, etc)
     * @return self For method chaining
     */
    public function flexBasis(string $basis): self
    {
        $this->config['flex_basis'] = $basis;
        return $this;
    }

    /**
     * Set order for flex item
     *
     * @param int $order Order value
     * @return self For method chaining
     */
    public function order(int $order): self
    {
        $this->config['order'] = $order;
        return $this;
    }

    // ========================================================================
    // GRID METHODS
    // ========================================================================

    /**
     * Set grid template columns
     *
     * @param string $template Template string (e.g., '1fr 2fr', 'repeat(3, 1fr)', '100px auto')
     * @return self For method chaining
     */
    public function gridTemplateColumns(string $template): self
    {
        $this->config['grid_template_columns'] = $template;
        return $this;
    }

    /**
     * Set grid template rows
     *
     * @param string $template Template string
     * @return self For method chaining
     */
    public function gridTemplateRows(string $template): self
    {
        $this->config['grid_template_rows'] = $template;
        return $this;
    }

    /**
     * Set grid template areas
     *
     * @param array|string $areas Area names or array of area strings
     * @return self For method chaining
     */
    public function gridTemplateAreas(array|string $areas): self
    {
        if (is_array($areas)) {
            $areas = implode(' ', array_map(fn($a) => '"' . $a . '"', $areas));
        }
        $this->config['grid_template_areas'] = $areas;
        return $this;
    }

    /**
     * Set grid auto columns
     *
     * @param string $size Size value (auto, minmax(), etc)
     * @return self For method chaining
     */
    public function gridAutoColumns(string $size): self
    {
        $this->config['grid_auto_columns'] = $size;
        return $this;
    }

    /**
     * Set grid auto rows
     *
     * @param string $size Size value
     * @return self For method chaining
     */
    public function gridAutoRows(string $size): self
    {
        $this->config['grid_auto_rows'] = $size;
        return $this;
    }

    /**
     * Set grid auto flow
     *
     * @param string $flow Values: row, column, row dense, column dense
     * @return self For method chaining
     */
    public function gridAutoFlow(string $flow): self
    {
        $this->config['grid_auto_flow'] = $flow;
        return $this;
    }

    /**
     * Set grid column span/position
     *
     * @param string $column Column value (e.g., '1 / 3', 'span 2')
     * @return self For method chaining
     */
    public function gridColumn(string $column): self
    {
        $this->config['grid_column'] = $column;
        return $this;
    }

    /**
     * Set grid row span/position
     *
     * @param string $row Row value
     * @return self For method chaining
     */
    public function gridRow(string $row): self
    {
        $this->config['grid_row'] = $row;
        return $this;
    }

    /**
     * Set grid area name
     *
     * @param string $area Area name
     * @return self For method chaining
     */
    public function gridArea(string $area): self
    {
        $this->config['grid_area'] = $area;
        return $this;
    }

    // ========================================================================
    // GAP/SPACING METHODS
    // ========================================================================

    /**
     * Set gap (spacing between children)
     *
     * @param string $gap Gap value (px, rem, etc)
     * @return self For method chaining
     */
    public function gap(string $gap): self
    {
        $this->config['gap'] = $gap;
        return $this;
    }

    /**
     * Set row gap
     *
     * @param string $gap Row gap value
     * @return self For method chaining
     */
    public function rowGap(string $gap): self
    {
        $this->config['row_gap'] = $gap;
        return $this;
    }

    /**
     * Set column gap
     *
     * @param string $gap Column gap value
     * @return self For method chaining
     */
    public function columnGap(string $gap): self
    {
        $this->config['column_gap'] = $gap;
        return $this;
    }

    // ========================================================================
    // PADDING METHODS
    // ========================================================================

    /**
     * Set padding (all sides)
     *
     * @param string $padding Padding value
     * @return self For method chaining
     */
    public function padding(string $padding): self
    {
        $this->config['padding'] = $padding;
        return $this;
    }

    /**
     * Set padding for individual sides
     *
     * @param string|null $top Top padding
     * @param string|null $right Right padding
     * @param string|null $bottom Bottom padding
     * @param string|null $left Left padding
     * @return self For method chaining
     */
    public function paddingEach(?string $top = null, ?string $right = null, ?string $bottom = null, ?string $left = null): self
    {
        if ($top !== null) $this->config['padding_top'] = $top;
        if ($right !== null) $this->config['padding_right'] = $right;
        if ($bottom !== null) $this->config['padding_bottom'] = $bottom;
        if ($left !== null) $this->config['padding_left'] = $left;
        return $this;
    }

    /**
     * Set top padding
     *
     * @param string $padding Padding value
     * @return self For method chaining
     */
    public function paddingTop(string $padding): self
    {
        $this->config['padding_top'] = $padding;
        return $this;
    }

    /**
     * Set right padding
     *
     * @param string $padding Padding value
     * @return self For method chaining
     */
    public function paddingRight(string $padding): self
    {
        $this->config['padding_right'] = $padding;
        return $this;
    }

    /**
     * Set bottom padding
     *
     * @param string $padding Padding value
     * @return self For method chaining
     */
    public function paddingBottom(string $padding): self
    {
        $this->config['padding_bottom'] = $padding;
        return $this;
    }

    /**
     * Set left padding
     *
     * @param string $padding Padding value
     * @return self For method chaining
     */
    public function paddingLeft(string $padding): self
    {
        $this->config['padding_left'] = $padding;
        return $this;
    }

    // ========================================================================
    // MARGIN METHODS
    // ========================================================================

    /**
     * Set margin (all sides)
     *
     * @param string $margin Margin value
     * @return self For method chaining
     */
    public function margin(string $margin): static
    {
        $this->config['margin'] = $margin;
        return $this;
    }

    /**
     * Set margin for individual sides
     *
     * @param string|null $top Top margin
     * @param string|null $right Right margin
     * @param string|null $bottom Bottom margin
     * @param string|null $left Left margin
     * @return self For method chaining
     */
    public function marginEach(?string $top = null, ?string $right = null, ?string $bottom = null, ?string $left = null): static
    {
        if ($top !== null) $this->config['margin_top'] = $top;
        if ($right !== null) $this->config['margin_right'] = $right;
        if ($bottom !== null) $this->config['margin_bottom'] = $bottom;
        if ($left !== null) $this->config['margin_left'] = $left;
        return $this;
    }

    /**
     * Set top margin
     *
     * @param string $margin Margin value
     * @return self For method chaining
     */
    public function marginTop(string $margin): static
    {
        $this->config['margin_top'] = $margin;
        return $this;
    }

    /**
     * Set right margin
     *
     * @param string $margin Margin value
     * @return self For method chaining
     */
    public function marginRight(string $margin): static
    {
        $this->config['margin_right'] = $margin;
        return $this;
    }

    /**
     * Set bottom margin
     *
     * @param string $margin Margin value
     * @return self For method chaining
     */
    public function marginBottom(string $margin): static
    {
        $this->config['margin_bottom'] = $margin;
        return $this;
    }

    /**
     * Set left margin
     *
     * @param string $margin Margin value
     * @return self For method chaining
     */
    public function marginLeft(string $margin): static
    {
        $this->config['margin_left'] = $margin;
        return $this;
    }

    // ========================================================================
    // SIZING METHODS
    // ========================================================================

    /**
     * Set width
     *
     * @param string $width Width value (px, %, vh, auto, etc)
     * @return self For method chaining
     */
    public function width(string $width): static
    {
        $this->config['width'] = $width;
        return $this;
    }

    /**
     * Set height
     *
     * @param string $height Height value
     * @return self For method chaining
     */
    public function height(string $height): static
    {
        $this->config['height'] = $height;
        return $this;
    }

    /**
     * Set minimum width
     *
     * @param string $width Min width value
     * @return self For method chaining
     */
    public function minWidth(string $width): static
    {
        $this->config['min_width'] = $width;
        return $this;
    }

    /**
     * Set minimum height
     *
     * @param string $height Min height value
     * @return self For method chaining
     */
    public function minHeight(string $height): static
    {
        $this->config['min_height'] = $height;
        return $this;
    }

    /**
     * Set maximum width
     *
     * @param string $width Max width value
     * @return self For method chaining
     */
    public function maxWidth(string $width): static
    {
        $this->config['max_width'] = $width;
        return $this;
    }

    /**
     * Set maximum height
     *
     * @param string $height Max height value
     * @return self For method chaining
     */
    public function maxHeight(string $height): static
    {
        $this->config['max_height'] = $height;
        return $this;
    }

    // ========================================================================
    // VISUAL STYLING METHODS
    // ========================================================================

    /**
     * Set background color
     *
     * @param string $color Color value (hex, rgb, named)
     * @return self For method chaining
     */
    public function backgroundColor(string $color): self
    {
        $this->config['background_color'] = $color;
        return $this;
    }

    /**
     * Set background image
     *
     * @param string $url Image URL
     * @return self For method chaining
     */
    public function backgroundImage(string $url): self
    {
        $this->config['background_image'] = $url;
        return $this;
    }

    /**
     * Set background size
     *
     * @param string $size Size value (cover, contain, auto, etc)
     * @return self For method chaining
     */
    public function backgroundSize(string $size): self
    {
        $this->config['background_size'] = $size;
        return $this;
    }

    /**
     * Set background position
     *
     * @param string $position Position value (center, top, bottom, etc)
     * @return self For method chaining
     */
    public function backgroundPosition(string $position): self
    {
        $this->config['background_position'] = $position;
        return $this;
    }

    /**
     * Set border
     *
     * @param string $border Border value (e.g., '1px solid #ccc')
     * @return self For method chaining
     */
    public function border(string $border): self
    {
        $this->config['border'] = $border;
        return $this;
    }

    /**
     * Set border radius
     *
     * @param string $radius Radius value
     * @return self For method chaining
     */
    public function borderRadius(string $radius): self
    {
        $this->config['border_radius'] = $radius;
        return $this;
    }

    /**
     * Set box shadow
     *
     * @param string $shadow Shadow value
     * @return self For method chaining
     */
    public function boxShadow(string $shadow): self
    {
        $this->config['box_shadow'] = $shadow;
        return $this;
    }

    /**
     * Set opacity
     *
     * @param float $opacity Opacity value (0-1)
     * @return self For method chaining
     */
    public function opacity(float $opacity): self
    {
        $this->config['opacity'] = $opacity;
        return $this;
    }

    /**
     * Center the container horizontally using margin auto
     *
     * @return self For method chaining
     */
    public function centerHorizontal(): self
    {
        $this->config['margin_left'] = 'auto';
        $this->config['margin_right'] = 'auto';
        return $this;
    }

    // ========================================================================
    // POSITION METHODS
    // ========================================================================

    /**
     * Set position type
     *
     * @param string $position Position value (static, relative, absolute, fixed, sticky)
     * @return self For method chaining
     */
    public function position(string $position): self
    {
        $this->config['position'] = $position;
        return $this;
    }

    /**
     * Set top position
     *
     * @param string $top Top value
     * @return self For method chaining
     */
    public function top(string $top): self
    {
        $this->config['top'] = $top;
        return $this;
    }

    /**
     * Set right position
     *
     * @param string $right Right value
     * @return self For method chaining
     */
    public function right(string $right): self
    {
        $this->config['right'] = $right;
        return $this;
    }

    /**
     * Set bottom position
     *
     * @param string $bottom Bottom value
     * @return self For method chaining
     */
    public function bottom(string $bottom): self
    {
        $this->config['bottom'] = $bottom;
        return $this;
    }

    /**
     * Set left position
     *
     * @param string $left Left value
     * @return self For method chaining
     */
    public function left(string $left): self
    {
        $this->config['left'] = $left;
        return $this;
    }

    /**
     * Set z-index
     *
     * @param int $zIndex Z-index value
     * @return self For method chaining
     */
    public function zIndex(int $zIndex): self
    {
        $this->config['z_index'] = $zIndex;
        return $this;
    }

    // ========================================================================
    // OVERFLOW & SCROLL METHODS
    // ========================================================================

    /**
     * Set overflow behavior
     *
     * @param string $overflow Overflow value (visible, hidden, scroll, auto)
     * @return self For method chaining
     */
    public function overflow(string $overflow): self
    {
        $this->config['overflow'] = $overflow;
        return $this;
    }

    /**
     * Set horizontal overflow
     *
     * @param string $overflow Overflow value
     * @return self For method chaining
     */
    public function overflowX(string $overflow): self
    {
        $this->config['overflow_x'] = $overflow;
        return $this;
    }

    /**
     * Set vertical overflow
     *
     * @param string $overflow Overflow value
     * @return self For method chaining
     */
    public function overflowY(string $overflow): self
    {
        $this->config['overflow_y'] = $overflow;
        return $this;
    }

    /**
     * Set scroll behavior
     *
     * @param string $behavior Scroll behavior (auto, smooth)
     * @return self For method chaining
     */
    public function scrollBehavior(string $behavior): self
    {
        $this->config['scroll_behavior'] = $behavior;
        return $this;
    }

    // ========================================================================
    // DISPLAY METHOD
    // ========================================================================

    /**
     * Set display property
     *
     * @param string $display Display value (block, inline, inline-block, flex, grid, none)
     * @return self For method chaining
     */
    public function display(string $display): self
    {
        $this->config['display'] = $display;
        return $this;
    }

    // ========================================================================
    // RESPONSIVE METHODS
    // ========================================================================

    /**
     * Set responsive configuration for different breakpoints
     *
     * @param array $config Responsive configuration [breakpoint => config]
     * @return self For method chaining
     */
    public function responsive(array $config): self
    {
        $this->config['responsive'] = $config;
        return $this;
    }

    /**
     * Hide container on specific breakpoints
     *
     * @param array $breakpoints Breakpoints to hide on (mobile, tablet, desktop)
     * @return self For method chaining
     */
    public function hideOn(array $breakpoints): self
    {
        $this->config['hide_on'] = $breakpoints;
        return $this;
    }

    /**
     * Show container only on specific breakpoints
     *
     * @param array $breakpoints Breakpoints to show on
     * @return self For method chaining
     */
    public function showOn(array $breakpoints): self
    {
        $this->config['show_on'] = $breakpoints;
        return $this;
    }

    // ========================================================================
    // CUSTOM STYLING METHODS
    // ========================================================================

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

    // ========================================================================
    // HELPER METHODS (SHORTCUTS FOR COMMON PATTERNS)
    // ========================================================================

    /**
     * Configure as flex row layout
     *
     * @return self For method chaining
     */
    public function flexRow(): self
    {
        return $this->layout(LayoutType::FLEX)->flexDirection('row');
    }

    /**
     * Configure as flex column layout
     *
     * @return self For method chaining
     */
    public function flexColumn(): self
    {
        return $this->layout(LayoutType::FLEX)->flexDirection('column');
    }

    /**
     * Center content (flex justify-content and align-items center)
     *
     * @return self For method chaining
     */
    public function centerContent(): self
    {
        return $this->justifyContent('center')->alignItems('center');
    }

    /**
     * Quick grid setup
     *
     * @param string $columns Grid columns template
     * @param string|null $rows Grid rows template
     * @return self For method chaining
     */
    public function grid(string $columns, ?string $rows = null): self
    {
        $this->layout(LayoutType::GRID)->gridTemplateColumns($columns);
        if ($rows !== null) {
            $this->gridTemplateRows($rows);
        }
        return $this;
    }

    /**
     * Create equal column grid
     *
     * @param int $columns Number of columns
     * @return self For method chaining
     */
    public function gridColumns(int $columns): self
    {
        return $this->grid("repeat($columns, 1fr)");
    }

    /**
     * Set all spacing (gap and padding) at once
     *
     * @param string $value Spacing value
     * @return self For method chaining
     */
    public function spacing(string $value): self
    {
        return $this->gap($value)->padding($value);
    }

    /**
     * Make container full width
     *
     * @return self For method chaining
     */
    public function fullWidth(): self
    {
        return $this->width('100%');
    }

    /**
     * Make container full height
     *
     * @return self For method chaining
     */
    public function fullHeight(): self
    {
        return $this->height('100%');
    }

    /**
     * Make container scrollable
     *
     * @param string $direction Direction (both, x, y)
     * @return self For method chaining
     */
    public function scrollable(string $direction = 'both'): self
    {
        if ($direction === 'both') {
            return $this->overflow('auto');
        } elseif ($direction === 'x') {
            return $this->overflowX('auto');
        } else {
            return $this->overflowY('auto');
        }
    }

    /**
     * Apply rounded corners
     *
     * @param string|int $radius Radius value (e.g., '8px', 8, 'medium') or integer for pixels
     * @return self For method chaining
     */
    public function rounded(string|int $radius = 8): self
    {
        if (is_int($radius)) {
            $radius = $radius === 0 ? '0' : "{$radius}px";
        }
        return $this->borderRadius($radius);
    }

    /**
     * Apply shadow effect
     *
     * @param string|int $intensity Shadow intensity (0=none, 1-3=levels, 'light', 'medium', 'heavy', or custom CSS)
     * @return self For method chaining
     */
    public function shadow(string|int $intensity = 1): self
    {
        if (is_int($intensity)) {
            $shadows = [
                0 => 'none',
                1 => '0 2px 8px rgba(0, 0, 0, 0.1)',
                2 => '0 4px 16px rgba(0, 0, 0, 0.15)',
                3 => '0 8px 32px rgba(0, 0, 0, 0.2)',
            ];
            $shadow = $shadows[$intensity] ?? $shadows[1];
        } else {
            $shadows = [
                'light' => '0 1px 3px rgba(0,0,0,0.1)',
                'medium' => '0 4px 6px rgba(0,0,0,0.1)',
                'heavy' => '0 10px 15px rgba(0,0,0,0.2)'
            ];
            $shadow = $shadows[$intensity] ?? $intensity;
        }

        return $this->boxShadow($shadow);
    }

    /**
     * Hide container (display: none)
     *
     * @return self For method chaining
     */
    public function hide(): self
    {
        return $this->display('none');
    }

    /**
     * Show container (display: block)
     *
     * @return self For method chaining
     */
    public function show(): self
    {
        return $this->display('block');
    }

    /**
     * {@inheritDoc}
     *
     * Converts this container and all its children to a flat JSON structure
     * All components are returned at the same level, with 'parent' indicating parent-child relationships
     * Null values are filtered out from the configuration
     */
    /**
     * {@inheritDoc}
     */
    public function toJson(?int $order = null): array
    {
        // Filter out null values from config
        $config = array_filter($this->config, fn($value) => $value !== null);

        // Remove default visible value to save JSON size
        if (isset($config['visible']) && $config['visible'] === true) {
            unset($config['visible']);
        }

        // Container receives its own _order from parent
        if ($order !== null) {
            $config['_order'] = $order;
        }

        // CRITICAL: Include component ID in config for frontend lookups
        $config['_id'] = $this->id;

        // Start with this container's config
        $result = [$this->id => $config];

        // Append children with incremental order (1, 2, 3...)
        $childOrder = 1;
        foreach ($this->children as $childId => $child) {
            $childJson = $child->toJson($childOrder);
            $childOrder++;

            // Use + operator to preserve numeric keys (array_merge reindexes them!)
            $result = $result + $childJson;
        }

        return $result;
    }

    /**
     * Convert to array (alias for toJson for backward compatibility)
     *
     * @return array
     */
    public function build(): array
    {
        return $this->toJson();
    }

    /**
     * Detecta automáticamente la clase que está invocando el builder
     * Busca en el stack trace la primera clase fuera del namespace UI
     *
     * @return string El nombre completo con namespace de la clase invocante
     */
    private function detectCallingContext(): string
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);

        // Buscar en el stack trace la primera clase que NO sea del namespace UI
        foreach ($trace as $frame) {
            if (
                isset($frame['class']) &&
                !str_starts_with($frame['class'], 'App\\Services\\UI\\')
            ) {
                // Retornar el nombre completo con namespace (no solo el basename)
                return $frame['class'];
            }
        }

        return 'default';
    }

    /**
     * Genera ID determinístico basado en contexto + nombre
     * Siempre retorna el mismo ID para el mismo contexto + nombre
     *
     * @param string $context Nombre completo de la clase invocante
     * @param string $name Nombre del contenedor
     * @return int ID determinístico
     */
    private function generateDeterministicId(string $context, string $name): int
    {
        // Obtener offset del contexto (ej: 56150000)
        $offset = $this->getContextOffset($context);

        // Hash del nombre (0-9999)
        $hash = abs(crc32($name)) % 9999;

        // ID final: offset + hash + 1
        return $offset + $hash + 1;
    }

    /**
     * Obtener offset del contexto (mismo cálculo que UIIdGenerator)
     *
     * @param string $context Nombre completo de la clase
     * @return int Offset único para el contexto
     */
    private function getContextOffset(string $context): int
    {
        if ($context === 'default') {
            return 0;
        }

        // Generar un hash numérico único del nombre de la clase usando CRC32
        $hash = crc32($context);

        // Convertir a positivo si es negativo y escalar al rango deseado
        // Múltiplos de 10000, máximo 9999 contextos diferentes
        $offset = (abs($hash) % 9999) * 10000;

        return $offset;
    }

    /**
     * Buscar componente hijo por nombre (recursivo)
     *
     * @param string $name Nombre del componente a buscar
     * @return UIElement|null Componente encontrado o null
     */
    public function findByName(string $name): ?UIElement
    {
        foreach ($this->children as $child) {
            // Buscar en hijos directos que tengan método getName
            $childName = null;
            if (method_exists($child, 'getName')) {
                /** @var UIComponent|UIContainer $child */
                $childName = $child->getName();
            }

            if ($childName === $name) {
                return $child;
            }

            // Buscar recursivamente en contenedores
            if ($child instanceof UIContainer) {
                $found = $child->findByName($name);
                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }

    /**
     * Buscar componente hijo por ID (recursivo)
     *
     * @param int $id ID del componente a buscar
     * @return UIElement|null Componente encontrado o null
     */
    public function findById(int $id): ?UIElement
    {
        // Verificar si este contenedor tiene el ID
        if ($this->id === $id) {
            return $this;
        }

        foreach ($this->children as $child) {
            // Verificar ID del hijo
            if ($child->getId() === $id) {
                return $child;
            }

            // Buscar recursivamente en contenedores
            if ($child instanceof UIContainer) {
                $found = $child->findById($id);
                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }
}
