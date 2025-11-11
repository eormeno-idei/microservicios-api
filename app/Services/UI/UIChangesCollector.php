<?php

namespace App\Services\UI;

class UIChangesCollector
{
    protected array $changes = [];

    public function add(array $change = []): void
    {
        $this->changes += $change;
    }

    public function all(): array
    {
        return $this->changes;
    }
}
