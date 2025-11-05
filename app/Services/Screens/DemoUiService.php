<?php

namespace App\Services\Screens;

use App\Services\UI\UIBuilder;
use Illuminate\Support\Facades\Auth;
use App\Services\UI\Enums\LayoutType;
use Illuminate\Support\Facades\Cache;
use App\Services\UI\AbstractUIService;
use App\Services\UI\Components\UIContainer;
use App\Services\UI\Components\LabelBuilder;

class DemoUiService extends AbstractUIService
{
    // Components that can be modified by event handlers
    protected LabelBuilder $lbl_welcome;
    protected LabelBuilder $lbl_counter;

    /**
     * Get counter value from cache
     * 
     * @return int Current counter value
     */
    private function getCounterValue(): int
    {
        $userId = Auth::check() ? Auth::id() : session()->getId();
        $key = "demo_counter:{$userId}";
        $value = Cache::get($key, 0);
        return $value;
    }

    /**
     * Set counter value in cache
     * 
     * @param int $value New counter value
     * @return void
     */
    private function setCounterValue(int $value): void
    {
        $userId = Auth::check() ? Auth::id() : session()->getId();
        $key = "demo_counter:{$userId}";
        Cache::put($key, $value, now()->addHours(24));
    }

    /**
     * Build base UI structure (required by StoresUIState trait)
     * 
     * Esta UI se genera cada vez que se necesita y se guarda en cache.
     * Los componentes con name tienen IDs determinísticos.
     * 
     * @param mixed ...$params Optional parameters for UI construction
     * 
     * @return UIContainer Base UI structure
     */
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

