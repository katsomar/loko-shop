// Notification Popup Handler
(function() {
    // Animated counter
    function animateCount() {
        const countEl = document.querySelector('.notification-count-animate');
        if (!countEl) return;
        
        const target = parseInt(countEl.getAttribute('data-count') || 0);
        let current = 0;
        const duration = 800;
        const increment = target / (duration / 16);
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                countEl.textContent = target;
                clearInterval(timer);
            } else {
                countEl.textContent = Math.floor(current);
            }
        }, 16);
    }
    
    // Show popup after short delay
    setTimeout(() => {
        animateCount();
    }, 600);
    
    // Close handlers
    function closePopup() {
        const overlay = document.getElementById('notificationPopupOverlay');
        const popup = document.getElementById('notificationPopup');
        
        if (!overlay || !popup) return;
        
        // Animate out
        popup.style.animation = 'none';
        popup.style.transform = 'scale(0.9) translateY(20px)';
        popup.style.opacity = '0';
        popup.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        
        overlay.style.opacity = '0';
        overlay.style.transition = 'opacity 0.3s ease';
        
        setTimeout(() => {
            overlay.remove();
            document.body.style.overflow = '';
            
            // Mark as shown via AJAX
            const currentScript = document.currentScript || document.querySelector('script[src*="notification_popup.js"]');
            const phpSelf = currentScript ? currentScript.getAttribute('data-php-self') : window.location.pathname;
            
            fetch(phpSelf, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'mark_notifications_shown=1'
            });
        }, 300);
    }
    
    // Event listeners
    document.getElementById('closeNotificationPopup')?.addEventListener('click', closePopup);
    document.getElementById('dismissNotificationPopup')?.addEventListener('click', closePopup);
    
    // Click outside to close
    document.getElementById('notificationPopupOverlay')?.addEventListener('click', function(e) {
        if (e.target.id === 'notificationPopupOverlay') {
            closePopup();
        }
    });
    
    // ESC key to close
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closePopup();
        }
    });
    
    // Lock body scroll
    document.body.style.overflow = 'hidden';
})();
