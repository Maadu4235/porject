// API Base URL - Updated to port 5001
const API_BASE = 'http://localhost:5000/api';

// Sample data for demo
const sampleListings = [
    {
        _id: "1",
        title: "Electrician Needed - Local Workshop",
        type: "job",
        category: "Electrical Work",
        description: "Urgent requirement for certified electrician in local manufacturing unit. Good salary with accommodation possible.",
        contactPhone: "+91 98765 43210",
        salary: { min: 18000, max: 25000 },
        isVerified: true,
        rating: 4.5,
        reviewCount: 12,
        createdAt: new Date().toISOString()
    },
    {
        _id: "2",
        title: "Free Computer Basics Training", 
        type: "skill",
        category: "Digital Literacy",
        description: "Government sponsored computer literacy program. 2-week course with certificate. Limited seats available.",
        contactPhone: "+91 87654 32109",
        isVerified: true,
        rating: 4.8, 
        reviewCount: 25,
        createdAt: new Date().toISOString()
    },
    {
        _id: "3",
        title: "Mobile Repair Service",
        type: "service",
        category: "Electronics Repair", 
        description: "Professional mobile phone repair service at your doorstep. All brands supported. 7-day service warranty.",
        contactPhone: "+91 76543 21098",
        isVerified: true,
        rating: 4.7,
        reviewCount: 32,
        createdAt: new Date().toISOString()
    },
    {
        _id: "4",
        title: "Tailor for Garment Shop",
        type: "job",
        category: "Textile & Fashion",
        description: "Experienced tailor required for readymade garment shop. Flexible timing with good incentives.",
        contactPhone: "+91 65432 10987", 
        salary: { min: 15000, max: 20000 },
        isVerified: false,
        rating: 4.0,
        reviewCount: 5,
        createdAt: new Date().toISOString()
    }
];

// Initialize the application
document.addEventListener('DOMContentLoaded', function() {
    loadListings();
    initializeEventListeners();
});

function initializeEventListeners() {
    document.getElementById('searchInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') searchListings();
    });
    document.getElementById('categoryFilter').addEventListener('change', filterListings);
    document.getElementById('distanceFilter').addEventListener('change', filterListings);
}

// Try to get real data, fallback to sample data
async function fetchListings() {
    try {
        const response = await fetch(`${API_BASE}/listings`);
        const data = await response.json();
        
        if (data.success && data.listings.length > 0) {
            console.log('✅ Using real data from backend');
            return data.listings;
        } else {
            console.log('ℹ️ Using sample data for demo');
            return sampleListings;
        }
    } catch (error) {
        console.log('⚠️ Backend connection issue, using sample data');
        return sampleListings;
    }
}

