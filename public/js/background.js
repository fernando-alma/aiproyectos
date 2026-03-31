document.addEventListener('DOMContentLoaded', () => {
    const orb1 = document.querySelector('.orb-1');
    const orb2 = document.querySelector('.orb-2');
    
    if(!orb1 || !orb2) return;

    // Efecto parallax en mousemove
    document.addEventListener('mousemove', (e) => {
        const { clientX, clientY } = e;
        
        // Calculamos offsets basados en el centro de la pantalla
        const windowWidth = window.innerWidth;
        const windowHeight = window.innerHeight;
        
        // Movimiento sutil hacia el mouse (orb 1)
        const moveX1 = (clientX - windowWidth / 2) * 0.1;
        const moveY1 = (clientY - windowHeight / 2) * 0.1;

        // Movimiento sutil en contra (orb 2)
        const moveX2 = (clientX - windowWidth / 2) * -0.05;
        const moveY2 = (clientY - windowHeight / 2) * -0.05;

        orb1.style.transform = `translate(${moveX1}px, ${moveY1}px)`;
        orb2.style.transform = `translate(${moveX2}px, ${moveY2}px)`;
    });
});
