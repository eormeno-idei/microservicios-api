/**
 * Configuración de la aplicación
 */
const API_BASE_URL = 'http://localhost:8000/api';
const TOKEN_KEY = 'auth_token';
const USER_KEY = 'user_data';

/**
 * Referencias a elementos del DOM
 */
const elements = {
    // Elementos principales
    loading: document.getElementById('loading'),
    content: document.getElementById('content'),
    error: document.getElementById('error'),
    errorMessage: document.getElementById('errorMessage'),
    apiMessage: document.getElementById('apiMessage'),
    apiVersion: document.getElementById('apiVersion'),
    apiTimestamp: document.getElementById('apiTimestamp'),

    // Secciones protegidas
    protectedSection: document.getElementById('protectedSection'),
    adminSection: document.getElementById('adminSection'),

    // Navegación
    userWelcome: document.getElementById('userWelcome'),
    btnLogin: document.getElementById('btnLogin'),
    btnLogout: document.getElementById('btnLogout'),

    // Modales
    loginModal: document.getElementById('loginModal'),
    registerModal: document.getElementById('registerModal'),
    closeLoginModal: document.getElementById('closeLoginModal'),
    closeRegisterModal: document.getElementById('closeRegisterModal'),
    showRegisterModal: document.getElementById('showRegisterModal'),
    showLoginModal: document.getElementById('showLoginModal'),

    // Formulario de Login
    loginForm: document.getElementById('loginForm'),
    loginEmail: document.getElementById('loginEmail'),
    loginPassword: document.getElementById('loginPassword'),
    rememberMe: document.getElementById('rememberMe'),
    loginError: document.getElementById('loginError'),
    loginErrorMessage: document.getElementById('loginErrorMessage'),
    btnSubmitLogin: document.getElementById('btnSubmitLogin'),

    // Formulario de Registro
    registerForm: document.getElementById('registerForm'),
    registerFirstName: document.getElementById('registerFirstName'),
    registerLastName: document.getElementById('registerLastName'),
    registerMobile: document.getElementById('registerMobile'),
    registerEmail: document.getElementById('registerEmail'),
    registerPassword: document.getElementById('registerPassword'),
    registerPasswordConfirm: document.getElementById('registerPasswordConfirm'),
    registerSemanticContext: document.getElementById('registerSemanticContext'),
    registerError: document.getElementById('registerError'),
    registerErrorMessage: document.getElementById('registerErrorMessage'),
    btnSubmitRegister: document.getElementById('btnSubmitRegister'),

    // Botones de acción
    btnUserAction: document.getElementById('btnUserAction'),
    btnAdminAction: document.getElementById('btnAdminAction'),
};

/**
 * Estado de la aplicación
 */
const appState = {
    isAuthenticated: false,
    user: null,
    token: null,
    permissions: [],
    roles: [],
};

/**
 * Clase para manejar el almacenamiento local
 */
class StorageManager {
    static setToken(token) {
        localStorage.setItem(TOKEN_KEY, token);
    }

    static getToken() {
        return localStorage.getItem(TOKEN_KEY);
    }

    static removeToken() {
        localStorage.removeItem(TOKEN_KEY);
    }

    static setUser(user) {
        localStorage.setItem(USER_KEY, JSON.stringify(user));
    }

    static getUser() {
        const user = localStorage.getItem(USER_KEY);
        return user ? JSON.parse(user) : null;
    }

    static removeUser() {
        localStorage.removeItem(USER_KEY);
    }

    static clear() {
        this.removeToken();
        this.removeUser();
    }
}

/**
 * Clase para manejar las peticiones a la API
 */
class ApiClient {
    constructor(baseUrl) {
        this.baseUrl = baseUrl;
    }

    /**
     * Obtener headers con autenticación
     */
    getHeaders(includeAuth = false) {
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        };

        if (includeAuth && appState.token) {
            headers['Authorization'] = `Bearer ${appState.token}`;
        }

