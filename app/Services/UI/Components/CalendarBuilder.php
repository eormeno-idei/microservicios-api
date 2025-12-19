<?php

namespace App\Services\UI\Components;

/**
 * Builder for Calendar UI component
 */
class CalendarBuilder extends UIComponent
{
    protected function getDefaultConfig(): array
    {
        return [
            'year' => date('Y'),
            'month' => date('n'), // 1-12
            'events' => [],
            'show_saturday_info' => true,
            'show_sunday_info' => true,
            'references_columns' => 2,
            'min_height' => '30px',
            'max_height' => null,
            'cell_size' => null,
        ];
    }

    public function year(int $year): static
    {
        return $this->setConfig('year', $year);
    }

    public function month(int $month): static
    {
        return $this->setConfig('month', $month);
    }

    public function events(array $events): static
    {
        return $this->setConfig('events', $events);
    }

    public function cellSize(string $size): static
    {
        return $this->setConfig('cell_size', $size);
    }

    public function minHeight(string $height): static
    {
        return $this->setConfig('min_height', $height);
    }

    public function maxHeight(string $height): static
    {
        return $this->setConfig('max_height', $height);
    }

    public function showSaturdayInfo(bool $show = true): static
    {
        return $this->setConfig('show_saturday_info', $show);
    }

    public function showSundayInfo(bool $show = true): static
    {
        return $this->setConfig('show_sunday_info', $show);
    }

    public function referencesColumns(int $columns): static
    {
        // Limitación entre 1 y 3
        $columns = max(1, min(3, $columns));
        return $this->setConfig('references_columns', $columns);
    }
}
