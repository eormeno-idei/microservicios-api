# Prompt para Iniciar la Siguiente Sesión

Copia y pega el siguiente texto en el chat de tu próxima sesión con GitHub Copilot para retomar el trabajo exactamente donde lo dejamos.

---

Hola. Estoy retomando el trabajo de arquitectura del framework **USIM**.

Actualmente estoy ubicado en la rama `refactor/extract-usim-incremental`.
Por favor lee detenidamente el archivo **`docs/usim-laravel-package-refactoring.md`**.

**ESTADO ACTUAL:**
1.  Hemos completado la **Fase 1 de Seguridad**: `authorize()` ya está implementado en `AbstractUIService` y protegido por los controladores `UIController` y `UIEventController`.
2.  El sistema funciona y es seguro a nivel de acceso, pero ahora debemos asegurar la **visibilidad**.

**TUS INSTRUCCIONES ESTRICTAS:**
1.  **NO HAGAS COMMITS AUTOMÁTICOS.** Espérame siempre.
2.  Analiza la sección **"IMMEDIATE PRIORITY: Security Architecture (Phase 2)"** del documento.
3.  Tu tarea inmediata es implementar el **Server-Side Filtering** en `MenuDropdownBuilder`.
    *   Revisa `packages/idei/usim/src/Services/Components/MenuDropdownBuilder.php`.
    *   Modifica el método `toJson()` para que filtre los items basándose en la lista de permisos configurada, antes de generar el array final.
4.  No escribas código todavía. Confirma que entiendes el plan de "Server-Side Filtering" antes de proceder.

Quedo a la espera de tu análisis y ejecución.
