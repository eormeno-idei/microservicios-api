<?php

namespace App\Services\Screens;

use App\Services\UI\UIBuilder;
use App\Services\UI\Enums\LayoutType;
use App\Services\UI\AbstractUIService;
use App\Services\UI\Components\UIContainer;

class LandingDemoService extends AbstractUIService
{
    protected function buildBaseUI(UIContainer $container, ...$params): void
    {
        $container //= UIBuilder::container('main')
            //->parent('main')
            ->layout(LayoutType::VERTICAL)
            ->shadow(false)
            ->justifyContent('center')
            ->alignItems('center')
            ->padding(20);

        // Hero Section
        $container->add(
            UIBuilder::label('welcome')
                ->text('🚀 Welcome to USIM UI Framework')
                ->style('h2')
                ->center()
        );

        $container->add(
            UIBuilder::label('subtitle')
                ->text('Sistema moderno de componentes UI para aplicaciones web')
                ->style('h4')
                ->center()
        );

        // Features Section
        $container->add(
            UIBuilder::label('features_title')
                ->text('✨ Características Principales')
                ->style('h2')
                ->center()
        );

        // Features Cards Container
        $featuresContainer = UIBuilder::container('features')
            ->layout(LayoutType::HORIZONTAL) // Flex row
            ->padding(20)
            ->gap("20px")
            ->shadow(false)
            ->justifyContent('center') // Centra las cards horizontalmente
            ->alignItems('center');    // Centra las cards verticalmente

        // Card 1: Components
        $featuresContainer->add(
            UIBuilder::card('components_card')
                ->title('🎨 Componentes Modernos')
                ->description('Sistema completo con buttons, forms, tables, modals y más. Diseño profesional y responsive.')
                ->image('https://picsum.photos/350/200?random=1', 'top', 'Componentes UI modernos')
                ->theme('primary')
                ->elevation('medium')
                ->addAction('Ver Demos', 'view_demos', [], 'primary')
        );

        // Card 2: Easy to Use
        $featuresContainer->add(
            UIBuilder::card('easy_card')
                ->title('⚡ Fácil de Usar')
                ->description('API fluida con method chaining. Crea interfaces complejas con código simple y legible.')
                ->image('https://picsum.photos/350/200?random=2', 'top', 'API fácil de usar')
                ->theme('success')
                ->elevation('medium')
                ->addAction('Ver Código', 'view_code', [], 'success')
        );

        // Card 3: Customizable
        $featuresContainer->add(
            UIBuilder::card('custom_card')
                ->title('🎯 Personalizable')
                ->description('Estilos flexibles, temas, tamaños y configuraciones avanzadas para cada componente.')
                ->image('https://picsum.photos/350/200?random=3', 'top', 'Componentes personalizables')
                ->theme('warning')
                ->elevation('medium')
                ->addAction('Personalizar', 'customize', [], 'warning')
        );

        $container->add($featuresContainer);

        // Getting Started Section
        $container->add(
            UIBuilder::label('getting_started')
                ->text('🚀 ¡Comienza Ahora!')
                ->style('h2')
                ->center()
        );

        $container->add(
            UIBuilder::card('getting_started_card')
                ->title('Explora los Demos')
                ->description('Navega por los diferentes demos para ver todas las capacidades del framework en acción.')
                ->style('elevated')
                ->size('large')
                ->addAction('Ver Todos los Demos', 'view_all_demos', [], 'primary')
                ->addAction('Documentación', 'view_docs', [], 'info')
        );

        // return $container;
    }

    /**
     * Handler for viewing demos
     */
    public function onViewDemos(array $params): array
    {
        return [
            'action' => 'redirect',
            'url' => '/demo/demo-ui'
        ];
    }

    /**
     * Handler for viewing code examples
     */
    public function onViewCode(array $params): array
    {
        return [
            'action' => 'redirect',
            'url' => '/demo/form-demo'
        ];
    }

    /**
     * Handler for customization demo
     */
    public function onCustomize(array $params): array
    {
        return [
            'action' => 'redirect',
            'url' => '/demo/button-demo'
        ];
    }

    /**
     * Handler for viewing all demos
     */
    public function onViewAllDemos(array $params): array
    {
        return [
            'action' => 'redirect',
            'url' => '/demo/demo-ui'
        ];
    }

    /**
     * Handler for viewing documentation
     */
    public function onViewDocs(array $params): array
    {
        return [
            'action' => 'redirect',
            'url' => '/demo/table-demo'
        ];
    }
}
