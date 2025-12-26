<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personaliza tu Pastel - Sabores de Cristal</title>
    
    <link rel="icon" type="image/png" sizes="16x16" href="../Images/LOGO.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../Images/LOGO.png">
    <link rel="apple-touch-icon" sizes="180x180" href="../Images/LOGO-min.png">
    <link rel="shortcut icon" href="../Images/LOGO.png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../responsive.css">
    
    </head>
<body>
    
    <header class="navbar navbar-expand-lg fixed-top navbar-scrolled">
        <div class="container">
            <a class="navbar-brand" href="../index.php"><img src="../Images/LOGO.png" alt="Logo Sabores de Cristal"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item"><a class="nav-link" href="../index.php">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link" href="../tienda.php">Tienda</a></li>
                    <li class="nav-item"><a class="nav-link active" href="pasteles.php">Personaliza tu Pastel</a></li>
                    <li class="nav-item">
                        <a class="nav-link" href="../cart.php" aria-label="Carrito">
                            <i class="fas fa-shopping-cart"></i><span class="badge rounded-pill bg-danger" id="cart-count-nav">0</span>
                        </a>
                    </li>
                    <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user-circle"></i> Mi Cuenta</a>
                            <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="../profile.php">Mi Perfil</a></li>
                                <li><a class="dropdown-item" href="../orders.php">Mis Recompensas</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="../logout.php">Cerrar Sesión</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link nav-link-cta" href="../login.php">Iniciar Sesión</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </header>

    <main style="margin-top: 100px; padding: 2rem 0;">
        <div class="container">
            <h1 class="subtitulo-seccion">Personaliza tu Pastel</h1>
            <p class="text-center mb-5" style="color: var(--crema);">Crea el pastel de tus sueños seleccionando cada detalle.</p>
            
            <div class="cake-builder-container">
                <div class="row g-4">
                    
                    <div class="col-lg-5 mb-4 mb-lg-0">
                        <div class="cake-preview position-sticky" style="top: 120px;">
                            
                            <video autoplay loop muted playsinline class="cake-preview-video">
                                <source src="../Images/Cupcake.mp4"" type="video/mp4">
                                Tu navegador no soporta videos.
                            </video>
                            <div class="video-overlay"></div>
                            <h4 class="position-relative">Tu Creación</h4>
                            <div id="selected-list" class="w-100 position-relative">
                                <div class="default-message text-center">Añade ingredientes a tu pastel.</div>
                            </div>
                            <div class="cake-total mt-auto position-relative" id="cake-total">Total: $0.00</div>
                        </div>
                    </div>
                    
                    <div class="col-lg-7">
                        <h4>Selecciona tus Ingredientes</h4>
                        <div class="ingredient-categories">
                            <button class="category-btn active" data-category="panes">🍞 Pan</button>
                            <button class="category-btn" data-category="rellenos">🍓 Relleno</button>
                            <button class="category-btn" data-category="betun">🎂 Betún</button>
                            <button class="category-btn" data-category="toppings">✨ Toppings</button>
                        </div>
                        <div class="ingredients-grid" id="ingredients-grid"></div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-center gap-3 mt-4">
                    <button class="aurora-button" id="reset-cake"><div><span>Reiniciar</span></div></button>
                    <button class="aurora-button" id="add-to-cart"><div><span>Añadir al Carrito</span></div></button>
                </div>
            </div>
        </div>
    </main>
    
    <footer class="footer-section"></footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // 
        // --- TODA LA LÓGICA DE JAVASCRIPT SE MANTIENE EXACTAMENTE IGUAL ---
        // 
        let cakeIngredients = [];
        let selectedIngredients = { pan: null, betun: null, rellenos: [], toppings: [] };
        const MAX_RELLENOS = 2;
        const EMOJI_MAP = { panes: '🍞', betun: '🎂', rellenos: '🍓', toppings: '✨' };

        function fetchIngredients() {
            fetch('obtener_ingredientes_pasteles.php').then(res => res.json()).then(data => {
                cakeIngredients = data;
                renderIngredients('panes');
            }).catch(e => console.error('Error al cargar ingredientes:', e));
        }

        function renderIngredients(category) {
            const grid = document.getElementById('ingredients-grid');
            grid.innerHTML = '';
            document.querySelectorAll('.category-btn').forEach(btn => btn.classList.toggle('active', btn.dataset.category === category));
            const filtered = cakeIngredients.filter(ing => ing.categoria_ingrediente === category);
            filtered.forEach(ingredient => grid.appendChild(createIngredientCard(ingredient)));
        }

        function createIngredientCard(ingredient) {
            const card = document.createElement('div');
            card.className = 'ingredient-card';
            const { categoria_ingrediente, id_ingrediente, nombre_ingrediente, descripcion_ingrediente, precio_ingrediente, imagen_ingrediente } = ingredient;
            let isSelected = false, isDisabled = false, disabledText = '';

            if (categoria_ingrediente === 'panes') {
                isSelected = selectedIngredients.pan?.id_ingrediente == id_ingrediente;
                isDisabled = selectedIngredients.pan && !isSelected;
                if(isDisabled) disabledText = `Solo puedes elegir un pan`;
            } else if (categoria_ingrediente === 'betun') {
                isSelected = selectedIngredients.betun?.id_ingrediente == id_ingrediente;
                isDisabled = selectedIngredients.betun && !isSelected;
                if(isDisabled) disabledText = `Solo puedes elegir un betún`;
            } else if (categoria_ingrediente === 'rellenos') {
                isSelected = selectedIngredients.rellenos.some(item => item.id_ingrediente == id_ingrediente);
                isDisabled = selectedIngredients.rellenos.length >= MAX_RELLENOS && !isSelected;
                if(isDisabled) disabledText = `Máximo ${MAX_RELLENOS} rellenos`;
            } else if (categoria_ingrediente === 'toppings') {
                isSelected = selectedIngredients.toppings.some(item => item.id_ingrediente == id_ingrediente);
            }

            if (isSelected) card.classList.add('selected');
            if (isDisabled) card.classList.add('disabled');
            
            card.innerHTML = `
                <div class="ingredient-image"><img src="Images/${imagen_ingrediente}" alt="${nombre_ingrediente}" onerror="this.style.display='none'"></div>
                <div class="ingredient-info">
                    <h5>${nombre_ingrediente}</h5> <p class="mb-1">${descripcion_ingrediente || ''}</p>
                    <span class="ingredient-price">+$${parseFloat(precio_ingrediente).toFixed(2)}</span>
                    ${isDisabled ? `<small class="disabled-text">${disabledText}</small>` : ''}
                </div>`;

            if (!isDisabled) card.addEventListener('click', () => handleSelection(ingredient));
            return card;
        }
        
        function handleSelection(ingredient) {
            const { categoria_ingrediente, id_ingrediente } = ingredient;
            if (categoria_ingrediente === 'panes') selectedIngredients.pan = selectedIngredients.pan?.id_ingrediente === id_ingrediente ? null : ingredient;
            else if (categoria_ingrediente === 'betun') selectedIngredients.betun = selectedIngredients.betun?.id_ingrediente === id_ingrediente ? null : ingredient;
            else if (categoria_ingrediente === 'rellenos') {
                const idx = selectedIngredients.rellenos.findIndex(i => i.id_ingrediente === id_ingrediente);
                if (idx > -1) selectedIngredients.rellenos.splice(idx, 1);
                else if (selectedIngredients.rellenos.length < MAX_RELLENOS) selectedIngredients.rellenos.push(ingredient);
            } else if (categoria_ingrediente === 'toppings') {
                const idx = selectedIngredients.toppings.findIndex(i => i.id_ingrediente === id_ingrediente);
                if (idx > -1) selectedIngredients.toppings.splice(idx, 1);
                else selectedIngredients.toppings.push(ingredient);
            }
            updateAllUI();
        }

        function updateAllUI() {
            renderIngredients(document.querySelector('.category-btn.active').dataset.category);
            updateSelectedListAndPrice();
        }

        function updateSelectedListAndPrice() {
            const list = document.getElementById('selected-list');
            list.innerHTML = '';
            let total = 0;
            const all = [selectedIngredients.pan, ...selectedIngredients.rellenos, selectedIngredients.betun, ...selectedIngredients.toppings].filter(Boolean);
            
            if(all.length === 0) { 
                list.innerHTML = '<div class="default-message text-center">Añade ingredientes a tu pastel.</div>';
                document.getElementById('cake-total').textContent = `Total: $0.00`; 
                return; 
            }

            all.forEach(item => {
                const div = document.createElement('div');
                div.className = 'selected-item d-flex justify-content-between align-items-center mb-2';
                div.innerHTML = `<div class="d-flex align-items-center"><span class="me-2 fs-5">${EMOJI_MAP[item.categoria_ingrediente] || '❓'}</span><span>${item.nombre_ingrediente}</span></div><span>$${parseFloat(item.precio_ingrediente).toFixed(2)}</span>`;
                list.appendChild(div);
                total += parseFloat(item.precio_ingrediente);
            });
            document.getElementById('cake-total').textContent = `Total: $${total.toFixed(2)}`;
        }

        function resetCake() {
            selectedIngredients = { pan: null, betun: null, rellenos: [], toppings: [] };
            updateAllUI();
        }

        function addCustomCakeToCart() {
            if (!selectedIngredients.pan) { alert('Debes seleccionar al menos un tipo de pan.'); return; }
            let total = 0;
            const all = [selectedIngredients.pan, ...selectedIngredients.rellenos, selectedIngredients.betun, ...selectedIngredients.toppings].filter(Boolean);
            let instructions = "Pastel Personalizado:\n";
            all.forEach(item => {
                instructions += `${EMOJI_MAP[item.categoria_ingrediente] || '❓'} ${item.nombre_ingrediente}: $${parseFloat(item.precio_ingrediente).toFixed(2)}\n`;
                total += parseFloat(item.precio_ingrediente);
            });

            const details = { pan: selectedIngredients.pan, rellenos: selectedIngredients.rellenos, betun: selectedIngredients.betun, toppings: selectedIngredients.toppings };
            const data = new FormData();
            data.append('is_custom_cake', 'true');
            data.append('nombre', 'Pastel Personalizado');
            data.append('precio', total);
            data.append('cantidad', 1);
            data.append('instrucciones', instructions);
            data.append('custom_details', JSON.stringify(details));

            fetch('../ajax-cart.php', { method: 'POST', body: data }).then(res => res.json()).then(d => {
                updateCartCount(d.total_items);
                alert('¡Tu pastel personalizado ha sido añadido al carrito!');
                resetCake();
            }).catch(e => console.error('Error:', e));
        }
        
        function updateCartCount(count = null) {
            const cartNav = document.getElementById('cart-count-nav');
            if (count !== null) { if (cartNav) cartNav.textContent = count; } 
            else { fetch('../ajax-cart.php').then(res => res.json()).then(data => { if (cartNav) cartNav.textContent = data.total_items || 0; }); }
        }

        document.querySelectorAll('.ingredient-categories .category-btn').forEach(b => b.onclick = () => renderIngredients(b.dataset.category));
        document.getElementById('reset-cake').addEventListener('click', resetCake);
        document.getElementById('add-to-cart').addEventListener('click', addCustomCakeToCart);
        
        fetchIngredients();
        updateCartCount();
    });
    </script>
</body>
</html>