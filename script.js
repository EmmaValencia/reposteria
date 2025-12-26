document.addEventListener('DOMContentLoaded', function () {
    console.log('🔧 Script cargado - Inicializando funcionalidades...');

    // ========================================================================= //
    // === VARIABLES GLOBALES ===
    // ========================================================================= //
    
    // Elementos de Tienda/Modal
    const productsContainer = document.getElementById('products-container');
    const categoryButtons = document.querySelectorAll('.category-filter-btn');
    const productModalElement = document.getElementById('productModal');
    const productModal = productModalElement ? new bootstrap.Modal(productModalElement) : null;
    const modalForm = document.getElementById('add-to-cart-form');
    const checkboxInstructions = document.getElementById('add-instructions-checkbox');
    const instructionsContainer = document.getElementById('instructions-container');
    const quantityInput = document.getElementById('modal-quantity');
    
    // Elementos del Carrito Flotante
    const toastContainer = document.getElementById('toast-container');
    const cartCountNav = document.getElementById('cart-count-nav');
    const floatingCartIcon = document.getElementById('floating-cart-icon');
    const liveCartSidebar = document.getElementById('live-cart-sidebar');
    const closeCartBtn = document.getElementById('close-cart-btn');
    const liveCartItemsContainer = document.getElementById('live-cart-items-container');
    const liveCartSubtotal = document.getElementById('live-cart-subtotal');
    const liveCartTotalItems = document.getElementById('live-cart-total-items');

    const initialCategory = 'Todos';


    // ========================================================================= //
    // === FUNCIONES AUXILIARES ===
    // ========================================================================= //
    
    function formatCurrency(amount) {
        const numericAmount = parseFloat(amount);
        if (isNaN(numericAmount)) return '$0.00';
        return '$' + numericAmount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
    
    function showToast(title, body, type = 'info') {
        console.log(`📢 Toast: ${title} - ${body}`);
        const toastId = 'toast-' + Date.now();
        const icon = type === 'success' ? 'fa-check-circle' : (type === 'error' ? 'fa-times-circle' : 'fa-info-circle');

        const toastHtml = `
            <div id="${toastId}" class="toast align-items-center text-white bg-${type === 'error' ? 'danger' : 'success'} border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center">
                        <i class="fas ${icon} me-2"></i>
                        <strong>${title}</strong>: ${body}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        
        if (toastContainer) {
            toastContainer.insertAdjacentHTML('beforeend', toastHtml);
            const newToast = document.getElementById(toastId);
            if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
                const bsToast = new bootstrap.Toast(newToast);
                bsToast.show();
            }
        }
    }
    
    function updateLiveCart(cartData) {
        console.log('🛒 Actualizando carrito:', cartData);
        
        if (!liveCartItemsContainer || !liveCartSubtotal) return;
        
        let totalItems = 0;
        let subtotal = 0;
        
        if (cartData.items && cartData.items.length > 0) {
            let itemsHTML = '';
            cartData.items.forEach(item => {
                totalItems += item.cantidad;
                subtotal += item.precio * item.cantidad;
                
                itemsHTML += `
                    <div class="live-cart-item d-flex align-items-center py-2 border-bottom border-secondary">
                        <img src="${item.imagen || 'Images/placeholder.png'}" alt="${item.nombre}" class="live-cart-item-image me-3" style="width: 50px; height: 50px; object-fit: cover;">
                        <div class="flex-grow-1">
                            <h6 class="mb-0 text-white">${item.nombre}</h6>
                            <p class="mb-0 small text-white-50">
                                ${item.cantidad} x ${formatCurrency(item.precio)}
                            </p>
                            ${item.instrucciones ? `<p class="mb-0 small text-warning">Instrucciones: ${item.instrucciones}</p>` : ''}
                        </div>
                        <button class="btn btn-sm btn-outline-danger remove-item-btn" data-product-id="${item.id}" aria-label="Eliminar producto">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                `;
            });
            liveCartItemsContainer.innerHTML = itemsHTML;
            
            // Agregar event listeners para eliminar
            liveCartItemsContainer.querySelectorAll('.remove-item-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const productId = this.getAttribute('data-product-id');
                    removeItemFromCart(productId);
                });
            });
        } else {
            liveCartItemsContainer.innerHTML = '<p class="text-center text-white-50 mt-4">Tu carrito está vacío.</p>';
        }

        // Actualizar subtotal y contadores
        liveCartSubtotal.textContent = formatCurrency(cartData.subtotal || cartData.subtotal_raw || subtotal);
        
        const finalTotalItems = cartData.total_items || totalItems;
        if (liveCartTotalItems) {
            liveCartTotalItems.textContent = finalTotalItems;
        }
        
        if (cartCountNav) {
            cartCountNav.textContent = finalTotalItems;
        }
        
        // Actualizar icono flotante
        if (floatingCartIcon) {
            const badge = floatingCartIcon.querySelector('.badge');
            if (badge) badge.textContent = finalTotalItems;
            floatingCartIcon.style.display = finalTotalItems > 0 ? 'flex' : 'none';
        }
    }

    function removeItemFromCart(productId) {
        console.log('🗑️ Eliminando producto:', productId);
        const formData = new FormData();
        formData.append('action', 'remove');
        formData.append('product_id', productId);
        
        fetch('ajax-cart.php', { 
            method: 'POST', 
            body: formData 
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' || data.items) {
                updateLiveCart(data);
                showToast('Eliminado', 'Producto eliminado del carrito.', 'success');
            } else {
                showToast('Error', data.message || 'No se pudo eliminar el producto.', 'error');
            }
        })
        .catch(error => {
            console.error('Error al eliminar del carrito:', error);
            showToast('Error', 'No se pudo completar la acción.', 'error');
        });
    }

    function toggleLiveCart() {
        if (!liveCartSidebar) return;
        console.log('📦 Alternando carrito');
        liveCartSidebar.classList.toggle('cart-sidebar-hidden');
    }

    function fetchAndUpdateCart() {
        console.log('🔄 Actualizando carrito desde servidor...');
        fetch('ajax-cart.php')
            .then(response => {
                if (!response.ok) throw new Error('Error al cargar el carrito');
                return response.json();
            })
            .then(updateLiveCart)
            .catch(error => {
                console.error('Error al obtener el carrito:', error);
            });
    }
    
    function loadProducts(category) {
        if (!productsContainer) return;
        console.log('📦 Cargando productos de categoría:', category);
        
        productsContainer.innerHTML = '<div class="col-12 text-center py-5"><i class="fas fa-spinner fa-spin fa-3x text-dorado"></i><p class="text-white mt-3">Cargando productos...</p></div>';

        fetch(`obtener_productos.php?categoria=${encodeURIComponent(category)}`)
            .then(response => response.text())
            .then(html => {
                productsContainer.innerHTML = html;
                initializeModalListeners();
            })
            .catch(error => {
                console.error('Error al cargar los productos:', error);
                productsContainer.innerHTML = '<div class="col-12 text-center"><p class="text-danger fs-4">Error al cargar los productos.</p></div>';
            });
    }

    function initializeModalListeners() {
        console.log('🎯 Inicializando listeners del modal...');
        document.querySelectorAll('.open-product-modal').forEach(button => {
            button.addEventListener('click', function() {
                const card = this.closest('.product-card');
                if (!card) {
                    console.error('No se encontró la tarjeta del producto');
                    return;
                }
                
                const productId = card.dataset.id;
                const productName = card.dataset.nombre;
                const productPrice = card.dataset.precio;
                const productImage = card.dataset.imagen;

                console.log('🔄 Abriendo modal para:', productName);

                // Llenar el modal
                document.getElementById('modal-product-id').value = productId;
                document.getElementById('modal-product-name').textContent = productName;
                document.getElementById('modal-product-price').textContent = formatCurrency(productPrice);
                document.getElementById('modal-product-image').src = productImage;
                
                // Resetear cantidad e instrucciones
                if (quantityInput) quantityInput.value = 1;
                if (checkboxInstructions) checkboxInstructions.checked = false;
                if (instructionsContainer) instructionsContainer.style.display = 'none';
                document.getElementById('modal-instructions').value = '';

                // Mostrar modal
                if (productModal) {
                    productModal.show();
                }
            });
        });
    }

    // ========================================================================= //
    // === 1. LÓGICA DE INTERFAZ Y ANIMACIONES ===
    // ========================================================================= //

    // Navbar que cambia al hacer scroll
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) navbar.classList.add('navbar-scrolled');
            else navbar.classList.remove('navbar-scrolled');
        });
    }

    // Slider de Flyers
    const flyerSliderContainer = document.querySelector('.flyer-slider-container');
    if (flyerSliderContainer) {
        const nextButton = flyerSliderContainer.querySelector('.next');
        const prevButton = flyerSliderContainer.querySelector('.prev');
        const slide = flyerSliderContainer.querySelector('.slide');
        const nextSlide = () => {
            if (slide) slide.appendChild(slide.querySelectorAll('.item')[0]);
        };
        if (nextButton && prevButton) {
            nextButton.addEventListener('click', nextSlide);
            prevButton.addEventListener('click', () => {
                if (slide) {
                    const items = slide.querySelectorAll('.item');
                    slide.prepend(items[items.length - 1]);
                }
            });
            let autoSlideInterval = setInterval(nextSlide, 3500);
            flyerSliderContainer.addEventListener('mouseenter', () => clearInterval(autoSlideInterval));
            flyerSliderContainer.addEventListener('mouseleave', () => autoSlideInterval = setInterval(nextSlide, 3500));
        }
    }

    // --- Botón flotante de WhatsApp ---
    const whatsappFloat = document.getElementById('whatsapp-float');
    if (whatsappFloat) {
        setTimeout(() => { whatsappFloat.classList.add('whatsapp-float-visible'); }, 2000);
    }
    // Carrusel de Creaciones (Coverflow)
    if (typeof Swiper !== 'undefined' && document.querySelector('.coverflow-carousel')) {
        new Swiper('.coverflow-carousel', {
            effect: 'coverflow', 
            grabCursor: true, 
            centeredSlides: true, 
            slidesPerView: 'auto', 
            loop: true,
            autoplay: { delay: 2000, disableOnInteraction: false },
            coverflowEffect: { rotate: 30, stretch: 0, depth: 100, modifier: 1, slideShadows: true },
            pagination: { el: '.swiper-pagination', clickable: true },
        });
    }

    // Efecto Parallax
    const UPDATE_PARALLAX = ({ x, y }) => {
        const card = document.querySelector('.parallax-card');
        if (!card) return;
        const rect = card.getBoundingClientRect();
        const cardX = x - rect.left;
        const cardY = y - rect.top;
        document.documentElement.style.setProperty("--x", (cardX / rect.width - 0.5) * 2);
        document.documentElement.style.setProperty("--y", (cardY / rect.height - 0.5) * 2);
    };
    window.addEventListener("mousemove", UPDATE_PARALLAX);

    // Animaciones al hacer Scroll
    if ('IntersectionObserver' in window) {
        const observerOptions = { threshold: 0.1 };
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible'); 
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        document.querySelectorAll('.animate-on-scroll').forEach(element => {
            observer.observe(element);
        });
    }

    // Sonido para el video del asistente
    document.querySelectorAll('.assistant-card').forEach(card => {
        const video = card.querySelector('video');
        if (video) {
            card.addEventListener('mouseenter', () => {
                video.muted = false; 
                video.play().catch(error => {
                    console.warn("Autoplay bloqueado con audio. Reproduciendo silenciado.", error);
                    video.muted = true;
                    video.play();
                });
            });
            card.addEventListener('mouseleave', () => { 
                video.pause(); 
                video.currentTime = 0;
            });
        }
    });

// ========================================================================= //
// === 2. LÓGICA DEL CARRITO FLOTANTE - VERSIÓN CSS CORREGIDA ===
// ========================================================================= //

// Crear backdrop dinámicamente si no existe
if (!document.getElementById('cart-backdrop')) {
    const backdrop = document.createElement('div');
    backdrop.id = 'cart-backdrop';
    backdrop.className = 'cart-backdrop';
    document.body.appendChild(backdrop);
}

function toggleLiveCart() {
    if (!liveCartSidebar) return;
    
    console.log('🔄 Alternando carrito. Estado actual:', liveCartSidebar.classList.contains('cart-sidebar-hidden') ? 'OCULTO' : 'VISIBLE');
    
    liveCartSidebar.classList.toggle('cart-sidebar-hidden');
    
    console.log('🔄 Nuevo estado:', liveCartSidebar.classList.contains('cart-sidebar-hidden') ? 'OCULTO' : 'VISIBLE');
}

// Event listeners simplificados
if (floatingCartIcon) {
    floatingCartIcon.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('🎯 Clic en icono del carrito');
        toggleLiveCart();
    });
}

if (closeCartBtn) {
    closeCartBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('🎯 Clic en botón cerrar');
        liveCartSidebar.classList.add('cart-sidebar-hidden');
    });
}

// Cerrar carrito al hacer clic en el backdrop
document.getElementById('cart-backdrop').addEventListener('click', function() {
    console.log('🎯 Clic en backdrop - cerrando carrito');
    liveCartSidebar.classList.add('cart-sidebar-hidden');
});

// Prevenir que se cierre al hacer clic dentro del carrito
if (liveCartSidebar) {
    liveCartSidebar.addEventListener('click', function(e) {
        e.stopPropagation();
    });
}

console.log('✅ Carrito flotante inicializado con CSS corregido');   // ========================================================================= //
    // === 3. LÓGICA DE TIENDA ===
    // ========================================================================= //

    if (productsContainer) {
        
        // Listeners para botones de categoría
        categoryButtons.forEach(button => {
            button.addEventListener('click', function() {
                categoryButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                const category = this.dataset.category;
                loadProducts(category);
            });
        });
        
        // Lógica del Modal de Producto - CORREGIDO
        if (modalForm) {
            modalForm.addEventListener('submit', function(event) {
                event.preventDefault();
                
                const formData = new FormData(modalForm);
                if (checkboxInstructions && !checkboxInstructions.checked) {
                    formData.delete('instrucciones');
                }
                
                console.log('📤 Enviando producto al carrito...');
                console.log('Datos del formulario:');
                for (let [key, value] of formData.entries()) {
                    console.log(key + ': ' + value);
                }
                
                fetch('ajax-cart.php', { 
                    method: 'POST', 
                    body: formData 
                })
                .then(response => {
                    console.log('Respuesta recibida, status:', response.status);
                    if (!response.ok) {
                        throw new Error('Error HTTP: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Datos de respuesta:', data);
                    if (data.status === 'success') {
                        if (productModal) productModal.hide();
                        fetchAndUpdateCart();
                        showToast(`Añadido al Carrito`, `${data.nombre} ha sido añadido.`, 'success');
                        
                        // Solo abrir el carrito si está cerrado
                        if (liveCartSidebar && liveCartSidebar.classList.contains('cart-sidebar-hidden')) {
                            toggleLiveCart();
                        }
                    } else {
                        showToast('Error', data.message || 'Error desconocido', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error completo al añadir al carrito:', error);
                    showToast('Error', 'No se pudo completar la acción. Revisa la consola.', 'error');
                });
            });
        }

        // Checkbox de instrucciones
        if (checkboxInstructions && instructionsContainer) {
            checkboxInstructions.addEventListener('change', function() {
                instructionsContainer.style.display = this.checked ? 'block' : 'none';
            });
        }
        
        // Botones de cantidad en el modal - CORREGIDO
        const quantityMinus = document.getElementById('quantity-minus');
        const quantityPlus = document.getElementById('quantity-plus');

        if (quantityMinus && quantityInput) {
            quantityMinus.addEventListener('click', function() {
                let currentValue = parseInt(quantityInput.value);
                if (currentValue > 1) {
                    quantityInput.value = currentValue - 1;
                }
            });
        }

        if (quantityPlus && quantityInput) {
            quantityPlus.addEventListener('click', function() {
                let currentValue = parseInt(quantityInput.value);
                quantityInput.value = currentValue + 1;
            });
        }

        // Carga inicial de productos
        if (categoryButtons.length > 0) {
            let activeCategoryButton = Array.from(categoryButtons).find(btn => btn.classList.contains('active'));
            if (!activeCategoryButton) {
                const todosBtn = Array.from(categoryButtons).find(btn => btn.textContent.trim() === 'Todos');
                if (todosBtn) todosBtn.classList.add('active');
            }
            loadProducts(initialCategory);
        }
    }
    
    // ========================================================================= //
    // === 4. FORMULARIO DE CONTACTO ===
    // ========================================================================= //
    
    const contactForm = document.querySelector('#contacto form');
    const formNotification = document.getElementById('form-notification');
    if (contactForm && formNotification) {
        contactForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(contactForm);
            const submitButton = contactForm.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            submitButton.textContent = 'Enviando...';

            fetch('enviar.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                showFormNotification(data.status, data.message);
                if (data.status === 'success') contactForm.reset();
            })
            .catch(() => showFormNotification('error', 'No se pudo conectar con el servidor.'))
            .finally(() => {
                submitButton.disabled = false;
                submitButton.innerHTML = 'Enviar Mensaje <i class="fas fa-paper-plane"></i>';
            });
        });

        function showFormNotification(status, message) {
            const titleEl = formNotification.querySelector('.notification-title');
            const messageEl = formNotification.querySelector('.notification-message');
            const iconEl = formNotification.querySelector('.notification-icon');
            
            formNotification.className = 'form-notification ' + status;
            iconEl.className = 'notification-icon fas ' + (status === 'success' ? 'fa-check-circle' : 'fa-times-circle');
            messageEl.textContent = message;
            titleEl.textContent = status === 'success' ? '¡Éxito!' : 'Error';
            
            formNotification.classList.add('visible');
            setTimeout(() => { formNotification.classList.remove('visible'); }, 5000);
        }
    }
});

console.log('✅ Script.js cargado correctamente');
   // --- 9. BOTÓN WHATSAPP FLOTANTE RESPONSIVE ---
    function initWhatsAppFloat() {
        const whatsappFloat = document.getElementById('whatsapp-float');
        if (!whatsappFloat) {
            const floatBtn = document.createElement('a');
            floatBtn.href = 'https://wa.me/message/LPG6PCF2MNNKH1';
            floatBtn.target = '_blank';
            floatBtn.rel = 'noopener noreferrer';
            floatBtn.className = 'whatsapp-float';
            floatBtn.id = 'whatsapp-float';
            floatBtn.innerHTML = '<i class="fab fa-whatsapp"></i>';
            document.body.appendChild(floatBtn);
        }
        
        // Ajustar posición según el dispositivo
        setTimeout(() => {
            const btn = document.getElementById('whatsapp-float');
            if (btn) {
                if (isMobile) {
                    btn.style.bottom = '80px';
                    btn.style.right = '10px';
                } else {
                    btn.style.bottom = '100px';
                    btn.style.right = '20px';
                }
                btn.style.display = 'flex';
                btn.style.visibility = 'visible';
            }
        }, 2000);
    }

    initWhatsAppFloat();
    
// 1. LÓGICA PARA EL MODAL DE PROMOCIÓN
    const promoModalElement = document.getElementById('promoModal');
    
    // ** MODIFICACIÓN: La ventana emergente aparecerá cada vez que se cargue la página. **
    if (promoModalElement) { 
        
        const promoModal = new bootstrap.Modal(promoModalElement);

        setTimeout(() => {
            promoModal.show();
            // Se elimina la lógica de sessionStorage para forzar la aparición constante.
        }, 1000); // Aparecerá 1 segundo después de cargar.
    }