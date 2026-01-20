<?php
namespace App\Services\Screens;

use Idei\Usim\Services\AbstractUIService;
use Idei\Usim\Services\Components\ButtonBuilder;
use Idei\Usim\Services\Components\UIContainer;
use Idei\Usim\Services\UIBuilder;
use Illuminate\Support\Facades\Log;

class ButtonDemoService extends AbstractUIService
{
    protected ButtonBuilder $btn_toggle;
    protected bool $store_state = false;

    protected function buildBaseUI(UIContainer $container, ...$params): void
    {
        $container
            ->alignContent('center')->alignItems('center')
            ->title('Button Demo - Click Me!')
            ->padding('30px')->maxWidth('400px')
            ->centerHorizontal()->shadow(2)
            ->add(
                UIBuilder::button('btn_toggle')
                    ->label('Click Me!')
                    ->action('toggle_label')
                    ->style('primary')
            );
    }

    protected function postLoadUI(): void
    {
        $this->updateButtonState();
    }

    public function onToggleLabel(array $params): void
    {
        $this->store_state = ! $this->store_state;
        $this->updateButtonState();
    }

    private function updateButtonState(): void
    {
        if ($this->store_state) {
            $this->btn_toggle->label('Clicked! 🎉')->style('success');
        } else {
            $this->btn_toggle->label('Click Me!')->style('primary');
        }
    }
}
