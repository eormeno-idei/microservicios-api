<?php

namespace App\Services\Screens;

use App\Services\UI\AbstractUIService;
use App\Services\UI\Components\UIContainer;

class AdminDashboardService extends AbstractUIService
{
    protected function buildBaseUI(UIContainer $container, ...$params): void
    {
        $container->title('Admin Dashboard');
    }
}
