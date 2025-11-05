<?php

namespace App\Services\Screens;

use App\Services\UI\AbstractUIService;
use App\Services\UI\Components\UIContainer;
use App\Services\UI\Enums\LayoutType;
use App\Services\UI\UIBuilder;
use App\Services\UI\Components\ButtonBuilder;

class ButtonDemoService extends AbstractUIService
{
    // Component reference (auto-injected)
    protected ButtonBuilder $btn_toggle;

    /**
     * Build the button demo UI
     */
    protected function buildBaseUI(...$params): UIContainer
    {
        $container = UIBuilder::container('main')
            ->parent('main')
            ->layout(LayoutType::VERTICAL)
            ->alignContent('center')
            ->alignItems('center')
            ->title('Button Demo - Click Me!');

        // Single button that changes its own label
        $container->add(
            UIBuilder::button('btn_toggle')
                ->label('Click Me!')
                ->action('toggle_label')
                ->style('primary')
        );

        return $container;
    }

    /**
     * Handle button click - toggles its own label
     */
    public function onToggleLabel(array $params): void
    {
        $currentLabel = $this->btn_toggle->get('label', 'Click Me!');
        
        // Toggle between two labels
        if ($currentLabel === 'Click Me!') {
            $this->btn_toggle->label('Clicked! 🎉');
            $this->btn_toggle->style('success');
        } else {
            $this->btn_toggle->label('Click Me!');
            $this->btn_toggle->style('primary');
        }
    }
}
