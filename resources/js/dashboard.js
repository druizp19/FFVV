/* ============================================
   DASHBOARD MODULE - JavaScript
   ============================================ */

// ==========================================
// 1. INITIALIZATION
// ==========================================

document.addEventListener('DOMContentLoaded', () => {
    console.log('Dashboard cargado correctamente');

    // Animación de números en las tarjetas de estadísticas
    animateStatNumbers();
});

// ==========================================
// 2. UTILITY FUNCTIONS
// ==========================================

/**
 * Anima los números de las tarjetas de estadísticas
 */
function animateStatNumbers() {
    const statValues = document.querySelectorAll('.stat-value');
    
    statValues.forEach(stat => {
        const target = parseInt(stat.textContent);
        if (isNaN(target)) return;
        
        let current = 0;
        const increment = target / 50;
        const duration = 1000;
        const stepTime = duration / 50;
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                stat.textContent = target;
                clearInterval(timer);
            } else {
                stat.textContent = Math.floor(current);
            }
        }, stepTime);
    });
}