    /**
     * Build and add UI elements to the container
     * 
     * @param \App\Services\UI\Components\UIContainer $container
     * @return void
     */
    private function buildUIElements($container): void
    {
        // ========================================
        // DEMO SIMPLIFICADO - SISTEMA REACTIVO
        // ========================================

        // Welcome label (con nombre para poder modificarlo)
        $container->add(
            UIBuilder::label('lbl_welcome')
                ->text('🔵 Estado inicial: Presiona "Test Update" para cambiar este texto')
                ->style('info')
        );

        // Botón para ACTUALIZAR componente
        $container->add(
            UIBuilder::button('btn_test_update')
                ->label('🔄 Test Update (ACTUALIZAR)')
                ->action('test_action')
                ->icon('star')
                ->style('primary')
                ->variant('filled')
        );

        // Botón para AGREGAR componente
        $container->add(
            UIBuilder::button('btn_test_add')
                ->label('➕ Test Add (AGREGAR)')
                ->action('open_settings')
                ->icon('settings')
                ->style('warning')
                ->variant('filled')
        );

        // Separador visual
        $container->add(
            UIBuilder::label()
                ->text('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━')
                ->style('default')
        );

        // Contador con botones incrementar/decrementar
        $container->add(
            UIBuilder::label()
                ->text('🔢 Contador Interactivo:')
                ->style('default')
        );

        $counterContainer = UIBuilder::container('counter_container')
            ->layout(LayoutType::HORIZONTAL);

        $counterContainer->add(
            UIBuilder::button('btn_decrement')
                ->label('➖')
                ->action('decrement_counter')
                ->style('danger')
                ->variant('filled')
        );

        // Obtener valor del contador desde session
        $counterValue = $this->getCounterValue();
        $counterStyle = 'primary';

        if ($counterValue > 5) {
            $counterStyle = 'success';
        } elseif ($counterValue < 0) {
            $counterStyle = 'danger';
        }

        $counterContainer->add(
            UIBuilder::label('lbl_counter')
                ->text((string) $counterValue)
                ->style($counterStyle)
        );

        $counterContainer->add(
            UIBuilder::button('btn_increment')
                ->label('➕')
                ->action('increment_counter')
                ->style('success')
                ->variant('filled')
        );

        $container->add($counterContainer);

        // Separador visual
        $container->add(
            UIBuilder::label()
                ->text('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━')
                ->style('default')
        );

        $container->add(
            UIBuilder::label()
                ->text('💡 Nuevos componentes aparecerán aquí abajo:')
                ->style('default')
        );

        /* CÓDIGO COMENTADO - DEMOS ADICIONALES
        
        // Section: Buttons
        $container->add(
            UIBuilder::label()
                ->text('Buttons with Different Styles')
                ->style('heading')
        );

        $container->add(
            UIBuilder::button('btn_success')
                ->label('Success Button')
                ->action('test_action')
                ->icon('check')
                ->style('success')
                ->variant('filled')
        );

        $container->add(
            UIBuilder::button('btn_danger')
                ->label('Danger Button')
                ->action('test_action')
                ->icon('trash')
                ->style('danger')
                ->variant('filled')
        );

        $container->add(
            UIBuilder::button('btn_warning')
                ->label('Warning Button')
                ->action('test_action')
                ->icon('alert')
                ->style('warning')
                ->variant('filled')
        );

        $container->add(
            UIBuilder::button('btn_disabled')
                ->label('Disabled Button')
                ->action('test_action')
                ->icon('lock')
                ->style('primary')
                ->enabled(false)
        );

        // Section: Horizontal Container with Buttons
        $container->add(
            UIBuilder::label()
                ->text('Horizontal Layout - Button Group')
                ->style('heading')
        );

        $horizontalButtons = UIBuilder::container('horizontal_buttons')
            ->layout(LayoutType::HORIZONTAL);

        $horizontalButtons->add(
            UIBuilder::button('h_btn_1')
                ->label('Action 1')
                ->action('action_1')
                ->style('primary')
        );

        $horizontalButtons->add(
            UIBuilder::button('h_btn_2')
                ->label('Action 2')
                ->action('action_2')
                ->style('success')
        );

        $horizontalButtons->add(
            UIBuilder::button('h_btn_3')
                ->label('Action 3')
                ->action('action_3')
                ->style('danger')
        );

        $container->add($horizontalButtons);

        // Section: Labels
        $container->add(
            UIBuilder::label()
                ->text('Labels with Different Styles')
                ->style('heading')
        );

        $container->add(
            UIBuilder::label()
                ->text('This is a default label')
                ->style('default')
        );

        $container->add(
            UIBuilder::label()
                ->text('This is a success message')
                ->style('success')
        );

        $container->add(
            UIBuilder::label()
                ->text('This is a warning message')
                ->style('warning')
        );

        $container->add(
            UIBuilder::label()
                ->text('This is an error message')
                ->style('error')
        );

        $container->add(
            UIBuilder::label()
                ->text('This is an info message')
                ->style('info')
        );

        // Section: Nested Container - Form Layout
        $container->add(
            UIBuilder::label()
                ->text('Form Layout - Vertical Container with Form Fields')
                ->style('heading')
        );

        $formContainer = UIBuilder::container('form_container')
            ->layout(LayoutType::VERTICAL)
            ->title('User Registration Form');

        $formContainer->add(
            UIBuilder::input('form_username')
                ->label('Username')
                ->placeholder('Enter your username')
                ->value('')
                ->required(true)
        );

        $formContainer->add(
            UIBuilder::input('form_email')
                ->label('Email Address')
                ->placeholder('user@example.com')
                ->type('email')
                ->required(true)
        );

        $formContainer->add(
            UIBuilder::input('form_password')
                ->label('Password')
                ->placeholder('Enter your password')
                ->type('password')
                ->required(true)
        );

        $formContainer->add(
            UIBuilder::select('form_country')
                ->label('Country')
                ->options([
                    'us' => 'United States',
                    'uk' => 'United Kingdom',
                    'ca' => 'Canada',
                    'mx' => 'Mexico',
                    'es' => 'Spain',
                ])
                ->placeholder('Select your country')
                ->required(true)
        );

        $formContainer->add(
            UIBuilder::checkbox('form_terms')
                ->label('I agree to the terms and conditions')
                ->checked(false)
                ->required(true)
        );

        // Form buttons in horizontal layout
        $formButtons = UIBuilder::container('form_buttons')
            ->layout(LayoutType::HORIZONTAL);

        $formButtons->add(
            UIBuilder::button('form_submit')
                ->label('Submit')
                ->action('submit_form')
                ->style('success')
        );

        $formButtons->add(
            UIBuilder::button('form_cancel')
                ->label('Cancel')
                ->action('cancel_form')
                ->style('danger')
        );

        $formContainer->add($formButtons);

        $container->add($formContainer);
        */ // FIN DE CÓDIGO COMENTADO
    }

