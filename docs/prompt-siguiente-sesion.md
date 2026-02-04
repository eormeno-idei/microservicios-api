# Prompt para Iniciar la Siguiente Sesión

Copia y pega el siguiente texto en el chat de tu próxima sesión con GitHub Copilot para retomar el trabajo exactamente donde lo dejamos.

---

Hola. Estoy retomando el trabajo de arquitectura del framework **USIM**.

Actualmente estoy ubicado en la rama `refactor/extract-usim-incremental`.
Por favor lee detenidamente el archivo **`docs/usim-laravel-package-refactoring.md`**.

**ESTADO ACTUAL:**
1.  Hemos completado todo el refactor de nombres (`App\Services` -> `App\UI` y eliminación del sufijo "Service").
2.  El sistema funciona, pero identificamos brechas de seguridad en la autorización de pantallas y menús.

**TUS INSTRUCCIONES ESTRICTAS:**
1.  **NO HAGAS COMMITS AUTOMÁTICOS.** Espérame siempre.
2.  Analiza la sección **"IMMEDIATE PRIORITY: Security Architecture"** del documento.
3.  Tu primera tarea es proponer el plan detallado para implementar `authorize(): bool` en `AbstractUIService` y cómo integrarlo en el `UIController`.
4.  No escribas código todavía. Solo preséntame el plan para discutirlo.

Quedo a la espera de tu análisis.
