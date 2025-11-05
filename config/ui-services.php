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
    | Cada servicio debe implementar métodos de evento con el formato:
    | 
    | public function on{ActionName}(array $params): array
    | 
    | Ejemplo: action "submit_form" → método onSubmitForm(array $params)
    |
    */
    
    \App\Services\Screens\DemoUiService::class,
    \App\Services\Screens\InputDemoService::class,
    \App\Services\Screens\SelectDemoService::class,
    \App\Services\Screens\CheckboxDemoService::class,
    \App\Services\Screens\FormDemoService::class,
    \App\Services\Screens\ButtonDemoService::class,
    \App\Services\Screens\TableDemoService::class,
    \App\Services\Screens\ModalDemoService::class,
    \App\Services\Screens\DemoMenuService::class,
    
];