        return headers;
    }

    /**
     * Petición GET
     */
    async get(endpoint, authenticated = false) {
        try {
            const response = await fetch(`${this.baseUrl}${endpoint}`, {
                method: 'GET',
                headers: this.getHeaders(authenticated),
            });

            return await this.handleResponse(response);
        } catch (error) {
            console.error('Error en GET:', error);
            throw error;
        }
    }

    /**
     * Petición POST
     */
    async post(endpoint, body, authenticated = false) {
        try {
            const response = await fetch(`${this.baseUrl}${endpoint}`, {
                method: 'POST',
                headers: this.getHeaders(authenticated),
                body: JSON.stringify(body),
            });

            return await this.handleResponse(response);
        } catch (error) {
            console.error('Error en POST:', error);
            throw error;
        }
    }

    /**
     * Manejar respuesta de la API
     */
    async handleResponse(response) {
        const data = await response.json();

        if (!response.ok) {
            // Si hay error de validación, extraer los mensajes
            if (data.errors) {
                const errorMessages = Object.values(data.errors).flat().join(', ');
                throw new Error(errorMessages);
            }
            throw new Error(data.message || `HTTP error! status: ${response.status}`);
        }

        return data;
    }
}

/**
 * Instancia del cliente API
 */
const apiClient = new ApiClient(API_BASE_URL);

/**
 * Clase para manejar la autenticación
 */
class AuthManager {
    /**
     * Iniciar sesión
     */
    static async login(email, password) {
        const data = await apiClient.post('/login', {
            email,
            password,
        });

        // Verificar que la respuesta sea exitosa
        if (data.status !== 'success') {
            throw new Error(data.message || 'Error en el login');
        }

        // Guardar token y usuario
        StorageManager.setToken(data.data.token);
        StorageManager.setUser(data.data.user);

        // Actualizar estado de la aplicación
        appState.isAuthenticated = true;
        appState.token = data.data.token;
        appState.user = data.data.user;

        // Obtener permisos y roles de Spatie si existen
        appState.permissions = data.data.user.permissions || [];
        appState.roles = data.data.user.roles || [];

        return data;
    }

    /**
     * Registrar usuario
     */
    static async register(userData) {
        const data = await apiClient.post('/register', {
            first_name: userData.firstName,
            last_name: userData.lastName,
            mobile: userData.mobile || null,
            semantic_context: userData.semanticContext || null,
            email: userData.email,
            password: userData.password,
            password_confirmation: userData.passwordConfirmation,
        });

        // Verificar que la respuesta sea exitosa
        if (!data.status || data.status !== 'success') {
            throw new Error(data.message || 'Error en el registro');
        }

        // Guardar token y usuario
        StorageManager.setToken(data.data.token);
        StorageManager.setUser(data.data.user);

        // Actualizar estado de la aplicación
        appState.isAuthenticated = true;
        appState.token = data.data.token;
        appState.user = data.data.user;

        // Obtener permisos y roles de Spatie si existen
        appState.permissions = data.data.user.permissions || [];
        appState.roles = data.data.user.roles || [];

        return data;
    }

    /**
     * Cerrar sesión
     */
    static async logout() {
        try {
            await apiClient.post('/logout', {}, true);
        } catch (error) {
            console.error('Error al cerrar sesión:', error);
        } finally {
            // Limpiar estado local siempre
            this.clearSession();
        }
    }

    /**
     * Limpiar sesión local
     */
    static clearSession() {
        StorageManager.clear();
        appState.isAuthenticated = false;
        appState.token = null;
        appState.user = null;
        appState.permissions = [];
        appState.roles = [];
    }

    /**
     * Verificar si hay sesión guardada
     */
    static checkStoredSession() {
        const token = StorageManager.getToken();
        const user = StorageManager.getUser();

        if (token && user) {
            appState.isAuthenticated = true;
            appState.token = token;
            appState.user = user;
            appState.permissions = user.permissions || [];
            appState.roles = user.roles || [];
            return true;
        }

        return false;
    }

    /**
     * Obtener usuario actual del servidor
     */
    static async getCurrentUser() {
        const data = await apiClient.get('/user', true);

        if (!data.success) {
            throw new Error(data.message || 'Error al obtener usuario');
        }

        // Actualizar usuario almacenado
        StorageManager.setUser(data.data);
        appState.user = data.data;
        appState.permissions = data.data.permissions || [];
        appState.roles = data.data.roles || [];

        return data.data;
    }
}

/**
 * Clase para manejar permisos
 */
