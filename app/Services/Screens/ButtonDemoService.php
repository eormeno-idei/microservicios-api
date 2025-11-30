<?php
namespace App\Services\Screens;

use App\Services\UI\AbstractUIService;
use App\Services\UI\Components\ButtonBuilder;
use App\Services\UI\Components\UIContainer;
use App\Services\UI\UIBuilder;
use Illuminate\Support\Facades\Log;

class ButtonDemoService extends AbstractUIService
{
    protected ButtonBuilder $btn_toggle;
    protected bool $store_state = false;

    protected function buildBaseUI(UIContainer $container, ...$params): void
    {
        \Log::info('📦 [ButtonDemoService] buildBaseUI() - Construyendo UI base', [
            'params' => $params,
        ]);
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
        \Log::info('🔄 [ButtonDemoService] postLoadUI() - Actualizando estado del botón', [
            'store_state' => $this->store_state,
        ]);
        $this->updateButtonState();
    }

    public function onToggleLabel(array $params): void
    {
        \Log::info('🎯 [ButtonDemoService] onToggleLabel() - Evento recibido', [
            'params' => $params,
            'store_state_antes' => $this->store_state,
        ]);
        
        $this->store_state = ! $this->store_state;
        
        \Log::info('✨ [ButtonDemoService] onToggleLabel() - Estado cambiado', [
            'store_state_después' => $this->store_state,
        ]);
        
        $this->updateButtonState();
    }

    private function updateButtonState(): void
    {
        \Log::info('🎨 [ButtonDemoService] updateButtonState() - Actualizando propiedades del botón', [
            'store_state' => $this->store_state,
        ]);
        
        if ($this->store_state) {
            $this->btn_toggle->label('Clicked! 🎉')->style('success');
            \Log::info('✅ [ButtonDemoService] Botón actualizado a estado SUCCESS');
        } else {
            $this->btn_toggle->label('Click Me!')->style('primary');
            \Log::info('🔵 [ButtonDemoService] Botón actualizado a estado PRIMARY');
        }
    }
}
