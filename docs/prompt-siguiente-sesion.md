# Prompt para Iniciar la Siguiente Sesión

Copia y pega el siguiente texto en el chat de tu próxima sesión con GitHub Copilot para retomar el trabajo exactamente donde lo dejamos.

---

Hola. Estoy retomando el trabajo de extracción y refactorización del framework **USIM**.

Actualmente estoy ubicado en la rama `refactor/extract-usim-incremental`.

En mi sesión anterior, generamos un documento esencial para transferirte el contexto completo: por favor lee detenidamente el archivo **`docs/usim-laravel-package-refactoring.md`**.

Ese documento explica:
1.  **La Arquitectura Actual:** La separación crítica entre `App\UI` (La "Implementación de Referencia" o proyecto limpio) y `packages/idei/usim` (El Core del Framework).
2.  **La Visión del Producto:** Estamos transformando esto en un paquete instalable (`composer require idei/usim`) que debe funcionar "Out-of-the-box" con pantallas estándar (Login, Landing, etc.).
3.  **El Contexto Técnico:** Los cambios recientes masivos en Namespaces (`App\Services` -> `App\UI`) y las correcciones en la lógica de reflexión del framework.

Una vez que hayas analizado ese archivo, por favor:
1.  Confirma tu entendimiento de la distinción entre el Paquete y la App.
2.  **Acción Inmediata:** Quiero comenzar eliminando el sufijo "Service" de las Screens (`LoginService` -> `Login`). Está hardcodeado en el `UIController` y no me gusta.
3.  Luego, avanzaremos con la fase de **"Productización"** (definida en el Roadmap del documento).