class PermissionManager {
    /**
     * Verificar si el usuario tiene un permiso específico
     */
    static hasPermission(permission) {
        return appState.permissions.includes(permission);
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    static hasRole(role) {
        return appState.roles.includes(role);
    }

    /**
     * Verificar si el usuario es administrador
     */
    static isAdmin() {
        return this.hasRole('admin');
    }

    /**
     * Verificar si el usuario tiene alguno de los permisos especificados
     */
    static hasAnyPermission(permissions) {
        return permissions.some(permission => this.hasPermission(permission));
    }
}

/**
 * Clase para manejar la interfaz de usuario
 */
class UIManager {
    /**
     * Mostrar el estado de carga
     */
    showLoading() {
        elements.loading.style.display = 'flex';
        elements.content.style.display = 'none';
        elements.error.style.display = 'none';
    }

    /**
     * Mostrar el contenido principal
     */
    showContent() {
        elements.loading.style.display = 'none';
        elements.content.style.display = 'block';
        elements.error.style.display = 'none';
    }

    /**
     * Mostrar un mensaje de error
     */
    showError(message) {
        elements.loading.style.display = 'none';
        elements.content.style.display = 'none';
        elements.error.style.display = 'block';
        elements.errorMessage.textContent = message;
    }

    /**
     * Actualizar el contenido con los datos de la API
     */
    updateContent(data) {
        elements.apiMessage.textContent = data.message;
        elements.apiVersion.textContent = data.version;
        elements.apiTimestamp.textContent = this.formatTimestamp(data.timestamp);
    }

    /**
     * Formatea un timestamp para mostrar
     */
    formatTimestamp(timestamp) {
        const date = new Date(timestamp);
        return date.toLocaleString('es-ES', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
    }

    /**
     * Actualizar la visibilidad de secciones según autenticación y permisos
     */
    updateAuthUI() {
        if (appState.isAuthenticated) {
            // Mostrar nombre de usuario
            elements.userWelcome.textContent = `Hola, ${appState.user.name}`;
            elements.userWelcome.style.display = 'inline';

            // Botones de navegación
            elements.btnLogin.style.display = 'none';
            elements.btnLogout.style.display = 'block';

            // Sección de usuario autenticado
            elements.protectedSection.style.display = 'block';

            // Sección de administrador (solo si tiene el permiso)
            if (PermissionManager.hasPermission('acceder-panel-admin')) {
                elements.adminSection.style.display = 'block';
            } else {
                elements.adminSection.style.display = 'none';
            }
        } else {
            // Usuario no autenticado
            elements.userWelcome.style.display = 'none';
            elements.btnLogin.style.display = 'block';
            elements.btnLogout.style.display = 'none';
            elements.protectedSection.style.display = 'none';
            elements.adminSection.style.display = 'none';
        }
    }

    /**
     * Mostrar modal
     */
    showModal(modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    /**
     * Ocultar modal
     */
    hideModal(modal) {
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    /**
     * Mostrar error en formulario
     */
    showFormError(errorElement, messageElement, message) {
        errorElement.style.display = 'block';
        messageElement.textContent = message;
    }

    /**
     * Ocultar error en formulario
     */
    hideFormError(errorElement) {
        errorElement.style.display = 'none';
    }

    /**
     * Resetear formulario
     */
    resetForm(form) {
        form.reset();
    }

    /**
     * Deshabilitar botón de submit
     */
    disableSubmitButton(button, text = 'Procesando...') {
        button.disabled = true;
        button.dataset.originalText = button.textContent;
        button.textContent = text;
    }

    /**
     * Habilitar botón de submit
     */
    enableSubmitButton(button) {
        button.disabled = false;
        button.textContent = button.dataset.originalText || 'Enviar';
    }
}

/**
 * Instancia del gestor de UI
 */
const uiManager = new UIManager();

/**
 * Controlador principal de la aplicación
 */
class AppController {
    /**
     * Inicializar la aplicación
     */
    async init() {
        console.log('Inicializando aplicación...');

        // Verificar si hay sesión guardada
        const hasStoredSession = AuthManager.checkStoredSession();

        if (hasStoredSession) {
            console.log('Sesión encontrada, verificando con servidor...');
            try {
                // Verificar que el token siga siendo válido
                await AuthManager.getCurrentUser();
                console.log('Sesión válida');
            } catch (error) {
                console.error('Sesión expirada:', error);
                AuthManager.clearSession();
            }
        }

        // Registrar event listeners
        this.registerEventListeners();

        // Cargar datos iniciales
        await this.loadLandingData();

        // Actualizar UI según estado de autenticación
        uiManager.updateAuthUI();
    }

    /**
     * Registrar los event listeners de la aplicación
     */
    registerEventListeners() {
        // Botones de navegación
        elements.btnLogin.addEventListener('click', () => {
            uiManager.showModal(elements.loginModal);
        });

        elements.btnLogout.addEventListener('click', async () => {
            await this.handleLogout();
        });

        // Cerrar modales
        elements.closeLoginModal.addEventListener('click', () => {
            uiManager.hideModal(elements.loginModal);
            uiManager.hideFormError(elements.loginError);
        });

        elements.closeRegisterModal.addEventListener('click', () => {
            uiManager.hideModal(elements.registerModal);
            uiManager.hideFormError(elements.registerError);
        });

        // Cerrar modal al hacer clic fuera
        elements.loginModal.addEventListener('click', (e) => {
            if (e.target === elements.loginModal) {
                uiManager.hideModal(elements.loginModal);
            }
        });

        elements.registerModal.addEventListener('click', (e) => {
            if (e.target === elements.registerModal) {
                uiManager.hideModal(elements.registerModal);
            }
        });

        // Alternar entre modales
        elements.showRegisterModal.addEventListener('click', (e) => {
            e.preventDefault();
            uiManager.hideModal(elements.loginModal);
            uiManager.showModal(elements.registerModal);
        });

        elements.showLoginModal.addEventListener('click', (e) => {
            e.preventDefault();
            uiManager.hideModal(elements.registerModal);
            uiManager.showModal(elements.loginModal);
        });

        // Formularios
        elements.loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleLogin();
        });

        elements.registerForm.addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleRegister();
        });

