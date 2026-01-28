<?php

namespace Idei\Usim\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class UsimEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $eventName,
        public array $params = []
    ) {}
}
