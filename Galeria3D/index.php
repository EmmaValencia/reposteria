<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'db_config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("ERROR CRÍTICO: No se pudo conectar a la base de datos. Detalle: " . $conn->connect_error);
}
$conn->set_charset('utf8');

// --- CAMBIO CLAVE AQUÍ ---
// OFFSET 4 significa saltar los primeros 4 registros.
// Si tu pastel de cumpleaños es el registro ID 5, esto lo mostrará como el primer elemento.
$sql = "SELECT id, nombre, descripcion, ruta_archivo, ruta_miniatura FROM modelos ORDER BY id ASC LIMIT 100 OFFSET 4"; 
// Puse un LIMIT alto (100) para cargar el resto, pero puedes ajustarlo si sabes cuántos modelos tienes.
// También puedes quitar el LIMIT si quieres cargar todos los modelos después del offset.
// $sql = "SELECT id, nombre, descripcion, ruta_archivo, ruta_miniatura FROM modelos ORDER BY id ASC OFFSET 4"; // (Si tu versión de MySQL lo permite)
// La versión con LIMIT es la más segura:
// $sql = "SELECT id, nombre, descripcion, ruta_archivo, ruta_miniatura FROM modelos ORDER BY id ASC LIMIT 100 OFFSET 4";

$result = $conn->query($sql);

