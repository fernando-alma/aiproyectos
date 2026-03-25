/*const CONFIG = {
    // URL Absoluta a la API en producción
    //API_BASE: 'https://aiday-utn-sanrafael-2025.alphadocere.cl/backend/public/',
    
    // Slug por defecto para el dashboard
    SLUG: 'hola' 
};

console.log('API conectada a:', CONFIG.API_BASE); */


// ------------------------------------------------------------------
/**
 * config.js - Inicializador de configuración global
 * Los valores reales vienen inyectados desde el servidor (nav.php)
 * Si por alguna razón no se inyectaron, este archivo actúa como fallback.
 */

if (!window.CONFIG) {
    window.CONFIG = {
        API_BASE: window.location.origin + '/aiproyectos/backend/public/',
        SLUG: 'hola'
    };
}

// Hacemos que la constante sea accesible globalmente de forma sencilla
const CONFIG = window.CONFIG;