        // Botones de acción
        elements.btnUserAction.addEventListener('click', () => {
            this.handleUserAction();
        });

        elements.btnAdminAction.addEventListener('click', () => {
            this.handleAdminAction();
        });
    }

    /**
     * Cargar los datos de landing desde la API
     */
    async loadLandingData() {
        try {
            uiManager.showLoading();
            const data = await apiClient.get('/landing');

            if (data.status === 'success') {
                uiManager.updateContent(data);
                uiManager.showContent();
            } else {
                throw new Error('La respuesta de la API no fue exitosa');
            }
        } catch (error) {
            console.error('Error al cargar datos:', error);
            uiManager.showError(
                'No se pudo conectar con la API. Verifica que el servidor esté ejecutándose.'
            );
        }
    }

    /**
     * Manejar el login
     */
    async handleLogin() {
        const email = elements.loginEmail.value.trim();
        const password = elements.loginPassword.value;
        const remember = elements.rememberMe.checked;

        // Ocultar errores previos
        uiManager.hideFormError(elements.loginError);

        // Validación básica
        if (!email || !password) {
            uiManager.showFormError(
                elements.loginError,
                elements.loginErrorMessage,
                'Por favor, completa todos los campos.'
            );
            return;
        }

        try {
            uiManager.disableSubmitButton(elements.btnSubmitLogin, 'Iniciando sesión...');

            await AuthManager.login(email, password);

            // Cerrar modal y actualizar UI
            uiManager.hideModal(elements.loginModal);
            uiManager.resetForm(elements.loginForm);
            uiManager.updateAuthUI();

            console.log('Login exitoso:', appState.user);

        } catch (error) {
            console.error('Error en login:', error);
            uiManager.showFormError(
                elements.loginError,
                elements.loginErrorMessage,
                error.message || 'Error al iniciar sesión. Verifica tus credenciales.'
            );
        } finally {
            uiManager.enableSubmitButton(elements.btnSubmitLogin);
        }
    }

