<?php
// obtener_productos.php

require_once 'config.php'; // Asegúrate de que esta ruta es correcta
$conn = conectar_db();

// 1. Obtener el filtro de categoría. Si no se envía, usa 'Todos'.
$categoria = isset($_GET['categoria']) ? $_GET['categoria'] : 'Todos';

// 2. Consulta SQL con lógica de filtrado
$sql = "SELECT id, nombre, precio, imagen FROM productos";

if ($categoria !== 'Todos') {
    // PREVENCIÓN DE INYECCIÓN SQL: Escapamos el valor de la categoría
    $categoria_segura = $conn->real_escape_string($categoria);
    // Filtra por la nueva columna 'categoria'
    $sql .= " WHERE categoria = '$categoria_segura'";
}

$sql .= " ORDER BY id DESC";

$resultado_productos = $conn->query($sql);

$delay = 0;
$output = '';

if ($resultado_productos && $resultado_productos->num_rows > 0) {
    while($producto = $resultado_productos->fetch_assoc()):
        $delay += 0.1;
        // El HTML del producto (copia del que te envié en el paso anterior)
        $output .= '
        <div class="col-lg-4 col-md-6 animate-on-scroll" style="animation-delay: ' . $delay . 's;">
            <div class="product-card h-100" 
                 data-id="' . $producto['id'] . '"
                 data-nombre="' . htmlspecialchars($producto['nombre']) . '"
                 data-precio="' . number_format($producto['precio'], 2) . '"
                 data-imagen="' . htmlspecialchars($producto['imagen']) . '">
                
                <div class="card-badge">Nuevo</div>
                
                <div class="image-container">
                    <img src="' . htmlspecialchars($producto['imagen']) . '" class="card-img-top zoom" alt="' . htmlspecialchars($producto['nombre']) . '">
                    <div class="overlay-actions">
                        <button type="button" class="btn-quick-view open-product-modal">
                            <i class="fas fa-eye"></i> Vista Rápida
                        </button>
                    </div>
                </div>
                
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0">' . htmlspecialchars($producto['nombre']) . '</h5>
                            <div class="rating text-gold small">
                                <i class="fas fa-star"></i> 5.0
                            </div>
                        </div>
                        <p class="card-text text-white-50 small">Delicioso y artesanal.</p>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top border-secondary">
                        <p class="card-price mb-0 text-dorado fw-bold fs-4">$' . number_format($producto['precio'], 2) . '</p>
                        <button type="button" class="btn-add-icon open-product-modal" aria-label="Añadir">
                            <i class="fas fa-shopping-bag"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>';
    endwhile;
} else {
    $output = "<div class='col-12 text-center'><p class='text-white fs-4'>No hay productos disponibles en esta categoría.</p></div>";
}

$conn->close();

echo $output;

?>