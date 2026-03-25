/*const CONFIG = {
    // URL Absoluta a la API en producción
    //API_BASE: 'https://aiday-utn-sanrafael-2025.alphadocere.cl/backend/public/',
    
    // Slug por defecto para el dashboard
    SLUG: 'hola' 
};

console.log('API conectada a:', CONFIG.API_BASE); */


// ------------------------------------------------------------------

// Detección automática del entorno (Local vs Producción)
const isLocalhost = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';

const CONFIG = {
    // Si estamos en XAMPP usa la carpeta 'aiproyectos', si es producción usa la raíz
    API_BASE: isLocalhost 
        ? window.location.origin + '/aiproyectos/backend/public/' 
        : window.location.origin + '/backend/public/',
    
    // Slug por defecto para el dashboard
    SLUG: 'hola' 
};

console.log('API conectada a:', CONFIG.API_BASE);