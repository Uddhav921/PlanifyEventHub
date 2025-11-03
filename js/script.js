// General Scripts
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const nav = document.querySelector('nav ul');
    
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            nav.classList.toggle('active');
        });
    }
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href').substring(1);
            if (!targetId) return;
            
            const targetElement = document.getElementById(targetId);
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 70,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Form validation
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const requiredFields = form.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    
                    // Add error styling
                    field.classList.add('error');
                    
                    // Create error message if it doesn't exist
                    let errorMsg = field.nextElementSibling;
                    if (!errorMsg || !errorMsg.classList.contains('error-text')) {
                        errorMsg = document.createElement('div');
                        errorMsg.classList.add('error-text');
                        field.parentNode.insertBefore(errorMsg, field.nextSibling);
                    }
                    
                    errorMsg.textContent = 'This field is required';
                } else {
                    // Remove error styling
                    field.classList.remove('error');
                    
                    // Remove error message if it exists
                    const errorMsg = field.nextElementSibling;
                    if (errorMsg && errorMsg.classList.contains('error-text')) {
                        errorMsg.remove();
                    }
                    
                    // Email validation
                    if (field.type === 'email' && !isValidEmail(field.value)) {
                        isValid = false;
                        field.classList.add('error');
                        
                        let errorMsg = field.nextElementSibling;
                        if (!errorMsg || !errorMsg.classList.contains('error-text')) {
                            errorMsg = document.createElement('div');
                            errorMsg.classList.add('error-text');
                            field.parentNode.insertBefore(errorMsg, field.nextSibling);
                        }
                        
                        errorMsg.textContent = 'Please enter a valid email address';
                    }
                }
            });
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    });
    
    // Email validation helper function
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    // Initialize image preview for file inputs
    const fileInputs = document.querySelectorAll('input[type="file"]');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            const preview = document.getElementById(input.id + '-preview');
            if (preview) {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }
                    
                    reader.readAsDataURL(this.files[0]);
                }
            }
        });
    });
});
    //for contact section redirection
    