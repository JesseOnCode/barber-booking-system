document.addEventListener('DOMContentLoaded', function() {
    const navToggle = document.querySelector('.nav-toggle');
    const mainNav = document.querySelector('.main-nav');
    const navLinks = document.querySelectorAll('.main-nav a');
    
    console.log('Nav toggle:', navToggle);
    console.log('Main nav:', mainNav);
    console.log('Nav links found:', navLinks.length);
    
    // Toggle navigaatio kun hamburger-painiketta klikataan
    if (navToggle && mainNav) {
        navToggle.addEventListener('click', function() {
            mainNav.classList.toggle('active');
            console.log('Toggle clicked, active:', mainNav.classList.contains('active'));
        });
    }
    
    // Sulje navigaatio kun mitä tahansa linkkiä klikataan
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            console.log('Link clicked:', this.textContent);
            if (mainNav && mainNav.classList.contains('active')) {
                mainNav.classList.remove('active');
                console.log('Navigation closed');
            }
        });
    });
});