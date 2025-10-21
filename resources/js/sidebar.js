/**
 * ============================================
 * Sidebar Functionality - PharmaSales
 * ============================================
 */

// Estado del sidebar
const SIDEBAR_STATE_KEY = 'pharmasales_sidebar_collapsed';
const MOBILE_BREAKPOINT = 768;

/**
 * Inicializa la funcionalidad del sidebar
 */
function initSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const toggleBtn = document.querySelector('.sidebar-toggle');
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const overlay = document.querySelector('.sidebar-overlay');
    
    if (!sidebar) return;

    // Restaurar estado guardado del sidebar (solo en desktop)
    if (window.innerWidth > MOBILE_BREAKPOINT) {
        const isCollapsed = localStorage.getItem(SIDEBAR_STATE_KEY) === 'true';
        if (isCollapsed) {
            sidebar.classList.add('collapsed');
        }
    }

    // Toggle desktop sidebar
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            toggleSidebar();
        });
    }

    // Toggle mobile menu
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', () => {
            toggleMobileSidebar();
        });
    }

    // Cerrar sidebar al hacer clic en overlay
    if (overlay) {
        overlay.addEventListener('click', () => {
            closeMobileSidebar();
        });
    }

    // Cerrar sidebar mobile al hacer clic en un link
    const navLinks = sidebar.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= MOBILE_BREAKPOINT) {
                closeMobileSidebar();
            }
        });
    });

    // Manejar redimensionamiento de ventana
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            handleResize();
        }, 250);
    });

    // Añadir tooltips a los links cuando está colapsado
    addTooltipsToNavLinks();
}

/**
 * Toggle del sidebar en desktop
 */
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    if (!sidebar) return;

    const isCollapsed = sidebar.classList.toggle('collapsed');
    
    // Guardar estado en localStorage
    localStorage.setItem(SIDEBAR_STATE_KEY, isCollapsed);

    // Disparar evento personalizado
    window.dispatchEvent(new CustomEvent('sidebarToggled', { 
        detail: { collapsed: isCollapsed } 
    }));
}

/**
 * Toggle del sidebar en mobile
 */
function toggleMobileSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const overlay = document.querySelector('.sidebar-overlay');
    
    if (!sidebar) return;

    const isOpen = sidebar.classList.toggle('mobile-open');
    
    if (mobileMenuBtn) {
        mobileMenuBtn.classList.toggle('active', isOpen);
    }
    
    if (overlay) {
        overlay.classList.toggle('active', isOpen);
    }

    // Prevenir scroll del body cuando el menú está abierto
    document.body.style.overflow = isOpen ? 'hidden' : '';
}

/**
 * Cierra el sidebar mobile
 */
function closeMobileSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const overlay = document.querySelector('.sidebar-overlay');
    
    if (!sidebar) return;

    sidebar.classList.remove('mobile-open');
    
    if (mobileMenuBtn) {
        mobileMenuBtn.classList.remove('active');
    }
    
    if (overlay) {
        overlay.classList.remove('active');
    }

    document.body.style.overflow = '';
}

/**
 * Maneja el redimensionamiento de la ventana
 */
function handleResize() {
    const sidebar = document.querySelector('.sidebar');
    if (!sidebar) return;

    if (window.innerWidth > MOBILE_BREAKPOINT) {
        // En desktop, cerrar menú mobile y restaurar estado guardado
        closeMobileSidebar();
        const isCollapsed = localStorage.getItem(SIDEBAR_STATE_KEY) === 'true';
        sidebar.classList.toggle('collapsed', isCollapsed);
    } else {
        // En mobile, asegurar que no esté colapsado
        sidebar.classList.remove('collapsed');
    }
}

/**
 * Añade tooltips a los nav links para el estado colapsado
 */
function addTooltipsToNavLinks() {
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        const textSpan = link.querySelector('span:last-child');
        if (textSpan) {
            const tooltipText = textSpan.textContent.trim();
            link.setAttribute('data-tooltip', tooltipText);
        }
    });
}

/**
 * Función global para exponer en window
 */
window.toggleSidebar = toggleSidebar;
window.toggleMobileSidebar = toggleMobileSidebar;

/**
 * Inicializar cuando el DOM esté listo
 */
document.addEventListener('DOMContentLoaded', () => {
    initSidebar();
    
    console.log('✅ Sidebar inicializado');
});
/**
 * Funciones disponibles globalmente
 */
// Las funciones ya están definidas globalmente arriba, no necesitan export


