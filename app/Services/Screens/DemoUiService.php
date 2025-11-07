<?php

namespace App\Services\Screens;

use App\Services\UI\UIBuilder;
use App\Services\UI\Enums\LayoutType;
use App\Services\UI\AbstractUIService;
use App\Services\UI\Components\UIContainer;
use App\Services\UI\Components\LabelBuilder;

class DemoUiService extends AbstractUIService
{
    protected LabelBuilder $lbl_welcome;
    protected LabelBuilder $lbl_counter;
    protected int $store_counter = 1000;


    protected function buildBaseUI(...$params): UIContainer
    {
        $container = UIBuilder::container('main')
            ->parent('main')
            ->layout(LayoutType::VERTICAL)
            ->title('Demo UI Components');

        // Build UI elements
        $this->buildUIElements($container);

        return $container;
    }

    private function buildUIElements($container): void
    {
        $container->add(
            UIBuilder::label('lbl_welcome')
                ->text('🔵 Estado inicial: Presiona "Test Update" para cambiar este texto')
                ->style('info')
        );

        $container->add(
            UIBuilder::button('btn_test_update')
                ->label('🔄 Test Update (ACTUALIZAR)')
                ->action('test_action')
                ->icon('star')
                ->style('primary')
                ->variant('filled')
        );

        $container->add(
            UIBuilder::button('btn_test_add')
                ->label('➕ Test Add (AGREGAR)')
                ->action('open_settings')
                ->icon('settings')
                ->style('warning')
                ->variant('filled')
        );

        $container->add(
            UIBuilder::label()
                ->text('🔢 Contador Interactivo:')
                ->style('default')
        );

        $counterContainer = UIBuilder::container('counter_container')
            ->layout(LayoutType::HORIZONTAL)
            ->gap("10px");

        $counterContainer->add(
            UIBuilder::button('btn_decrement')
                ->label('➖')
                ->action('decrement_counter')
                ->style('danger')
                ->variant('filled')
        );

        $counterContainer->add(
            $this->updateCounterDisplay(UIBuilder::label('lbl_counter'))
        );

        $counterContainer->add(
            UIBuilder::button('btn_increment')
                ->label('➕')
                ->action('increment_counter')
                ->style('success')
                ->variant('filled')
        );

        $container->add($counterContainer);

        $container->add(
            UIBuilder::label()
                ->text('💡 Nuevos componentes aparecerán aquí abajo:')
                ->style('default')
        );
    }

    public function onTestAction(array $params): void
    {
        $this->lbl_welcome
            ->text("✅ ¡Botón presionado!\n\nHora actual: " . now()->toDateTimeString())
            ->style('success');
    }

    public function onIncrementCounter(array $params): void
    {
        $this->store_counter++;
        $this->updateCounterDisplay($this->lbl_counter);
    }

    public function onDecrementCounter(array $params): void
    {
        $this->store_counter--;
        $this->updateCounterDisplay($this->lbl_counter);
    }

    private function updateCounterDisplay(LabelBuilder $labelBuilder): LabelBuilder
    {
        $counterStyle = 'primary';
        if ($this->store_counter > 5) {
            $counterStyle = 'success';
        } elseif ($this->store_counter < 0) {
            $counterStyle = 'danger';
        }

        $labelBuilder
            ->text((string) $this->store_counter)
            ->style($counterStyle);

        return $labelBuilder;
    }

    public function onOpenSettings(array $params): void
    {
        // Agregar nuevo label al final del container
        $this->container->add(
            UIBuilder::label('lbl_settings_' . time())
                ->text('⚙️ Settings panel opened!')
                ->style('warning')
        );
    }
}