async function loadListings(filteredListings = null) {
    const container = document.getElementById('listingsContainer');
    
    try {
        let listings = filteredListings || await fetchListings();
        
        if (listings.length === 0) {
            container.innerHTML = `<div class="col-12 text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">No opportunities found</h4>
                <p class="text-muted">Try adjusting your search criteria</p>
            </div>`;
            return;
        }
        
        container.innerHTML = listings.map(listing => `
            <div class="col-md-6 col-lg-4">
                <div class="card listing-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="category-tag">${listing.category}</span>
                            ${listing.isVerified ? 
                                '<span class="verified-badge"><i class="fas fa-check"></i> Verified</span>' : 
                                '<span class="text-muted small"><i class="fas fa-clock"></i> Pending</span>'
                            }
                        </div>
                        
                        <h6 class="card-title fw-bold">${listing.title}</h6>
                        <p class="card-text text-muted small mb-3">${listing.description}</p>
                        
                        <div class="listing-meta mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="distance-badge">
                                    <i class="fas fa-map-marker-alt"></i> ${getRandomDistance()}
                                </span>
                                <div class="rating">
                                    <i class="fas fa-star"></i> ${listing.rating} <small class="text-muted">(${listing.reviewCount})</small>
                                </div>
                            </div>
                            ${listing.salary ? `<div class="text-success fw-bold small">₹${listing.salary.min} - ₹${listing.salary.max}/month</div>` : ''}
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">${formatDate(listing.createdAt)}</small>
                            <div>
                                <button class="btn btn-sm btn-outline-primary me-1" onclick="viewListing('${listing._id}')">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-primary" onclick="contactListing('${listing.contactPhone}')">
                                    <i class="fas fa-phone"></i> Contact
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
        
    } catch (error) {
        console.error('Error loading listings:', error);
        container.innerHTML = `<div class="col-12 text-center py-5">
            <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
            <h4 class="text-danger">Error loading opportunities</h4>
            <p class="text-muted">Please try again later</p>
        </div>`;
    }
}

// Helper functions
function getRandomDistance() {
    const distances = ['0.5 km', '1.2 km', '0.8 km', '2.1 km', '1.5 km'];
    return distances[Math.floor(Math.random() * distances.length)];
}

function formatDate(dateString) {
    const dates = ['2 hours ago', '1 day ago', '3 hours ago', '5 hours ago'];
    return dates[Math.floor(Math.random() * dates.length)];
}

// Authentication state
let isRegisterMode = false;

// Modal functions
function showLoginModal() {
    isRegisterMode = false;
    document.getElementById('authModalTitle').textContent = 'Login to Dwar';
    document.getElementById('registerFields').style.display = 'none';
    document.getElementById('confirmPasswordField').style.display = 'none';
    document.getElementById('authButton').textContent = 'Login';
    document.getElementById('authSwitchText').textContent = "Don't have an account?";
    document.getElementById('authSwitchLink').textContent = "Sign up";
    
    // Clear validation
    clearValidation();
    
    new bootstrap.Modal(document.getElementById('authModal')).show();
}

function showRegisterModal() {
    isRegisterMode = true;
    document.getElementById('authModalTitle').textContent = 'Join Dwar Community';
    document.getElementById('registerFields').style.display = 'block';
    document.getElementById('confirmPasswordField').style.display = 'block';
    document.getElementById('authButton').textContent = 'Create Account';
    document.getElementById('authSwitchText').textContent = "Already have an account?";
    document.getElementById('authSwitchLink').textContent = "Login";
    
    // Clear validation
    clearValidation();
    
    new bootstrap.Modal(document.getElementById('authModal')).show();
}

// Toggle between login and register
function toggleAuthMode() {
    if (isRegisterMode) {
        showLoginModal();
    } else {
        showRegisterModal();
    }
}

// Clear form validation
function clearValidation() {
    const inputs = document.querySelectorAll('#authForm input');
    inputs.forEach(input => {
        input.classList.remove('is-invalid');
        input.classList.remove('is-valid');
    });
}

// Real-time password confirmation validation
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', function() {
            validatePasswordMatch();
        });
        
        passwordInput.addEventListener('input', function() {
            if (isRegisterMode) {
                validatePasswordMatch();
            }
        });
    }
});

// Validate password match
function validatePasswordMatch() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const confirmInput = document.getElementById('confirmPassword');
    
    if (confirmPassword && password !== confirmPassword) {
        confirmInput.classList.add('is-invalid');
        confirmInput.classList.remove('is-valid');
        return false;
    } else if (confirmPassword && password === confirmPassword) {
        confirmInput.classList.remove('is-invalid');
        confirmInput.classList.add('is-valid');
        return true;
    }
    return null; // Not validated yet
}

// Enhanced validation function
function validateForm() {
    let isValid = true;
    
    // Clear previous validation
    clearValidation();
    
    if (isRegisterMode) {
        // First Name validation
        const firstName = document.getElementById('firstName').value.trim();
        if (!firstName) {
            document.getElementById('firstName').classList.add('is-invalid');
            isValid = false;
        }
        
        // Last Name validation
        const lastName = document.getElementById('lastName').value.trim();
        if (!lastName) {
            document.getElementById('lastName').classList.add('is-invalid');
            isValid = false;
        }
        
        // Password confirmation validation
        const passwordMatch = validatePasswordMatch();
        if (passwordMatch === false) {
            isValid = false;
        }
    }
    
    // Phone validation
    const phone = document.getElementById('phone').value.trim();
    if (!phone || !isValidPhone(phone)) {
        document.getElementById('phone').classList.add('is-invalid');
        isValid = false;
    }
    
    // Password validation
    const password = document.getElementById('password').value;
    if (!password || password.length < 6) {
        document.getElementById('password').classList.add('is-invalid');
        isValid = false;
    }
    
    return isValid;
}

// Phone validation helper
function isValidPhone(phone) {
    // Basic phone validation - you can enhance this
    const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
    return phoneRegex.test(phone.replace(/\s/g, ''));
}

// Updated authentication function
async function handleAuthForm() {
    if (!validateForm()) {
        showAlert('Please fix the errors in the form', 'danger');
        return;
    }
    
    const phone = document.getElementById('phone').value.trim();
    const password = document.getElementById('password').value;
    
    try {
        // Show loading state
        const button = document.getElementById('authButton');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        button.disabled = true;
        
        if (IS_GITHUB_DEMO) {
            // Mock authentication for GitHub
            setTimeout(() => {
                const fullName = isRegisterMode ? 
                    `${document.getElementById('firstName').value} ${document.getElementById('lastName').value}` : 
                    "Demo User";
                
                const mockUser = {
                    id: "demo_" + Date.now(),
                    phone: phone,
                    name: fullName,
                    firstName: isRegisterMode ? document.getElementById('firstName').value : "Demo",
                    lastName: isRegisterMode ? document.getElementById('lastName').value : "User",
                    userType: "seeker"
                };
                
                // Store in localStorage for demo
                localStorage.setItem('dwar_demo_user', JSON.stringify(mockUser));
                localStorage.setItem('dwar_demo_mode', 'true');
                
                showAlert(`✅ ${isRegisterMode ? 'Registration' : 'Login'} successful! (Demo Mode)`, 'success');
                bootstrap.Modal.getInstance(document.getElementById('authModal')).hide();
                document.getElementById('authForm').reset();
                updateAuthUI(mockUser);
                
                button.innerHTML = isRegisterMode ? 'Create Account' : 'Login';
                button.disabled = false;
            }, 1500);
            
        } else {
            // Real backend authentication
            const endpoint = isRegisterMode ? '/auth/register' : '/auth/login';
            let body;
            
            if (isRegisterMode) {
                body = {
                    phone: phone,
                    firstName: document.getElementById('firstName').value.trim(),
                    lastName: document.getElementById('lastName').value.trim(),
                    name: `${document.getElementById('firstName').value.trim()} ${document.getElementById('lastName').value.trim()}`,
                    password: password
                };
            } else {
                body = { phone, password };
            }
            
            const response = await fetch(`${API_BASE}${endpoint}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body)
            });
            
            const data = await response.json();
            
            if (data.success) {
                localStorage.setItem('dwar_token', data.token);
                localStorage.setItem('dwar_user', JSON.stringify(data.user));
                showAlert(`✅ ${isRegisterMode ? 'Registration' : 'Login'} successful!`, 'success');
                bootstrap.Modal.getInstance(document.getElementById('authModal')).hide();
                document.getElementById('authForm').reset();
                updateAuthUI(data.user);
            } else {
                showAlert(`❌ ${data.message}`, 'danger');
            }
        }
        
    } catch (error) {
        console.error('Auth error:', error);
        showAlert('❌ Network error. Please try again.', 'danger');
    } finally {
        const button = document.getElementById('authButton');
        button.innerHTML = isRegisterMode ? 'Create Account' : 'Login';
        button.disabled = false;
    }
}

