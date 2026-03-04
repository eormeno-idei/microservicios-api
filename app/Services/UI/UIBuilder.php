<?php

namespace App\Services\UI;

use App\Services\UI\Components\ButtonBuilder;
use App\Services\UI\Components\LabelBuilder;
use App\Services\UI\Components\UIContainer;
use App\Services\UI\Components\TableBuilder;
use App\Services\UI\Components\TableRowBuilder;
use App\Services\UI\Components\InputBuilder;
use App\Services\UI\Components\SelectBuilder;
use App\Services\UI\Components\CheckboxBuilder;
use App\Services\UI\Components\FormBuilder;
use App\Services\UI\Components\MenuDropdownBuilder;
use App\Services\UI\Components\CardBuilder;

/**
 * Factory class for creating UI components
 * 
 * Provides static methods to create various UI component builders.
 * These builders use a fluent API for configuring components.
 */
class UIBuilder
{
    /**
     * Create a new button component
     * 
     * @param string|null $name an optional semantic name for the button
     * @return ButtonBuilder
     */
    public static function button(?string $name = null): ButtonBuilder
    {
        return new ButtonBuilder($name);
    }

    /**
     * Create a new label component
     * 
     * @param string|null $name an optional semantic name for the label
     * @return LabelBuilder
     */
    public static function label(?string $name = null): LabelBuilder
    {
        return new LabelBuilder($name);
    }

    /**
     * Create a new table component
     * 
     * @param string|null $name The optional semantic name for the table
     * @param int $rows Number of data rows (0 for dynamic table)
     * @param int $cols Number of columns (0 for dynamic table)
     * @return TableBuilder
     */
    public static function table(?string $name = null, int $rows = 0, int $cols = 0): TableBuilder
    {
        return new TableBuilder($name, $rows, $cols);
    }

    /**
     * Create a new table row component
     * 
     * @param TableBuilder $table The parent table this row belongs to
     * @param string|null $name The optional semantic name for the row
     * @return TableRowBuilder
     */
    public static function tableRow(TableBuilder $table, ?string $name = null): TableRowBuilder
    {
        return new TableRowBuilder($table, $name);
    }

    /**
     * Create a new input component
     * 
     * @param string|null $name The optional semantic name for the input
     * @return InputBuilder
     */
    public static function input(?string $name = null): InputBuilder
    {
        return new InputBuilder($name);
    }

    /**
     * Create a new select component
     * 
     * @param string|null $name The optional semantic name for the select
     * @return SelectBuilder
     */
    public static function select(?string $name = null): SelectBuilder
    {
        return new SelectBuilder($name);
    }

    /**
     * Create a new checkbox component
     * 
     * @param string|null $name The optional semantic name for the checkbox
     * @return CheckboxBuilder
     */
    public static function checkbox(?string $name = null): CheckboxBuilder
    {
        return new CheckboxBuilder($name);
    }

    /**
     * Create a new form component
     * 
     * @param string|null $name The optional semantic name for the form
     * @return FormBuilder
     */
    public static function form(?string $name = null): FormBuilder
    {
        return new FormBuilder($name);
    }

    /**
     * Create a new container component
     * 
     * @param string|null $name The optional semantic name for the container
     * @return UIContainer
     */
    public static function container(?string $name = null, ?string $context = null): UIContainer
    {
        return new UIContainer($name, $context);
    }

    /**
     * Create a new menu dropdown component
     * 
     * @param string $name The semantic name for the menu
     * @return MenuDropdownBuilder
     */
    public static function menuDropdown(string $name): MenuDropdownBuilder
    {
        return new MenuDropdownBuilder($name);
    }

    /**
     * Create a new card component
     * 
     * @param string|null $name The optional semantic name for the card
     * @return CardBuilder
     */
    public static function card(?string $name = null): CardBuilder
    {
        return new CardBuilder($name);
    }
}