$modelos = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $modelos[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>

    <meta charset="UTF-8">
    <title>Galería 3D - Sabores de Cristal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="16x16" href="../Images/LOGO.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../Images/LOGO.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    <link rel="stylesheet" href="../style.css"> 
    <link rel="stylesheet" href="gallery-professional.css"> 
    <style>
        /* Tus estilos .model-3d-container, .model-placeholder, .loading-spinner ... (se mantienen igual) */
        .model-3d-container {
            width: 100%;
            height: 100%;
            background: #1a1a1a;
            border-radius: 8px;
            overflow: hidden;
            position: relative;
        }
        
        .model-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #2a2a2a;
            color: #666;
            font-size: 0.9rem;
        }
        
        .loading-spinner {
            width: 30px;
            height: 30px;
            border: 3px solid rgba(255,255,255,0.3);
            border-top: 3px solid var(--dorado);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="gallery-body">

    <header class="gallery-header">
        <h1 class="gallery-title">Galería 3D - Repostería</h1>
        <a href="../profile.php" class="aurora-button" style="text-decoration: none;">
            <div><span><i class="fas fa-arrow-left me-2"></i>Volver al Perfil</span></div>
        </a>
    </header>

    <main>
        <div class="fluid-grid">
            <?php if (!empty($modelos)): ?>
                <?php foreach ($modelos as $modelo): ?>
                    <?php
                        // --- ESTO ES IMPORTANTE PARA LAS IMÁGENES DE RESPALDO ---
                        $ruta_relativa_miniatura = str_replace('Galeria3D/', '', $modelo['ruta_miniatura']);
                    ?>
                    <div class="grid-item">
                        <a href="viewer.php?id=<?php echo $modelo['id']; ?>">
                            <div class="model-3d-container" id="model-container-<?php echo $modelo['id']; ?>" 
                                 data-thumbnail-url="<?php echo htmlspecialchars($ruta_relativa_miniatura); ?>">
                                <div class="model-placeholder">
                                    <div class="loading-spinner"></div>
                                </div>
                            </div>
                            
                            <div class="grid-item-content">
                                <h2 class="grid-item-title"><?php echo htmlspecialchars($modelo['nombre']); ?></h2>
                                <p class="grid-item-desc"><?php echo htmlspecialchars($modelo['descripcion']); ?></p>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="grid-column: 1 / -1; text-align: center; color: white; font-size: 1.2em;">No se encontraron modelos en la base de datos.</p>
            <?php endif; ?>
        </div>
    </main>

    <script type="importmap">
        {
            "imports": {
                "three": "https://unpkg.com/three@0.157.0/build/three.module.js",
                "three/addons/": "https://unpkg.com/three@0.157.0/examples/jsm/"
            }
        }
    </script>
    
    <script type="module">
        import * as THREE from 'three';
        import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
        import { OrbitControls } from 'three/addons/controls/OrbitControls.js';

        const modelConfigs = <?php echo json_encode($modelos); ?>;
        
        function initModelPreview(modelId, modelPath, containerId) {
            const container = document.getElementById(containerId);
            if (!container) return;
            
            const width = container.clientWidth;
            const height = container.clientHeight;
            
            const scene = new THREE.Scene();
            scene.background = new THREE.Color(0x1a1a1a);
            
            const camera = new THREE.PerspectiveCamera(45, width / height, 0.1, 1000);
            camera.position.set(0, 0, 5);
            
            const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
            renderer.setSize(width, height);
            renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
            
            container.innerHTML = '';
            container.appendChild(renderer.domElement);
            
            const controls = new OrbitControls(camera, renderer.domElement);
            controls.enableDamping = true;
            controls.dampingFactor = 0.05;
            controls.autoRotate = true;
            controls.autoRotateSpeed = 1.5;
            controls.enableZoom = false;
            controls.enablePan = false;
            // No hay min/max distance, ¡bien!
            
            const ambientLight = new THREE.AmbientLight(0xffffff, 1.2);
            scene.add(ambientLight);
            const directionalLight = new THREE.DirectionalLight(0xfff5e1, 1.5);
            directionalLight.position.set(2, 3, 4);
            scene.add(directionalLight);
            const fillLight = new THREE.DirectionalLight(0xfff0d4, 0.8);
            fillLight.position.set(-2, -1, 3);
            scene.add(fillLight);
            
            const loader = new GLTFLoader();
            loader.load(
                modelPath,
                function (gltf) {
                    const model = gltf.scene;
                    
                    // --- AQUÍ ESTÁ LA LÓGICA DE CENTRADO BUENA ---
                    scene.add(model);
                    const box = new THREE.Box3().setFromObject(model);
                    const size = box.getSize(new THREE.Vector3());
                    const center = box.getCenter(new THREE.Vector3());
                    model.position.sub(center);
                    const maxDim = Math.max(size.x, size.y, size.z);
                    const fov = camera.fov * (Math.PI / 180);
                    let cameraZ = Math.abs(maxDim / 2 / Math.tan(fov / 2));
                    cameraZ *= 1.5;
                    camera.position.set(0, 0, cameraZ);
                    controls.target.set(0, 0, 0);
                    camera.near = cameraZ * 0.01;
                    camera.far = cameraZ * 20;
                    camera.updateProjectionMatrix();
                    // --- FIN DE LA LÓGICA DE CENTRADO ---
                },
                undefined,
                function (error) {
                    console.error(`Error cargando modelo ID ${modelId} desde ${modelPath}:`, error);
                    // Usamos la URL de la miniatura que pusimos en el data-attribute
                    const fallbackImgUrl = container.dataset.thumbnailUrl || '';
                    if (fallbackImgUrl) {
                        container.innerHTML = `<img src="${fallbackImgUrl}" style="width:100%;height:100%;object-fit:cover;" alt="Modelo ${modelId}">`;
                    } else {
                         container.innerHTML = `<div class="model-placeholder">Error</div>`;
                    }
                }
            );
            
            function animate() {
                requestAnimationFrame(animate);
                controls.update();
                renderer.render(scene, camera);
            }
            animate();
            
            function handleResize() {
                const newWidth = container.clientWidth;
                const newHeight = container.clientHeight;
                camera.aspect = newWidth / newHeight;
                camera.updateProjectionMatrix();
                renderer.setSize(newWidth, newHeight);
            }
            window.addEventListener('resize', handleResize);
            
            return () => {
                window.removeEventListener('resize', handleResize);
                renderer.dispose();
            };
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            modelConfigs.forEach(modelo => {
                
                // --- AQUÍ ESTÁ LA LÓGICA DE RUTA BUENA ---
                const rutaModelo = modelo.ruta_archivo.replace('Galeria3D/', '');

                setTimeout(() => {
                    initModelPreview(
                        modelo.id, 
                        rutaModelo, 
                        `model-container-${modelo.id}`
                    );
                }, 100 * modelo.id);
            });
        });
        
        // Efecto de hover (se mantiene igual)
        document.querySelectorAll('.grid-item').forEach(item => {
            item.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-15px) scale(1.05)';
                this.style.zIndex = '10';
            });
            
            item.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
                this.style.zIndex = '1';
            });
        });
    </script>

</body>
</html>