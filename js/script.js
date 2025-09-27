// Funcionalidades generales del sistema

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips
    initTooltips();
    
    // Inicializar formularios
    initForms();
    
    // Inicializar carruseles
    initCarousels();
    
    // Inicializar modales
    initModals();
});

// Tooltips
function initTooltips() {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
}

function showTooltip(e) {
    const tooltipText = this.getAttribute('data-tooltip');
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.textContent = tooltipText;
    tooltip.style.position = 'absolute';
    tooltip.style.background = 'rgba(0, 0, 0, 0.8)';
    tooltip.style.color = 'white';
    tooltip.style.padding = '5px 10px';
    tooltip.style.borderRadius = '4px';
    tooltip.style.fontSize = '12px';
    tooltip.style.zIndex = '1000';
    
    document.body.appendChild(tooltip);
    
    const rect = this.getBoundingClientRect();
    tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
    tooltip.style.left = (rect.left + (rect.width - tooltip.offsetWidth) / 2) + 'px';
    
    this.tooltip = tooltip;
}

function hideTooltip() {
    if (this.tooltip) {
        this.tooltip.remove();
        this.tooltip = null;
    }
}

// Formularios
function initForms() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Validación básica de campos requeridos
            const requiredFields = this.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = 'var(--error-color)';
                    
                    // Remover el estilo cuando el usuario comience a escribir
                    field.addEventListener('input', function() {
                        this.style.borderColor = '';
                    }, { once: true });
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Por favor complete todos los campos obligatorios.');
            }
        });
    });
}

// Carruseles
function initCarousels() {
    const carousels = document.querySelectorAll('.image-carousel');
    
    carousels.forEach(carousel => {
        let isDown = false;
        let startX;
        let scrollLeft;
        
        carousel.addEventListener('mousedown', (e) => {
            isDown = true;
            startX = e.pageX - carousel.offsetLeft;
            scrollLeft = carousel.scrollLeft;
        });
        
        carousel.addEventListener('mouseleave', () => {
            isDown = false;
        });
        
        carousel.addEventListener('mouseup', () => {
            isDown = false;
        });
        
        carousel.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - carousel.offsetLeft;
            const walk = (x - startX) * 2;
            carousel.scrollLeft = scrollLeft - walk;
        });
        
        // Soporte para touch devices
        carousel.addEventListener('touchstart', (e) => {
            isDown = true;
            startX = e.touches[0].pageX - carousel.offsetLeft;
            scrollLeft = carousel.scrollLeft;
        });
        
        carousel.addEventListener('touchend', () => {
            isDown = false;
        });
        
        carousel.addEventListener('touchmove', (e) => {
            if (!isDown) return;
            const x = e.touches[0].pageX - carousel.offsetLeft;
            const walk = (x - startX) * 2;
            carousel.scrollLeft = scrollLeft - walk;
        });
    });
}

// Modales
function initModals() {
    const modals = document.querySelectorAll('.modal');
    
    modals.forEach(modal => {
        const closeBtn = modal.querySelector('.close');
        
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                modal.style.display = 'none';
            });
        }
        
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    });
}

// Funciones utilitarias
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

function formatCurrency(amount) {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(amount);
}

// Notificaciones toast
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    toast.style.position = 'fixed';
    toast.style.bottom = '20px';
    toast.style.right = '20px';
    toast.style.padding = '10px 20px';
    toast.style.borderRadius = '4px';
    toast.style.color = 'white';
    toast.style.zIndex = '10000';
    toast.style.opacity = '0';
    toast.style.transition = 'opacity 0.3s';
    
    switch (type) {
        case 'success':
            toast.style.background = 'var(--success-color)';
            break;
        case 'error':
            toast.style.background = 'var(--error-color)';
            break;
        case 'warning':
            toast.style.background = 'var(--warning-color)';
            break;
        default:
            toast.style.background = 'var(--primary-color)';
    }
    
    document.body.appendChild(toast);
    
    // Mostrar toast
    setTimeout(() => {
        toast.style.opacity = '1';
    }, 10);
    
    // Ocultar y eliminar toast después de 3 segundos
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}