    // ============================================================
    // Event Handlers
    // ============================================================
    // Methods that handle UI component events from the frontend.
    // Convention: action "snake_case" → method "onPascalCase"
    // ============================================================

    /**
     * Handle test action event
     * 
     * Triggered by: Primary Button (action: "test_action")
     * Demuestra: Actualización automática de texto en label
     * 
     * @param array $params Event parameters
     * @return void
     */
    public function onTestAction(array $params): void
    {
        $this->lbl_welcome
            ->text("✅ ¡Botón presionado!\n\nAhora puedes ver que los saltos de línea\nfuncionan correctamente en las labels.\n\nLínea 1\nLínea 2\nLínea 3")
            ->style('success');
    }

    /**
     * Handle form submission
     * 
     * Example action: "submit_form"
     * 
     * @param array $params Form data
     * @return array Response
     */
    public function onSubmitForm(array $params): array
    {
        // Validate form data
        $username = $params['username'] ?? null;
        $email = $params['email'] ?? null;

        if (empty($username) || empty($email)) {
            return response()->json([
                'error' => 'Username and email are required',
            ], 400)->getData(true);
        }

        // Process form (save to database, send email, etc.)
        // ...

        return [
            'message' => "Form submitted successfully for user: {$username}",
            'data' => [
                'username' => $username,
                'email' => $email,
            ],
        ];
    }

    /**
     * Handle form cancellation
     * 
     * Example action: "cancel_form"
     * 
     * @param array $params Event parameters
     * @return array Response
     */
    public function onCancelForm(array $params): array
    {
        return [
            'message' => 'Form cancelled',
            'redirect' => '/dashboard',
        ];
    }

    /**
     * Handle settings opening
     * 
     * Example action: "open_settings"
     * Demuestra: Agregar nuevo componente dinámicamente
     * 
     * @param array $params Event parameters
     * @return void
     */
    public function onOpenSettings(array $params): void
    {
        // Agregar nuevo label al final del container
        $this->container->add(
            UIBuilder::label('lbl_settings_' . time())
                ->text('⚙️ Settings panel opened!')
                ->style('warning')
        );
    }

    /**
     * Handle counter increment
     * 
     * Example action: "increment_counter"
     * Demuestra: Actualizar valor numérico en label
     * 
     * @param array $params Event parameters
     * @return void
     */
    public function onIncrementCounter(array $params): void
    {
        // Incrementar valor en cache
        $currentValue = $this->getCounterValue();
        $newValue = $currentValue + 1;
        $this->setCounterValue($newValue);

        // Determinar estilo basado en el valor
        $counterStyle = 'primary';
        if ($newValue > 5) {
            $counterStyle = 'success';
        } elseif ($newValue < 0) {
            $counterStyle = 'danger';
        }

        $this->lbl_counter->text((string) $newValue)->style($counterStyle);
    }

    /**
     * Handle counter decrement
     * 
     * Example action: "decrement_counter"
     * Demuestra: Actualizar valor numérico en label
     * 
     * @param array $params Event parameters
     * @return void
     */
    public function onDecrementCounter(array $params): void
    {
        // Decrementar valor en cache
        $currentValue = $this->getCounterValue();
        $newValue = $currentValue - 1;
        $this->setCounterValue($newValue);

        $counterStyle = 'primary';
        if ($newValue > 5) {
            $counterStyle = 'success';
        } elseif ($newValue < 0) {
            $counterStyle = 'danger';
        }

        $this->lbl_counter->text((string) $newValue)->style($counterStyle);
    }
}
