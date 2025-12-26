<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda - Sabores de Cristal</title>
    
    <link rel="icon" type="image/png" sizes="16x16" href="Images/LOGO.png">
    <link rel="icon" type="image/png" sizes="32x32" href="Images/LOGO.png">
    <link rel="apple-touch-icon" sizes="180x180" href="Images/LOGO-min.png">
    <link rel="shortcut icon" href="Images/LOGO-min.png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="responsive.css">
</head>
<body>
    
    <header class="navbar navbar-expand-lg fixed-top navbar-scrolled">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="Images/LOGO.png" alt="Logo Sabores de Cristal">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item"><a class="nav-link" href="index.php">Inicio</a></li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php" aria-label="Carrito">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="badge rounded-pill bg-danger" id="cart-count-nav">0</span>
                        </a>
                    </li>
                    
                    <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle"></i> Mi Cuenta
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="profile.php">Mi Perfil</a></li>
                                <li><a class="dropdown-item" href="orders.php">Mis Recompensas</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Cerrar Sesión</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link nav-link-cta" href="login.php">Iniciar Sesión</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </header>

<main>
        <section class="store-header">
            <div class="header-bg-overlay"></div> 
            <div class="container position-relative z-2">
                <div class="row align-items-center">
                    <div class="col-lg-6 text-center text-lg-start mb-5 mb-lg-0 animate-fade-right">
                        <span class="badge bg-dorado text-dark mb-3 px-3 py-2 text-uppercase fw-bold">Recomendación del Chef</span>
                        <h1 class="featured-product-title display-3">Pastel de Chocolate Intenso</h1>
                        <p class="lead my-4 text-white-50">Una experiencia sensorial única. Capas de bizcocho húmedo de cacao infusionado con café artesanal y un ganache de chocolate belga al 70%.</p>
                        <div class="d-flex align-items-center justify-content-center justify-content-lg-start gap-4">
                            <p class="featured-product-price mb-0">$350.00</p>
                            <a href="#productos" class="aurora-button">
                                <div><span>Ver Catálogo</span></div>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-6 text-center">
                        <div class="hero-image-wrapper floating-animation">
                            <div class="circle-backdrop"></div>
                            <img src="Images/Dia de Muertos-min.jpeg" alt="Pastel de Chocolate" class="img-fluid featured-product-image">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="product-grid" id="productos">
            <div class="container">
                
                <div class="section-header text-center mb-5">
                    <h2 class="subtitulo-seccion">Nuestra Colección</h2>
                    <p class="text-white-50">Horneado diariamente con amor y los mejores ingredientes.</p>
                </div>

                <div class="toolbar-container mb-5 d-flex justify-content-between align-items-center flex-wrap gap-3 p-3 rounded-3">
                    <div class="category-pills">
                        <button class="btn btn-sm btn-outline-gold category-filter-btn active" data-category="Todos">Todos</button>
                        <button class="btn btn-sm btn-outline-gold category-filter-btn" data-category="Pasteles">Pasteles</button>
                        <button class="btn btn-sm btn-outline-gold category-filter-btn" data-category="Pan Dulce">Pan Dulce</button>
                        <button class="btn btn-sm btn-outline-gold category-filter-btn" data-category="Temporada">Temporada</button>
                    </div>
                    <div class="sort-dropdown">
                        <select class="form-select form-select-sm bg-dark text-light border-gold">
                            <option selected>Ordenar por: Destacados</option>
                            <option value="1">Precio: Menor a Mayor</option>
                            <option value="2">Precio: Mayor a Menor</option>
                        </select>
                    </div>
                </div>

                <!-- Contenedor donde se cargarán los productos dinámicamente -->
                <div class="row g-4" id="products-container">
                    <!-- Los productos se cargarán aquí por JavaScript -->
                </div>
            </div>
        </section>
    </main>    

    <!-- Container para toast notifications -->
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>

    <!-- Carrito flotante -->
    <div id="live-cart-sidebar" class="cart-sidebar-hidden">
        <div class="cart-header">
            <h4>Mi Orden</h4>
            <button id="close-cart-btn" class="btn-close btn-close-white"></button>
        </div>
        <div id="live-cart-items-container" class="cart-items"></div>
        <div class="cart-footer">
            <div class="d-flex justify-content-between fs-5 mb-2">
                <span>Items:</span>
                <span id="live-cart-total-items">0</span>
            </div>
            <div class="d-flex justify-content-between fs-5 mb-3">
                <span>Subtotal:</span>
                <span id="live-cart-subtotal">$0.00</span>
            </div>
            <div class="d-grid mt-3 d-flex justify-content-center">
                <a href="cart.php" class="aurora-button" style="text-decoration: none;">
                    <div><span>Ver Carrito y Pagar</span></div>
                </a>
            </div>
        </div>
    </div>
    
    <div id="floating-cart-icon">
        <i class="fas fa-shopping-bag"></i>
        <span class="badge rounded-pill bg-danger" id="cart-count-icon">0</span>
    </div>
    
    <footer class="footer-section">
        <!-- Tu footer aquí -->
    </footer>

    <!-- Modal de Producto -->
    <div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background: var(--gris-oscuro); color: var(--crema); border: 1px solid var(--dorado);">
                <div class="modal-header" style="border-bottom: 1px solid var(--negro);">
                    <h5 class="modal-title" id="productModalLabel" style="color: var(--dorado);">Añadir Producto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <img id="modal-product-image" src="" alt="Imagen del producto" class="img-fluid rounded" style="max-height: 200px;">
                    </div>
                    <h4 id="modal-product-name" class="text-center" style="color: var(--dorado);"></h4>
                    <p id="modal-product-price" class="text-center fs-4 fw-bold"></p>
                    
                    <form id="add-to-cart-form">
                        <input type="hidden" id="modal-product-id" name="producto_id">
                        
                        <div class="mb-3">
                            <label for="modal-quantity" class="form-label">Cantidad:</label>
                            <div class="input-group" style="width: 150px; margin: auto;">
                                <button class="btn btn-outline-secondary" type="button" id="quantity-minus">-</button>
                                <input type="number" id="modal-quantity" name="cantidad" class="form-control text-center" value="1" min="1">
                                <button class="btn btn-outline-secondary" type="button" id="quantity-plus">+</button>
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="add-instructions-checkbox">
                            <label class="form-check-label" for="add-instructions-checkbox">
                                Agregar alguna instrucción especial
                            </label>
                        </div>
                        
                        <div class="mb-3" id="instructions-container" style="display: none;">
                            <label for="modal-instructions" class="form-label">Instrucciones:</label>
                            <textarea id="modal-instructions" name="instrucciones" class="form-control" rows="3" placeholder="Ej: 'Sin nuez', 'Con una nota de felicitación', etc."></textarea>
                        </div>
                        
                        <div class="d-grid">
                           <button type="submit" class="aurora-button">
                                <div><span>Agregar al Carrito</span></div>
                           </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
    <script src="//code.tidio.co/hozkxtcboflla4min0ycifog36zo1tz9.js" async></script>
</body>
</html>