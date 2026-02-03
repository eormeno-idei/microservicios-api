# USIM Framework Refactoring & Extraction Context

> **Note to AI Agent:** This document serves as a "Context Dump" to continue the work of extracting the USIM framework into a standalone product. Read this before starting any task.

## 1. Product Vision (North Star)
**Goal:** Transform `idei/usim` into a standalone, installable Composer package.

**Target User Experience:**
1.  User runs: `composer require idei/usim`
2.  User runs: `php artisan usim:install`
3.  **Result:** The project immediately runs with a functional **Landing Page** and **Default Menu** without the developer writing a single line of UI code.

## 2. Architectural Structure: The Package vs The App
A critical previous step was the physical extraction of the framework core logic into the `packages/idei/usim` directory.

### A. The Framework Core (`packages/idei/usim`)
*   **Namespace:** `Idei\Usim`
*   **Status:** This is the reusable engine. It contains:
    *   **Core Logic:** Builders (`TableBuilder`, `UIContainer`), Base Classes (`AbstractUIService`), and Trait logic.
    *   **Routing:** `UIController` (formerly `UIDemoController`) handles all generic UI endpoints via dynamic routing. The controller itself is part of the package, eliminating the need for boilerplate controllers in the user's app.
    *   **Service Provider:** `UsimServiceProvider` which binds the package to Laravel.
*   **Goal:** This folder will eventually be a separate Git repository.

### B. The Implementation Layer (`App\UI`)
*   **Namespace:** `App\UI` (Previously `App\Services`)
*   **Role:** This layer acts as the **Reference Implementation** and "Test Bed" for the framework.
*   **The "Clean Project" Strategy:** The goal was to strip the host project of any generic framework *logic* (now safely isolated in `packages`), leaving `App/UI` to contain only the *definitions* of the screens.
*   **Current Contents:**
    1.  **Standard Screens:** (`LoginService`, `ProfileService`, `LandingService`) - These are intended to be the "Starter Kit" or "Stubs" that the framework provides to any new project.
    2.  **Demo Screens:** (`TableDemoService`, `DemoMenuService`) - Used to validate framework components during development.
*   **Composer Config:** Check `composer.json`. It defines a local path repository (`"url": "packages/idei/usim"`) to treat the framework as an external dependency, ensuring true decoupling.

## 3. Recent Milestone: Refactoring `App\UI`
**Action:** Renamed and moved the entire directory `App\Services` to `App\UI`.

**Rationale:**
*   **Semantic Improvement (Primary):** The name "Services" was generic and uninformative. The user preferred `App\UI` to explicitly communicate that these classes are responsible for User Interface construction.
*   **Separation of Concerns:** It further distinguishes between the **Application Implementation** (`App\UI`) and the **Framework Core** (`packages/idei/usim`).

## 3. Technical Implementation Details
This refactor involved deep changes to how the framework detects components via Reflection.

### A. Namespace Migration
*   **Old:** `App\Services\Screens`, `App\Services\Components`
*   **New:** `App\UI\Screens`, `App\UI\Components`
*   **Status:** Complete. `grep` checks confirm no references to `App\Services` remain in the codebase.

### B. Framework Internals & Reflection Updates (CRITICAL)
The framework uses `debug_backtrace` to auto-wire components. We modified the core detection logic.

**File:** `packages/idei/usim/src/Services/Components/BaseUIBuilder.php`
*   **Logic Change:** Updated `detectCallingContext`. It now correctly identifies classes in `App\UI` as valid calling contexts and ignores internal framework classes.

**File:** `packages/idei/usim/src/Http/Controllers/UIController.php`
*   **Logic Change:** The dynamic router now looks for screen classes in `App\UI\Screens` by default.

**File:** `config/ui-services.php`
*   **Logic Change:** All service registrations updated to `App\UI\Screens\...`.

### C. Naming Convention Standardized
*   **Action:** Removed "Service" suffix from all Screen classes in `App\UI\Screens`.
*   **Old:** `LoginService`, `AdminDashboardService`.
*   **New:** `Login`, `AdminDashboard`.
*   **Update:** `UIController` no longer appends "Service" automatically.

## 4. Current System State
*   **Repo Status:** Changes committed locally (Refactor `App\Services` -> `App\UI`).
*   **Code Integrity:** No static analysis errors in main services (`AdminDashboard`, `DemoMenu`, etc.).
*   **Location of Demos:** Currently, `DemoUi`, `DemoMenu`, etc., reside in `App\UI\Screens`.
    *   *Context:* These currently act as "User Land" code.

## 5. Roadmap / Next Steps for AI Agent
The next phase is "Productization".

1.  **Define Strategy for Defaults:**
    *   *Question:* Should the "Landing Page" and "Menu" come pre-compiled inside `vendor/idei/usim`?
    *   *Alternative:* Should they be "Stubs" that get published to `App\UI` when running `usim:install`?
    *   *Current leaning:* The goal mentions "Out of the Box", implying the package should serve them by default if no `App\UI` overrides exist.

2.  **Create Installer Command:**
    *   *Question:* Should the "Landing Page" and "Menu" come pre-compiled inside `vendor/idei/usim`?
    *   *Alternative:* Should they be "Stubs" that get published to `App\UI` when running `usim:install`?
    *   *Current leaning:* The goal mentions "Out of the Box", implying the package should serve them by default if no `App\UI` overrides exist.

2.  **Create Installer Command:**
    *   Implement `php artisan usim:install`.
    *   This command should publish assets (CSS/JS) and potentially create the directory structure in `App\UI`.

3.  **Package Isolation Check:**
    *   Ensure `packages/idei/usim` does not have any hardcoded dependencies on `App\Models` or specific implementations of this specific app.

4.  **Physical Extraction:**
    *   Prepare the `packages/idei/usim` folder to be moved to its own git repository eventually.
