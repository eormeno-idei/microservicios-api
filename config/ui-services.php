<?php

/**
 * UI Services Registry
 *
 * Lista de servicios que construyen interfaces de usuario.
 * Se usa para resolver qué servicio debe manejar eventos de componentes
 * basándose en el offset del ID del componente.
 *
 * Performance: Este archivo se carga una vez por worker PHP-FPM y se
 * cachea en memoria para lookups instantáneos (~0.001ms).
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Registered UI Services
    |--------------------------------------------------------------------------
    |
    | Servicios que generan UIs y manejan eventos de componentes.
    | Cada servicio puede implementar métodos de evento con el formato:
    |
    | public function on{ActionName}(array $params): array
    |
    | Ejemplo: action "submit_form" → método onSubmitForm(array $params)
    |
    */

    \App\UI\Screens\DemoUi::class,
    \App\UI\Screens\ForgotPassword::class,
    \App\UI\Screens\ResetPassword::class,
    \App\UI\Screens\Login::class,
    \App\UI\Screens\InputDemo::class,
    \App\UI\Screens\SelectDemo::class,
    \App\UI\Screens\CheckboxDemo::class,
    \App\UI\Screens\FormDemo::class,
    \App\UI\Screens\ButtonDemo::class,
    \App\UI\Screens\TableDemo::class,
    \App\UI\Screens\ModalDemo::class,
    \App\UI\Screens\DemoMenu::class,
    \App\UI\Screens\UploaderDemo::class,
    \App\UI\Screens\CalendarDemo::class,

    // Servicio de dashboard admin
    \App\UI\Screens\AdminDashboard::class,
    // Servicio de verificación de email
    \App\UI\Screens\EmailVerified::class,
    // Servicio de perfil de usuario
    \App\UI\Screens\Profile::class,

];
