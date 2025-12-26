<?php
session_start();
// Asegúrate de que solo los administradores puedan acceder a esta página.
// Aquí podrías agregar una comprobación de rol de usuario si tuvieras una.
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Pasteles - Sabores de Cristal</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../responsive.css">

</head>
<body>
    
    <header class="navbar navbar-expand-lg fixed-top navbar-scrolled">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <img src="../Images/LOGO.png" alt="Logo Sabores de Cristal">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item"><a class="nav-link" href="../index.php">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link" href="../tienda.php">Tienda</a></li>
                    <li class="nav-item"><a class="nav-link" href="pasteles.php">Personalizar Pastel</a></li>
                    
                    <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle"></i> Mi Cuenta
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="../profile.php">Mi Perfil</a></li>
                                <li><a class="dropdown-item" href="../orders.php">Mis Recompensas</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="admin_pasteles.php">Administrar Pasteles</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="../logout.php">Cerrar Sesión</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link nav-link-cta" href="../login.php">Iniciar Sesión</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </header>

    <main style="margin-top: 100px; padding: 2rem 0;">
        <div class="container">
            <h1 class="subtitulo-seccion">Administrar Productos de Pasteles</h1>
            
            <div class="admin-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 style="color: var(--dorado);">Gestión de Ingredientes</h4>
                    <button class="aurora-button" id="add-product-btn">
                        <div><span>Agregar Producto</span></div>
                    </button>
                </div>
                
                <div class="admin-actions">
                    <button class="category-btn active" data-category="all">Todos</button>
                    <button class="category-btn" data-category="panes">Panes</button>
                    <button class="category-btn" data-category="betun">Betún</button>
                    <button class="category-btn" data-category="rellenos">Rellenos</button>
                    <button class="category-btn" data-category="toppings">Toppings</button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-dark table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Categoría</th>
                                <th>Precio</th>
                                <th>Imagen</th>
                                <th>Textura</th>
                                <th>Descripción</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="products-table-body">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="background-color: var(--gris-oscuro); color: var(--crema);">
                <div class="modal-header" style="border-bottom: 1px solid var(--dorado);">
                    <h5 class="modal-title" id="productModalLabel">Agregar Producto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="product-form" enctype="multipart/form-data">
                        <input type="hidden" id="product-id_ingrediente">
                        <div class="mb-3">
                            <label for="product-name" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="product-name" required>
                        </div>
                        <div class="mb-3">
                            <label for="product-category" class="form-label">Categoría</label>
                            <select class="form-select" id="product-category" required>
                                <option value="">Selecciona una categoría</option>
                                <option value="panes">Panes</option>
                                <option value="betun">Betún</option>
                                <option value="rellenos">Rellenos</option>
                                <option value="toppings">Toppings</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="product-price" class="form-label">Precio</label>
                            <input type="number" step="0.01" class="form-control" id="product-price" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Imagen del Producto</label>
                            <div class="d-flex gap-3 mb-2">
                                <div class="flex-grow-1">
                                    <input type="text" class="form-control" id="product-image" placeholder="Nombre del archivo" readonly>
                                    <small class="form-text text-muted">Selecciona una imagen de la galería o sube una nueva</small>
                                </div>
                                <button type="button" class="btn upload-btn" id="browse-gallery-btn">
                                    <i class="fas fa-images"></i> Galería
                                </button>
                                <button type="button" class="btn upload-btn" id="upload-new-btn">
                                    <i class="fas fa-upload"></i> Subir
                                </button>
                            </div>
                            <div id="image-preview-container" class="mt-2" style="display: none;">
                                <img id="image-preview" class="image-preview" src="" alt="Vista previa">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Textura del Ingrediente</label>
                            <div class="d-flex gap-3 mb-2">
                                <div class="flex-grow-1">
                                    <input type="text" class="form-control" id="product-textura" placeholder="Nombre del archivo" readonly>
                                    <small class="form-text text-muted">Selecciona una textura de la galería o sube una nueva</small>
                                </div>
                                <button type="button" class="btn upload-btn" id="browse-textura-gallery-btn">
                                    <i class="fas fa-images"></i> Galería
                                </button>
                                <button type="button" class="btn upload-btn" id="upload-textura-new-btn">
                                    <i class="fas fa-upload"></i> Subir
                                </button>
                            </div>
                            <div id="textura-preview-container" class="mt-2" style="display: none;">
                                <img id="textura-preview" class="image-preview" src="" alt="Vista previa textura">
                            </div>
                        </div>

                        <div id="image-gallery-container" class="mt-2" style="display: none;">
                            <div class="image-gallery" id="image-gallery"></div>
                        </div>

                        <div id="upload-container" class="mt-2" style="display: none;">
                            <input type="file" class="form-control" id="new-image-upload" accept="image/png, image/jpeg, image/jpg, image/gif, image/webp">
                            <small class="form-text text-muted">Formatos permitidos: JPG, PNG, GIF, WEBP. Tamaño máximo: 2MB</small>
                        </div>

                        <div class="mb-3">
                            <label for="product-description" class="form-label">Descripción</label>
                            <textarea class="form-control" id="product-description" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer" style="border-top: 1px solid var(--dorado);">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="aurora-button" id="save-product-btn">
                        <div><span>Guardar Producto</span></div>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer-section"></footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let products = [];
            let currentCategory = 'all';
            let availableImages = [];
            let currentImageType = 'product'; // 'product' o 'textura'
            
            loadProducts();
            setupCategoryButtons();
            setupActionButtons();
            loadAvailableImages();
            
            function loadProducts() {
                fetch('obtener_productos_pasteles.php')
                    .then(response => response.json())
                    .then(data => {
                        products = data;
                        renderProducts(currentCategory);
                    })
                    .catch(error => console.error('Error:', error));
            }

            function loadAvailableImages() {
                fetch('obtener_imagenes_pasteles.php')
                    .then(response => response.json())
                    .then(data => { availableImages = data; })
                    .catch(error => console.error('Error:', error));
            }
            
            function setupCategoryButtons() {
                document.querySelectorAll('.admin-actions .category-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        document.querySelectorAll('.admin-actions .category-btn').forEach(btn => btn.classList.remove('active'));
                        this.classList.add('active');
                        currentCategory = this.dataset.category;
                        renderProducts(currentCategory);
                    });
                });
            }
            
            function setupActionButtons() {
                document.getElementById('add-product-btn').addEventListener('click', () => openProductModal());
                document.getElementById('save-product-btn').addEventListener('click', saveProduct);
                
                const setupImageButtons = (type) => {
                    document.getElementById(`browse-${type}-gallery-btn`).addEventListener('click', () => {
                        currentImageType = type;
                        showImageGallery();
                    });
                    document.getElementById(`upload-${type}-new-btn`).addEventListener('click', () => {
                        currentImageType = type;
                        showUploadSection();
                    });
                };
                
                setupImageButtons('product');
                setupImageButtons('textura');

                document.getElementById('new-image-upload').addEventListener('change', e => {
                    if (e.target.files[0]) uploadNewImage(e.target.files[0]);
                });
            }

            function showImageGallery() {
                document.getElementById('upload-container').style.display = 'none';
                const galleryContainer = document.getElementById('image-gallery-container');
                galleryContainer.style.display = 'block';
                
                const gallery = document.getElementById('image-gallery');
                gallery.innerHTML = availableImages.length ? '' : '<div class="text-center w-100">No hay imágenes disponibles</div>';
                
                availableImages.forEach(image => {
                    const img = document.createElement('img');
                    img.src = `../Images/pasteles/${image}`;
                    img.className = 'gallery-image';
                    img.onclick = () => {
                        document.querySelectorAll('.gallery-image').forEach(i => i.classList.remove('selected'));
                        img.classList.add('selected');
                        const targetId = currentImageType === 'product' ? 'product-image' : 'product-textura';
                        document.getElementById(targetId).value = image;
                        showImagePreview(`../Images/pasteles/${image}`, currentImageType);
                        setTimeout(() => { galleryContainer.style.display = 'none'; }, 300);
                    };
                    gallery.appendChild(img);
                });
            }

            function showUploadSection() {
                document.getElementById('image-gallery-container').style.display = 'none';
                document.getElementById('upload-container').style.display = 'block';
                document.getElementById('new-image-upload').value = '';
            }

            function uploadNewImage(file) {
                if (!file.type.match('image.*') || file.size > 2 * 1024 * 1024) {
                    alert('Archivo no válido. Debe ser imagen de menos de 2MB.');
                    return;
                }
                const formData = new FormData();
                formData.append('image', file);

                fetch('subir_imagen_pastel.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const targetId = currentImageType === 'product' ? 'product-image' : 'product-textura';
                        document.getElementById(targetId).value = data.filename;
                        showImagePreview(`../Images/pasteles/${data.filename}`, currentImageType);
                        loadAvailableImages();
                        document.getElementById('upload-container').style.display = 'none';
                        alert('Imagen subida.');
                    } else {
                        alert(`Error: ${data.message}`);
                    }
                })
                .catch(err => alert(`Error: ${err}`));
            }

            function showImagePreview(url, type) {
                const previewContainer = document.getElementById(`${type === 'product' ? 'image' : 'textura'}-preview-container`);
                const previewImg = document.getElementById(`${type === 'product' ? 'image' : 'textura'}-preview`);
                previewImg.src = url;
                previewContainer.style.display = 'block';
            }
            
            function renderProducts(category) {
                const tableBody = document.getElementById('products-table-body');
                tableBody.innerHTML = '';
                const filtered = category === 'all' ? products : products.filter(p => p.categoria_ingrediente === category);
                
                if (filtered.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="9" class="text-center">No hay productos</td></tr>';
                    return;
                }
                
                filtered.forEach(p => {
                    const row = `
                        <tr>
                            <td>${p.id_ingrediente}</td>
                            <td>${p.nombre_ingrediente}</td>
                            <td>${p.categoria_ingrediente}</td>
                            <td>$${p.precio_ingrediente}</td>
                            <td><img src="../Images/pasteles/${p.imagen_ingrediente || 'placeholder.jpg'}" class="image-preview"></td>
                            <td><img src="../Images/pasteles/${p.textura_ingrediente || 'placeholder.jpg'}" class="image-preview"></td>
                            <td>${p.descripcion_ingrediente || 'N/A'}</td>
                            <td><button class="action-btn status-btn ${p.activo_ingrediente == 1 ? '' : 'inactive'}" data-id_ingrediente="${p.id_ingrediente}">${p.activo_ingrediente == 1 ? 'Activo' : 'Inactivo'}</button></td>
                            <td>
                                <button class="action-btn edit-btn" data-id_ingrediente="${p.id_ingrediente}"><i class="fas fa-edit"></i></button>
                                <button class="action-btn delete-btn" data-id_ingrediente="${p.id_ingrediente}"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    `;
                    tableBody.innerHTML += row;
                });
                
                document.querySelectorAll('.edit-btn').forEach(b => b.onclick = e => openProductModal(e.currentTarget.dataset.id_ingrediente));
                document.querySelectorAll('.delete-btn').forEach(b => b.onclick = e => deleteProduct(e.currentTarget.dataset.id_ingrediente));
                document.querySelectorAll('.status-btn').forEach(b => b.onclick = e => toggleProductStatus(e.currentTarget.dataset.id_ingrediente));
            }
            
            function openProductModal(id = null) {
                const modal = new bootstrap.Modal(document.getElementById('productModal'));
                document.getElementById('product-form').reset();
                ['image-preview-container', 'textura-preview-container', 'image-gallery-container', 'upload-container'].forEach(el => document.getElementById(el).style.display = 'none');
                
                if (id) {
                    document.getElementById('productModalLabel').textContent = 'Editar Producto';
                    const p = products.find(prod => prod.id_ingrediente == id);
                    if (p) {
                        document.getElementById('product-id_ingrediente').value = p.id_ingrediente;
                        document.getElementById('product-name').value = p.nombre_ingrediente;
                        document.getElementById('product-category').value = p.categoria_ingrediente;
                        document.getElementById('product-price').value = p.precio_ingrediente;
                        document.getElementById('product-description').value = p.descripcion_ingrediente || '';
                        if (p.imagen_ingrediente) {
                            document.getElementById('product-image').value = p.imagen_ingrediente;
                            showImagePreview(`../Images/pasteles/${p.imagen_ingrediente}`, 'product');
                        }
                        if (p.textura_ingrediente) {
                            document.getElementById('product-textura').value = p.textura_ingrediente;
                            showImagePreview(`../Images/pasteles/${p.textura_ingrediente}`, 'textura');
                        }
                    }
                } else {
                    document.getElementById('productModalLabel').textContent = 'Agregar Producto';
                    document.getElementById('product-id_ingrediente').value = '';
                }
                modal.show();
            }
            
            function saveProduct() {
                const data = {
                    id_ingrediente: document.getElementById('product-id_ingrediente').value,
                    nombre_ingrediente: document.getElementById('product-name').value,
                    categoria_ingrediente: document.getElementById('product-category').value,
                    precio_ingrediente: document.getElementById('product-price').value,
                    imagen_ingrediente: document.getElementById('product-image').value,
                    textura_ingrediente: document.getElementById('product-textura').value,
                    descripcion_ingrediente: document.getElementById('product-description').value
                };
                
                if (!data.nombre_ingrediente || !data.categoria_ingrediente || !data.precio_ingrediente) {
                    alert('Por favor, completa los campos obligatorios.');
                    return;
                }
                
                const url = data.id_ingrediente ? 'actualizar_producto_pastel.php' : 'agregar_producto_pastel.php';
                fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) })
                .then(res => res.json())
                .then(result => {
                    if (result.success) {
                        bootstrap.Modal.getInstance(document.getElementById('productModal')).hide();
                        loadProducts();
                        alert(result.message);
                    } else {
                        alert(`Error: ${result.message}`);
                    }
                })
                .catch(err => alert(`Error: ${err}`));
            }
            
            function deleteProduct(id) {
                if (!confirm('¿Estás seguro de eliminar este producto?')) return;
                fetch('eliminar_producto_pastel.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id_ingrediente: id }) })
                .then(res => res.json())
                .then(handleResponse)
                .catch(err => alert(`Error: ${err}`));
            }
            
            function toggleProductStatus(id) {
                fetch('cambiar_estado_producto_pastel.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id_ingrediente: id }) })
                .then(res => res.json())
                .then(handleResponse)
                .catch(err => alert(`Error: ${err}`));
            }
            
            function handleResponse(data) {
                if (data.success) {
                    loadProducts();
                    alert(data.message);
                } else {
                    alert(`Error: ${data.message}`);
                }
            }
        });
    </script>
</body>
</html>