    /**
     * Manejar el registro
     */
    async handleRegister() {
        // Obtener valores del formulario
        const firstName = elements.registerFirstName.value.trim();
        const lastName = elements.registerLastName.value.trim();
        const mobile = elements.registerMobile.value.trim();
        const email = elements.registerEmail.value.trim();
        const password = elements.registerPassword.value;
        const passwordConfirm = elements.registerPasswordConfirm.value;
        const semanticContext = elements.registerSemanticContext.value.trim();

        // Ocultar errores previos
        uiManager.hideFormError(elements.registerError);

        // Validación básica de campos requeridos
        if (!firstName || !lastName || !email || !password || !passwordConfirm) {
            uiManager.showFormError(
                elements.registerError,
                elements.registerErrorMessage,
                'Por favor, completa todos los campos obligatorios.'
            );
            return;
        }

        // Validar que las contraseñas coincidan
        if (password !== passwordConfirm) {
            uiManager.showFormError(
                elements.registerError,
                elements.registerErrorMessage,
                'Las contraseñas no coinciden.'
            );
            return;
        }

        // Validar longitud mínima de contraseña
        if (password.length < 8) {
            uiManager.showFormError(
                elements.registerError,
                elements.registerErrorMessage,
                'La contraseña debe tener al menos 8 caracteres.'
            );
            return;
        }

        try {
            uiManager.disableSubmitButton(elements.btnSubmitRegister, 'Registrando...');

            // Preparar datos del usuario
            const userData = {
                firstName,
                lastName,
                mobile,
                email,
                password,
                passwordConfirmation: passwordConfirm,
                semanticContext
            };

            await AuthManager.register(userData);

            // Cerrar modal y actualizar UI
            uiManager.hideModal(elements.registerModal);
            uiManager.resetForm(elements.registerForm);
            uiManager.updateAuthUI();

            console.log('Registro exitoso:', appState.user);
            alert(`¡Cuenta creada exitosamente! Bienvenido, ${appState.user.name}!`);

        } catch (error) {
            console.error('Error en registro:', error);
            uiManager.showFormError(
                elements.registerError,
                elements.registerErrorMessage,
                error.message || 'Error al crear la cuenta. Intenta nuevamente.'
            );
        } finally {
            uiManager.enableSubmitButton(elements.btnSubmitRegister);
        }
    }

    /**
     * Manejar el logout
     */
    async handleLogout() {
        if (!confirm('¿Estás seguro de que deseas cerrar sesión?')) {
            return;
        }

        try {
            await AuthManager.logout();
            uiManager.updateAuthUI();
            console.log('Logout exitoso');
        } catch (error) {
            console.error('Error en logout:', error);
            alert('Error al cerrar sesión');
        }
    }

    /**
     * Manejar acción de usuario
     */
    async handleUserAction() {
        try {
            // Obtener datos actuales del usuario
            const data = await apiClient.get('/user', true);

            if (data.success) {
                const user = data.data;
                console.log('Perfil de usuario:', user);

                const userInfo = `
                    === PERFIL DE USUARIO ===
                    Nombre: ${user.name}
                    Email: ${user.email}
                    Roles: ${user.roles?.join(', ') || 'Ninguno'}
                    Permisos: ${user.permissions?.join(', ') || 'Ninguno'}
                    Email verificado: ${user.email_verified_at ? 'Sí' : 'No'}
                `;

                alert(userInfo);
            }
        } catch (error) {
            console.error('Error al obtener perfil:', error);
            alert('Error al cargar el perfil de usuario');
        }
    }

    /**
     * Manejar acción de administrador
     */
    async handleAdminAction() {
        // Verificar que tenga el permiso necesario
        if (!PermissionManager.hasPermission('acceder-panel-admin')) {
            alert('No tienes permisos para acceder al panel de administración');
            return;
        }

        // Mostrar información de administrador
        const adminInfo = `
            === PANEL DE ADMINISTRADOR ===
            Usuario: ${appState.user.name}
            Roles: ${appState.roles.join(', ')}
            Permisos: ${appState.permissions.join(', ')}

            Este panel permite gestionar:
            - Usuarios del sistema
            - Roles y permisos
            - Configuración general

            (La implementación completa requiere endpoints adicionales en el backend)
        `;

        alert(adminInfo);
    }
}

/**
 * Punto de entrada de la aplicación
 */
document.addEventListener('DOMContentLoaded', () => {
    const app = new AppController();
    app.init();
});
