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
The next phase is "Productization" and **Security Consolidation**.

### IMMEDIATE PRIORITY: Security Architecture
The user validated the current refactor but identified authorized access as a critical next step before packaging.

1.  **Authorization Gate (`authorize(): bool`)**:
    *   **Goal:** Implement a self-contained security check within `AbstractUIService`.
    *   **Proposal:** Add `public function authorize(): bool` (defaulting to true) to the base class.
    *   **Implementation:** Before rendering any Screen or handling an Event, the Controller MUST call this method. If `false`, abort with 403.
    *   **Benefit:** Keeps security logic inside the Screen class (e.g., `AdminDashboard::authorize()` checks `auth()->user()->isAdmin()`).

2.  **Menu Visibility Automation**:
    *   **Goal:** The Menu Builder currently sends ALL items to the frontend (leaving hiding logic to JS). This is insecure (info disclosure).
    *   **Proposal:** Move the filtering logic to the Backend (`toJson` method of `MenuDropdownBuilder`). It should check permissions against the user *before* sending the payload.
    *   **Advanced Idea:** Could the Menu Builder automatically check the `authorize()` method of the target Screen class instead of relying on manual string permissions like `'auth'`? (To be discussed).

3.  **Productization Strategy (Defaults):**
    *   Define if Landing/Menu should be internal defaults or published stubs.

## 6. Rules of Engagement for AI Agent
1.  **NO AUTO-COMMITS:** Do NOT commit any code unless explicitly asked by the user.
2.  **PLAN FIRST:** Before implementing any feature (especially the `authorize` logic), summarize your plan and wait for user confirmation ("Go ahead").
3.  **Context Awareness:** Always assume the user is continuing from a previous session on a different machine. Check the git status and file system first.
