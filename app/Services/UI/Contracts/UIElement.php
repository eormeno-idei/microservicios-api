<?php

namespace App\Services\UI\Contracts;

/**
 * Interface for all UI elements in the component tree
 * 
 * This interface defines the contract that all UI elements must implement,
 * enabling the Composite pattern for building hierarchical UI structures.
 */
interface UIElement
{
    /**
     * Get the unique identifier for this UI element
     * 
     * @return string The element ID (format: "id:type")
     */
    public function getId(): int;

    /**
     * Get the type of UI element (button, label, container, table, etc.)
     * 
     * @return string The element type
     */
    public function getType(): string;

    /**
     * Check if this element is a container
     *
     * @return bool True if container, false otherwise
     */
    public function isContainer(): bool;

    /**
     * Convert the UI element to its JSON representation
     * 
     * For leaf elements (Button, Label), this returns their configuration.
     * For composite elements (Container), this recursively calls toJson() on children.
     * 
     * @param int|null $order Optional order index relative to parent container (1, 2, 3...)
     * @return array The JSON-serializable array representation
     */
    public function toJson(?int $order = null): array;

    /**
     * Deserialize a UI element instance from its JSON representation.
     * First pass of two-pass deserialization: creates the element in isolation
     * without establishing parent-child relationships.
     * 
     * @param int $id The unique identifier for the element
     * @param array $data The JSON data to create from
     * @return self A new instance of the UI element
     */
    public static function deserialize(int $id, array $data): self;

    /**
     * Connect a child UI element to this element.
     * Second pass of two-pass deserialization: establishes the parent-child relationship
     * in the hierarchical structure.
     * 
     * @param UIElement $element The child element to connect
     * @return void
     */
    public function connectChild(UIElement $element): void;

    /**
     * Perform any post-connection initialization after all children are connected.
     * This method is called after the two-pass deserialization is complete,
     * allowing the element to finalize its state based on its children.
     * 
     * @return void
     */
    public function postConnect(): void;

    /**
     * Get the visibility state of the element
     * 
     * @return bool True if visible, false otherwise
     */
    public function isVisible(): bool;

    /**
     * Set the visibility state of the element
     * 
     * @param bool $visible The visibility state
     * @return self For method chaining
     */
    public function setVisible(bool $visible): self;

    public function setName(?string $name): self;

    /**
     * Get the parent where this element belongs
     * 
     * @return int|string|null The parent (int = parent ID, string = parent name, null = no parent)
     */
    public function getParent(): int|string|null;

    /**
     * Set the parent where this element belongs
     * 
     * @param int|string|null $parent The parent (int = parent ID, string = parent name, null = delete)
     * @return self For method chaining
     */
    public function setParent(int|string|null $parent): self;

    public function toString(): string;
}