// Cargar más contenido (para paginación infinita)
function setupInfiniteScroll(container, loadMoreCallback) {
    let isLoading = false;
    
    container.addEventListener('scroll', debounce(() => {
        if (isLoading) return;
        
        const { scrollTop, scrollHeight, clientHeight } = container;
        const isAtBottom = scrollTop + clientHeight >= scrollHeight - 100;
        
        if (isAtBottom) {
            isLoading = true;
            loadMoreCallback().then(() => {
                isLoading = false;
            }).catch(() => {
                isLoading = false;
            });
        }
    }, 100));
}

// Exportar funciones para uso global
window.utils = {
    debounce,
    formatCurrency,
    showToast,
    setupInfiniteScroll
};


// Funcionalidad para el filtrado de favoritos por usuario
function initUserFilter() {
    const filterForm = document.querySelector('.filter-form');
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            const userIdInput = document.getElementById('user_id');
            if (userIdInput && userIdInput.value.trim() === '') {
                e.preventDefault();
                alert('Por favor ingrese un ID de usuario para filtrar');
            }
        });
    }
}

// Funcionalidad para los botones de acción rápida
function initQuickActions() {
    const actionButtons = document.querySelectorAll('.action-buttons .btn');
    actionButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            // Aquí puedes añadir tracking o analytics si es necesario
            console.log('Acción rápida: ' + this.textContent);
        });
    });
}

// Funcionalidad para el envío de WhatsApp
function initWhatsAppButton() {
    const whatsappBtn = document.querySelector('.btn-whatsapp');
    if (whatsappBtn) {
        whatsappBtn.addEventListener('click', function(e) {
            // Confirmación antes de enviar
            if (!confirm('¿Está seguro que desea contactar por WhatsApp?')) {
                e.preventDefault();
            }
        });
    }
}

// Funcionalidad para la búsqueda de pedidos
function initOrderSearch() {
    const searchForm = document.querySelector('.search-order form');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const orderCodeInput = document.getElementById('order_code');
            if (orderCodeInput && orderCodeInput.value.trim() === '') {
                e.preventDefault();
                alert('Por favor ingrese un código de pedido');
            }
        });
    }
}

// Inicializar todas las funcionalidades cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Funcionalidades existentes
    initTooltips();
    initForms();
    initCarousels();
    initModals();
    
    // Nuevas funcionalidades
    initUserFilter();
    initQuickActions();
    initWhatsAppButton();
    initOrderSearch();
    
    // Configurar scroll infinito si es necesario
    const favoritesContainer = document.querySelector('.favorites-list');
    if (favoritesContainer) {
        setupInfiniteScroll(favoritesContainer, loadMoreFavorites);
    }
});

// Función para cargar más favoritos (paginación)
async function loadMoreFavorites() {
    // Implementar lógica de paginación si es necesario
    console.log('Cargando más favoritos...');
    return new Promise(resolve => setTimeout(resolve, 1000));
}

// Funcionalidad para el cambio de contraseña
function initPasswordChange() {
    const passwordForm = document.querySelector('.change-password form');
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            const currentPassword = document.getElementById('current_password');
            const newPassword = document.getElementById('new_password');
            const confirmPassword = document.getElementById('confirm_password');
            
            if (newPassword.value !== confirmPassword.value) {
                e.preventDefault();
                alert('Las nuevas contraseñas no coinciden');
                return;
            }
            
            if (newPassword.value.length < 6) {
                e.preventDefault();
                alert('La nueva contraseña debe tener al menos 6 caracteres');
                return;
            }
        });
    }
}



// Exportar funciones para uso global
window.utils = {
    debounce,
    formatCurrency,
    showToast,
    setupInfiniteScroll,
    initPasswordChange
};
