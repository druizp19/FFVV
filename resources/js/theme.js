// ============================================
// Sistema de Temas - PharmaSales
// ============================================

/**
 * Gestor de temas para la aplicación
 */
class ThemeManager {
    constructor() {
        this.THEME_KEY = 'pharmasales-theme';
        this.THEME_LIGHT = 'light';
        this.THEME_DARK = 'dark';
        this.currentTheme = null;
        
        this.init();
    }

    /**
     * Inicializa el gestor de temas
     */
    init() {
        // Cargar tema guardado o usar el del sistema
        const savedTheme = this.getSavedTheme();
        const systemTheme = this.getSystemTheme();
        const initialTheme = savedTheme || systemTheme;
        
        this.setTheme(initialTheme, false);
        
        // Escuchar cambios en el tema del sistema
        this.watchSystemTheme();
    }

    /**
     * Obtiene el tema guardado en localStorage
     */
    getSavedTheme() {
        return localStorage.getItem(this.THEME_KEY);
    }

    /**
     * Obtiene el tema preferido del sistema
     */
    getSystemTheme() {
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches) {
            return this.THEME_LIGHT;
        }
        return this.THEME_DARK;
    }

    /**
     * Establece el tema
     * @param {string} theme - 'light' o 'dark'
     * @param {boolean} save - Si debe guardar la preferencia
     */
    setTheme(theme, save = true) {
        // Validar el tema
        if (theme !== this.THEME_LIGHT && theme !== this.THEME_DARK) {
            console.warn(`Tema inválido: ${theme}. Usando tema oscuro.`);
            theme = this.THEME_DARK;
        }

        // Aplicar el tema
        if (theme === this.THEME_LIGHT) {
            document.documentElement.setAttribute('data-theme', 'light');
        } else {
            document.documentElement.removeAttribute('data-theme');
        }

        this.currentTheme = theme;

        // Guardar preferencia si se solicita
        if (save) {
            localStorage.setItem(this.THEME_KEY, theme);
        }

        // Actualizar el ícono del botón si existe
        this.updateToggleButton();

        // Disparar evento personalizado
        window.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme } }));
    }

    /**
     * Alterna entre tema claro y oscuro
     */
    toggleTheme() {
        const newTheme = this.currentTheme === this.THEME_LIGHT ? this.THEME_DARK : this.THEME_LIGHT;
        this.setTheme(newTheme);
    }

    /**
     * Actualiza el ícono del botón de cambio de tema
     */
    updateToggleButton() {
        const button = document.getElementById('theme-toggle');
        if (!button) return;

        const icon = button.querySelector('.theme-icon');
        if (!icon) return;

        if (this.currentTheme === this.THEME_LIGHT) {
            icon.textContent = '🌙';
            button.setAttribute('aria-label', 'Cambiar a tema oscuro');
        } else {
            icon.textContent = '☀️';
            button.setAttribute('aria-label', 'Cambiar a tema claro');
        }
    }

    /**
     * Escucha cambios en el tema del sistema
     */
    watchSystemTheme() {
        if (!window.matchMedia) return;

        const mediaQuery = window.matchMedia('(prefers-color-scheme: light)');
        
        mediaQuery.addEventListener('change', (e) => {
            // Solo cambiar si no hay preferencia guardada
            if (!this.getSavedTheme()) {
                const newTheme = e.matches ? this.THEME_LIGHT : this.THEME_DARK;
                this.setTheme(newTheme, false);
            }
        });
    }

    /**
     * Obtiene el tema actual
     */
    getCurrentTheme() {
        return this.currentTheme;
    }

    /**
     * Resetea el tema a la preferencia del sistema
     */
    resetToSystem() {
        localStorage.removeItem(this.THEME_KEY);
        const systemTheme = this.getSystemTheme();
        this.setTheme(systemTheme, false);
    }
}

// Crear instancia global del gestor de temas
const themeManager = new ThemeManager();

// Exportar para uso en otros módulos
window.themeManager = themeManager;

// Función global para toggle (usada en el botón)
window.toggleTheme = function() {
    themeManager.toggleTheme();
};

// Log de inicialización (opcional, se puede comentar en producción)
console.log('🎨 Sistema de temas inicializado. Tema actual:', themeManager.getCurrentTheme());

