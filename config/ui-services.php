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

    \App\UI\Screens\DemoUiService::class,
    \App\UI\Screens\ForgotPasswordService::class,
    \App\UI\Screens\ResetPasswordService::class,
    \App\UI\Screens\LoginService::class,
    \App\UI\Screens\InputDemoService::class,
    \App\UI\Screens\SelectDemoService::class,
    \App\UI\Screens\CheckboxDemoService::class,
    \App\UI\Screens\FormDemoService::class,
    \App\UI\Screens\ButtonDemoService::class,
    \App\UI\Screens\TableDemoService::class,
    \App\UI\Screens\ModalDemoService::class,
    \App\UI\Screens\DemoMenuService::class,
    \App\UI\Screens\UploaderDemoService::class,
    \App\UI\Screens\CalendarDemoService::class,

    // Servicio de dashboard admin
    \App\UI\Screens\AdminDashboardService::class,
    // Servicio de verificación de email
    \App\UI\Screens\EmailVerifiedService::class,
    // Servicio de perfil de usuario
    \App\UI\Screens\ProfileService::class,

];
