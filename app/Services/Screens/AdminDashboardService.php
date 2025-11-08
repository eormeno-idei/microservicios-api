<?php

namespace App\Services\Screens;

use App\Services\UI\UIBuilder;
use App\Services\UI\Enums\LayoutType;
use App\Services\UI\AbstractUIService;
use App\Services\UI\Components\UIContainer;

class AdminDashboardService extends AbstractUIService
{
    protected function buildBaseUI(...$params): UIContainer
    {
        $container = UIBuilder::container('admin_dashboard')
            ->parent('main')
            ->layout(LayoutType::VERTICAL)
            ->alignContent('center')
            ->alignItems('center')
            ->title('Admin Dashboard');

        return $container;
    }
}