// Job search function
async function searchJobsOnGoogle(city, keyword) {
    if (!city || city.trim() === '') {
        alert('Please enter a valid city name.');
        return;
    }
    let query = 'jobs in ' + city;
    if (keyword && keyword.trim() !== '') {
        query = keyword + ' jobs in ' + city;
    }
    const encodedQuery = encodeURIComponent(query);
    const url = 'https://www.google.com/search?q=' + encodedQuery;
    try {
        const response = await fetch('https://api.allorigins.win/get?url=' + encodeURIComponent(url));
        const data = await response.json();
        document.getElementById('listingsContainer').innerHTML = `<div class="google-results"><h4>Google Jobs for ${city}${keyword ? ' (' + keyword + ')' : ''}</h4><div>${data.contents}</div></div>`;
    } catch (error) {
        document.getElementById('listingsContainer').innerHTML = `<div class="text-danger">Unable to fetch Google results. Please try manually: <a href='${url}' target='_blank'>Google Jobs in ${city}${keyword ? ' (' + keyword + ')' : ''}</a></div>`;
    }
}


// Add event listener for Jobs menu item
const jobsMenuItem = document.querySelector('a[href="jobs.html"]');
if (jobsMenuItem) {
    jobsMenuItem.addEventListener('click', function(e) {
        e.preventDefault();
        const city = prompt('Enter the city to search jobs:');
        const keyword = prompt('Enter a job keyword (optional):');
        if (city) searchJobsOnGoogle(city, keyword);
    });
}

// Smooth scroll for anchor links
const anchorLinks = document.querySelectorAll('a[href^="#"]');
anchorLinks.forEach(link => {
    link.addEventListener('click', function(e) {
        const targetId = this.getAttribute('href').substring(1);
        const target = document.getElementById(targetId);
        if (target) {
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth' });
        }
    });
});

// Animated card reveal
window.addEventListener('DOMContentLoaded', () => {
    const cards = document.querySelectorAll('.listing-card');
    cards.forEach((card, i) => {
        card.style.opacity = 0;
        setTimeout(() => {
            card.style.transition = 'opacity 0.6s';
            card.style.opacity = 1;
        }, 120 * i);
    });
});

// Update backend models (if using real backend)
// In models/User.js, update the schema:
/*
const userSchema = new mongoose.Schema({
    phone: { type: String, required: true, unique: true },
    firstName: { type: String, required: true },
    lastName: { type: String, required: true },
    name: { type: String, required: true },
    password: { type: String, required: true, minlength: 6 },
    // ... rest of your schema
});
*/