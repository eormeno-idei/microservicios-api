<?php

namespace Idei\Usim\Services;

use Idei\Usim\Services\Components\ButtonBuilder;
use Idei\Usim\Services\Components\LabelBuilder;
use Idei\Usim\Services\Components\UIContainer;
use Idei\Usim\Services\Components\TableBuilder;
use Idei\Usim\Services\Components\TableRowBuilder;
use Idei\Usim\Services\Components\InputBuilder;
use Idei\Usim\Services\Components\SelectBuilder;
use Idei\Usim\Services\Components\CheckboxBuilder;
use Idei\Usim\Services\Components\FormBuilder;
use Idei\Usim\Services\Components\MenuDropdownBuilder;
use Idei\Usim\Services\Components\CardBuilder;
use Idei\Usim\Services\Components\UploaderBuilder;
use Idei\Usim\Services\Components\CalendarBuilder;

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

    /**
     * Create a new uploader component
     *
     * @param string|null $name The optional semantic name for the uploader
     * @return UploaderBuilder
     */
    public static function uploader(?string $name = null): UploaderBuilder
    {
        return new UploaderBuilder($name);
    }

    /**
     * Create a new calendar component
     *
     * @param string|null $name The optional semantic name for the calendar
     * @return CalendarBuilder
     */
    public static function calendar(?string $name = null): CalendarBuilder
    {
        return new CalendarBuilder($name);
    }
}
