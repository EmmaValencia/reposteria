<?php
// Ya no necesitamos toda la lógica de conexión aquí, solo la configuración de errores.
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    </head>
<body class="gallery-body">

    <header class="gallery-header">
        <h1 class="gallery-title">Galería 3D - Repostería</h1>
        <a href="../profile.php" class="aurora-button" style="text-decoration: none;">
            <div><span><i class="fas fa-arrow-left me-2"></i>Volver al Perfil</span></div>
        </a>
    </header>

    <main>
        <div class="fluid-grid" id="model-grid">
            </div>
        
        <div id="loading-indicator" style="text-align: center; padding: 20px; color: white; display: none;">
            <div class="loading-spinner" style="margin: 0 auto;"></div>
            <p style="margin-top: 10px;">Cargando más modelos...</p>
        </div>
        
        <p id="end-indicator" style="text-align: center; padding: 20px; color: #666; display: none;">--- Fin de la Galería ---</p>
    </main>

    <script type="module">
        import * as THREE from 'three';
        import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
        import { OrbitControls } from 'three/addons/controls/OrbitControls.js';

        // --- Variables de Control de Paginación ---
        const GRID_CONTAINER = document.getElementById('model-grid');
        const LOADING_INDICATOR = document.getElementById('loading-indicator');
        const END_INDICATOR = document.getElementById('end-indicator');
        
        let currentOffset = 0;
        const limit = 8; // Cargar 8 modelos por lote
        let isLoading = false;
        let allModelsLoaded = false;
        // ------------------------------------------

        // Función para inicializar un modelo 3D en miniatura (Casi igual que antes)
        function initModelPreview(modelo, containerId) {
            const container = document.getElementById(containerId);
            if (!container) return;
            
            const modelId = modelo.id;
            // Lógica de ruta correcta
            const modelPath = modelo.ruta_archivo.replace('Galeria3D/', '');
            const fallbackImgUrl = modelo.ruta_miniatura.replace('Galeria3D/', '');

            const width = container.clientWidth;
            const height = container.clientHeight;
            
            // ... (El código de inicialización de Three.js, escena, cámara, luces y animación es idéntico al bueno que ya tienes) ...
            
            // ----------------------------------------------------
            // COPIA EL CONTENIDO COMPLETO DE initModelPreview AQUÍ 
            // (desde 'const scene = new THREE.Scene();' hasta el 'return () => { ... }')
            // para que no quede incompleto.
            // ----------------------------------------------------

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
                    
                    // Lógica de centrado
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
                },
                undefined,
                function (error) {
                    console.error(`Error cargando modelo ID ${modelId} desde ${modelPath}:`, error);
                    // Mostrar imagen de respaldo
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

        // Función para inyectar el HTML de un modelo
        function createModelElement(modelo) {
            const ruta_relativa_miniatura = modelo.ruta_miniatura.replace('Galeria3D/', '');
            return `
                <div class="grid-item">
                    <a href="viewer.php?id=${modelo.id}">
                        <div class="model-3d-container" id="model-container-${modelo.id}" 
                             data-thumbnail-url="${ruta_relativa_miniatura}">
                            <div class="model-placeholder">
                                <div class="loading-spinner"></div>
                            </div>
                        </div>
                        <div class="grid-item-content">
                            <h2 class="grid-item-title">${modelo.nombre}</h2>
                            <p class="grid-item-desc">${modelo.descripcion}</p>
                        </div>
                    </a>
                </div>
            `;
        }

        // Función principal para cargar y renderizar modelos
        async function loadModels() {
            if (isLoading || allModelsLoaded) return;
            isLoading = true;
            LOADING_INDICATOR.style.display = 'block';

            try {
                const response = await fetch(`api.php?limit=${limit}&offset=${currentOffset}`);
                if (!response.ok) throw new Error('Error al cargar la API.');
                
                const modelos = await response.json();

                LOADING_INDICATOR.style.display = 'none';

                if (modelos.length === 0) {
                    allModelsLoaded = true;
                    END_INDICATOR.style.display = 'block';
                    return;
                }

                let htmlContent = '';
                modelos.forEach(modelo => {
                    htmlContent += createModelElement(modelo);
                });

                // Agrega los nuevos elementos al DOM
                GRID_CONTAINER.insertAdjacentHTML('beforeend', htmlContent);

                // Inicializa Three.js para cada nuevo modelo
                modelos.forEach((modelo, index) => {
                    // Escalonar la carga para que sea más suave
                    setTimeout(() => {
                        initModelPreview(modelo, `model-container-${modelo.id}`);
                    }, 100 * (index + 1)); 
                });
                
                // Actualiza el offset para la siguiente carga
                currentOffset += modelos.length;

                // Re-adjuntar eventos de hover (solo si usas CSS en JS)
                attachHoverEffects(); 

            } catch (error) {
                console.error("Fallo la carga de modelos:", error);
                LOADING_INDICATOR.style.display = 'none';
            } finally {
                isLoading = false;
            }
        }
        
        // Cargar el primer lote al iniciar
        document.addEventListener('DOMContentLoaded', loadModels);

        // Lógica de Scroll Infinito
        window.addEventListener('scroll', () => {
            // Si el scroll está a menos de 500px del final de la página
            if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 500) {
                loadModels();
            }
        });

        // Evento de hover (lo mantendremos en JS por si el CSS no cubre todo)
        function attachHoverEffects() {
            document.querySelectorAll('.grid-item:not(.hover-attached)').forEach(item => {
                item.classList.add('hover-attached'); // Prevenir adjuntar múltiples veces
                item.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-15px) scale(1.05)';
                    this.style.zIndex = '10';
                });
                
                item.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                    this.style.zIndex = '1';
                });
            });
        }
    </script>

</body>
</html>