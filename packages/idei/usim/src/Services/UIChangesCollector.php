<?php

namespace Idei\Usim\Services;

class UIChangesCollector
{
    protected array $changes = [];
    protected array $storage_changes = [];

    public function add(array $change = []): void
    {
        $this->changes += $change;
    }

    public function setStorage(array $storageChange = []): void
    {
        $this->storage_changes = array_merge($this->storage_changes, $storageChange);
    }

    public function all(): array
    {
        $this->changes['storage'] =['usim' => encrypt(json_encode($this->storage_changes))];
        return $this->changes;
    }
}
