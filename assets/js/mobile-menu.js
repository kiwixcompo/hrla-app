/**
 * Mobile Menu Functionality
 * Universal mobile menu toggle for all pages
 */
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (mobileMenuToggle && navMenu) {
        // Toggle menu on button click
        mobileMenuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const isOpen = navMenu.classList.contains('mobile-open');
            
            if (isOpen) {
                navMenu.classList.remove('mobile-open');
                this.setAttribute('aria-expanded', 'false');
                this.innerHTML = '<span class="hamburger-icon">≡</span>';
            } else {
                navMenu.classList.add('mobile-open');
                this.setAttribute('aria-expanded', 'true');
                this.innerHTML = '<span class="hamburger-icon">✕</span>';
            }
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!navMenu.contains(e.target) && !mobileMenuToggle.contains(e.target)) {
                navMenu.classList.remove('mobile-open');
                mobileMenuToggle.setAttribute('aria-expanded', 'false');
                mobileMenuToggle.innerHTML = '<span class="hamburger-icon">≡</span>';
            }
        });
        
        // Close menu when clicking on a link
        const navLinks = navMenu.querySelectorAll('.nav-link, .btn');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                navMenu.classList.remove('mobile-open');
                mobileMenuToggle.setAttribute('aria-expanded', 'false');
                mobileMenuToggle.innerHTML = '<span class="hamburger-icon">≡</span>';
            });
        });
        
        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                navMenu.classList.remove('mobile-open');
                mobileMenuToggle.setAttribute('aria-expanded', 'false');
                mobileMenuToggle.innerHTML = '<span class="hamburger-icon">≡</span>';
            }
        });
    }
});