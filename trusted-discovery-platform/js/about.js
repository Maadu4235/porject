// About Page JavaScript

// Initialize the about page
document.addEventListener('DOMContentLoaded', function() {
    initializeAboutPage();
    initializeEventListeners();
});

function initializeAboutPage() {
    console.log('Dwar About Page initialized');
    // Any about page specific initialization
}

function initializeEventListeners() {
    // Auth form submission
    document.getElementById('authForm').addEventListener('submit', function(e) {
        e.preventDefault();
        handleAuthFormSubmit();
    });
}

function handleAuthFormSubmit() {
    const isRegister = document.getElementById('registerFields').style.display !== 'none';
    const phone = document.getElementById('phone').value;
    const password = document.getElementById('password').value;
    const fullName = document.getElementById('fullName').value;
    
    // Simple validation
    if (isRegister && !fullName) {
        alert('Please enter your full name');
        return;
    }
    
    if (!phone || !password) {
        alert('Please fill in all required fields');
        return;
    }
    
    // Simulate API call
    showLoading();
    setTimeout(() => {
        hideLoading();
        alert(`${isRegister ? 'Registration' : 'Login'} successful! Welcome to Dwar.`);
        bootstrap.Modal.getInstance(document.getElementById('authModal')).hide();
        document.getElementById('authForm').reset();
    }, 1500);
}

function showLoginModal() {
    document.getElementById('authModalTitle').textContent = 'Login to Dwar';
    document.getElementById('registerFields').style.display = 'none';
    document.getElementById('authButton').textContent = 'Login';
    document.getElementById('fullName').required = false;
    new bootstrap.Modal(document.getElementById('authModal')).show();
}

function showRegisterModal() {
    document.getElementById('authModalTitle').textContent = 'Join Dwar Community';
    document.getElementById('registerFields').style.display = 'block';
    document.getElementById('authButton').textContent = 'Register';
    document.getElementById('fullName').required = true;
    new bootstrap.Modal(document.getElementById('authModal')).show();
}

function showLoading() {
    const button = document.getElementById('authButton');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    button.disabled = true;
}

function hideLoading() {
    const button = document.getElementById('authButton');
    const isRegister = document.getElementById('registerFields').style.display !== 'none';
    button.innerHTML = isRegister ? 'Register' : 'Login';
    button.disabled = false;
}

// Smooth scrolling for navigation
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Animation on scroll
function animateOnScroll() {
    const elements = document.querySelectorAll('.problem-card, .value-card, .approach-card, .story-card');
    
    elements.forEach(element => {
        const elementTop = element.getBoundingClientRect().top;
        const elementVisible = 150;
        
        if (elementTop < window.innerHeight - elementVisible) {
            element.style.opacity = "1";
            element.style.transform = "translateY(0)";
        }
    });
}

// Initialize animations
window.addEventListener('scroll', animateOnScroll);