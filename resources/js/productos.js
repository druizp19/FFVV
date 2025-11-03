/* ============================================
   PRODUCTOS MODULE - JavaScript
   ============================================ */

// ==========================================
// 1. INITIALIZATION
// ==========================================

document.addEventListener('DOMContentLoaded', () => {
    console.log('Módulo de Productos cargado correctamente');
    
    // Auto-submit form on select change
    const cicloSelect = document.getElementById('ciclo');
    const estadoSelect = document.getElementById('estado');
    
    if (cicloSelect) {
        cicloSelect.addEventListener('change', () => {
            // Optional: auto-submit on change
            // document.querySelector('.filters-form').submit();
        });
    }
    
    if (estadoSelect) {
        estadoSelect.addEventListener('change', () => {
            // Optional: auto-submit on change
            // document.querySelector('.filters-form').submit();
        });
    }
});
