<?php

namespace App\Services\Screens;

use App\Services\UI\UIBuilder;
use App\Services\UI\AbstractUIService;
use App\Services\UI\Components\UIContainer;
use App\Services\UI\Components\ButtonBuilder;

class ButtonDemoService extends AbstractUIService
{
    protected ButtonBuilder $btn_toggle;

    /**
     * Build the button demo UI
     */
    protected function buildBaseUI(UIContainer $container, ...$params): void
    {
        $container
            ->alignContent('center')
            ->alignItems('center')
            ->title('Button Demo - Click Me!')
            ->add(
                UIBuilder::button('btn_toggle')
                    ->label('Click Me!')
                    ->action('toggle_label')
                    ->style('primary')
            );
    }

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
