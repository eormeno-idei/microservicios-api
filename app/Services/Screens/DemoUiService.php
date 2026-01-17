<?php
namespace App\Services\Screens;

use Idei\Usim\Services\AbstractUIService;
use Idei\Usim\Services\Components\LabelBuilder;
use Idei\Usim\Services\Components\UIContainer;
use Idei\Usim\Services\Enums\LayoutType;
use Idei\Usim\Services\Support\UIDebug;
use Idei\Usim\Services\UIBuilder;

class DemoUiService extends AbstractUIService
{
    protected LabelBuilder $lbl_welcome;
    protected LabelBuilder $lbl_counter;
    protected int $store_counter = 1000;

    protected function buildBaseUI(UIContainer $container, ...$params): void
    {
        $container
            ->title('Demo UI Components')
            ->maxWidth('500px')
            ->centerHorizontal()
            ->shadow(2)
            ->padding('30px');

        $this->buildUIElements($container);
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
            ->shadow(false)
            ->centerContent()
            ->gap("10px");

        $counterContainer->add(
            UIBuilder::button('btn_decrement')
                ->label('➖')
                ->action('decrement_counter')
                ->style('danger')
                ->variant('filled')
        );

        $counterContainer->add(
            UIBuilder::label('lbl_counter')
                ->text($this->store_counter)
                ->style('primary')
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

    protected function postLoadUI(): void
    {
        $this->updateCounterLabel($this->lbl_counter, $this->store_counter);
    }

    public function onTestAction(array $params): void
    {
        $this->lbl_welcome
            ->text("✅ ¡Botón presionado!\n\nHora actual: " . now()->toDateTimeString())
            ->style('success');
    }

    public function onIncrementCounter(array $params): void
    {
        $this->updateCounterLabel($this->lbl_counter, ++$this->store_counter);
    }

    public function onDecrementCounter(array $params): void
    {
        $this->updateCounterLabel($this->lbl_counter, --$this->store_counter);
    }

    private function updateCounterLabel(LabelBuilder $labelBuilder, int $counterValue): LabelBuilder
    {
        $counterStyle = 'primary';
        if ($counterValue > 5) {
            $counterStyle = 'success';
        } elseif ($counterValue < 0) {
            $counterStyle = 'danger';
        }

        $labelBuilder
            ->text((string) $counterValue)
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
