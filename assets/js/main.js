// assets/js/main.js
// Modern JavaScript for StayNest

// Search Functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(handleSearch, 300));
    }
    
    // Initialize tooltips
    initTooltips();
    
    // Add smooth scroll
    initSmoothScroll();
});

// Debounce function for search
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Handle search
async function handleSearch(e) {
    const query = e.target.value;
    if (query.length > 2) {
        try {
            const response = await fetch(`/api/search.php?q=${encodeURIComponent(query)}`);
            const properties = await response.json();
            displaySearchResults(properties);
        } catch (error) {
            console.error('Search error:', error);
        }
    }
}

// Display search results
function displaySearchResults(properties) {
    const resultsContainer = document.getElementById('searchResults');
    if (!resultsContainer) return;
    
    if (properties.length === 0) {
        resultsContainer.innerHTML = '<p class="text-center text-gray-500">No properties found 😢</p>';
        return;
    }
    
    resultsContainer.innerHTML = properties.map(prop => `
        <div class="property-card fade-in-up">
            <img src="${prop.image_url || '/assets/images/default.jpg'}" alt="${prop.name}">
            <div class="p-4">
                <h3 class="font-bold text-xl">${prop.name}</h3>
                <p class="text-gray-600">📍 ${prop.location}</p>
                <p class="text-purple-600 font-bold mt-2">Rp ${formatPrice(prop.price_per_month)}/bulan</p>
                <a href="/detail.php?id=${prop.id}" class="btn-gradient inline-block mt-3">Lihat Detail →</a>
            </div>
        </div>
    `).join('');
}

// Format price
function formatPrice(price) {
    return new Intl.NumberFormat('id-ID').format(price);
}

// Booking confirmation
async function confirmBooking(propertyId) {
    const formData = {
        property_id: propertyId,
        customer_name: document.getElementById('name').value,
        customer_email: document.getElementById('email').value,
        customer_phone: document.getElementById('phone').value,
        check_in_date: document.getElementById('checkin').value,
        duration_months: parseInt(document.getElementById('duration').value)
    };
    
    try {
        const response = await fetch('/api/submit_booking.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            showNotification('✅ Booking成功了!', 'success');
            setTimeout(() => {
                window.location.href = `/bookings/confirmation.php?id=${result.booking_id}`;
            }, 1500);
        } else {
            showNotification('❌ Booking failed: ' + result.message, 'error');
        }
    } catch (error) {
        console.error('Booking error:', error);
        showNotification('❌ Network error. Please try again.', 'error');
    }
}

// Show notification
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `fixed top-20 right-4 z-50 p-4 rounded-lg shadow-lg ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    } text-white`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Initialize tooltips
function initTooltips() {
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(el => {
        el.addEventListener('mouseenter', (e) => {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = el.dataset.tooltip;
            document.body.appendChild(tooltip);
            
            const rect = el.getBoundingClientRect();
            tooltip.style.top = `${rect.top - 30}px`;
            tooltip.style.left = `${rect.left + rect.width/2 - tooltip.offsetWidth/2}px`;
        });
        
        el.addEventListener('mouseleave', () => {
            const tooltip = document.querySelector('.tooltip');
            if (tooltip) tooltip.remove();
        });
    });
}

// Smooth scroll
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
}

// Real-time availability check
async function checkAvailability(propertyId) {
    try {
        const response = await fetch(`/api/check_availability.php?id=${propertyId}`);
        const data = await response.json();
        
        const availabilityBadge = document.getElementById('availabilityBadge');
        if (availabilityBadge) {
            if (data.available) {
                availabilityBadge.innerHTML = '✅ Tersedia';
                availabilityBadge.className = 'bg-green-100 text-green-700 px-3 py-1 rounded-full';
            } else {
                availabilityBadge.innerHTML = '❌ Penuh';
                availabilityBadge.className = 'bg-red-100 text-red-700 px-3 py-1 rounded-full';
            }
        }
    } catch (error) {
        console.error('Availability check error:', error);
    }
}

// Export functions for global use
window.confirmBooking = confirmBooking;
window.checkAvailability = checkAvailability;