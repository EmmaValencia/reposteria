<?php
// --- LÓGICA PHP (SIN CAMBIOS) ---
if (!isset($_GET['id'])) { die("ID de modelo no especificado."); }
$current_model_id = intval($_GET['id']);

// Asumimos que tienes un archivo 'config.php' o 'db_config.php' que define las constantes
// Si no, reemplaza con los datos de tu 'config.php'
define('DB_HOST', 'localhost');
define('DB_USER', 'u686732311_Sabores'); // Reemplaza si es necesario
define('DB_PASS', 'Cuchit@Pichichis9901221512'); // Reemplaza si es necesario
define('DB_NAME', 'u686732311_saboresdc'); // Reemplaza si es necesario

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) { die("Conexión fallida: " . $conn->connect_error); }
$conn->set_charset('utf8');

$stmt = $conn->prepare("SELECT id, nombre, descripcion, ruta_archivo FROM modelos WHERE id = ?");
$stmt->bind_param("i", $current_model_id);
$stmt->execute();
$current_model = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$current_model) { die("Modelo no encontrado."); }

if (strpos($current_model['ruta_archivo'], 'Galeria3D/') === 0) {
    $ruta_relativa_modelo = str_replace('Galeria3D/', '', $current_model['ruta_archivo']);
} else {
    $ruta_relativa_modelo = $current_model['ruta_archivo'];
}

$prev_model_id = $conn->query("SELECT id FROM modelos WHERE id < $current_model_id ORDER BY id DESC LIMIT 1")->fetch_assoc()['id'] ?? null;
$next_model_id = $conn->query("SELECT id FROM modelos WHERE id > $current_model_id ORDER BY id ASC LIMIT 1")->fetch_assoc()['id'] ?? null;

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Visor 3D: <?php echo htmlspecialchars($current_model['nombre']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="16x16" href="../Images/LOGO.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../Images/LOGO.png">

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>

    <style>
        /* ========================================================================= */
        /* === 1. CONFIGURACIÓN GLOBAL Y VARIABLES (Paleta "Sabores de Cristal") === */
        /* ========================================================================= */
        :root {
            --negro: #1C1C1C;
            --dorado: #CBA135;
            --crema: #FAF3E0;
            --burdeos: #7B2D26;
            --burdeos-claro: #A53D34;
            --gris-oscuro: #2a2a2a;
            --gris-panel: #242424; /* Panel lateral */
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            margin: 0;
            padding: 0;
            background-color: var(--negro);
            font-family: 'Montserrat', sans-serif;
            color: var(--crema);
            height: 100vh;
            overflow: hidden; /* Evita el scroll en la página del visor */
        }

        /* ========================================================================= */
        /* === 2. ESTRUCTURA PRINCIPAL DEL VISOR (Header, Content, Nav) === */
        /* ========================================================================= */
        .viewer-wrapper {
            display: flex;
            flex-direction: column;
            height: 100vh;
            background-color: var(--negro);
        }

        /* --- 2.1 Encabezado --- */
        .viewer-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem 2.5rem; /* Más padding */
            background-color: var(--gris-panel);
            border-bottom: 2px solid var(--dorado);
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            z-index: 10;
            flex-shrink: 0; /* Evita que se encoja */
        }

        .viewer-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            color: var(--dorado);
            margin: 0;
        }

        /* --- 2.2 Contenido Principal (Visor + Panel) --- */
        .viewer-main-content {
            flex-grow: 1; /* Ocupa todo el espacio restante */
            display: flex;
            overflow: hidden; 
            position: relative;
        }

        /* --- 2.3 Contenedor del Canvas 3D --- */
        #viewer-container {
            flex-grow: 1;
            width: 70%; /* Mantenemos tu split 70/30 */
            height: 100%;
            position: relative;
            background-color: var(--negro); /* Fondo oscuro para el visor */
            display: flex;
            align-items: center;
            justify-content: center;
            color: #777;
        }

        /* --- 2.4 Panel de Información (Lateral) --- */
        .model-info {
            width: 30%; /* Mantenemos tu split 70/30 */
            flex-shrink: 0; /* Evita que se encoja */
            padding: 2.5rem; /* Más padding */
            background-color: var(--gris-panel);
            color: var(--crema);
            overflow-y: auto; /* Scroll si el texto es largo */
            border-left: 1px solid #333;
            height: 100%;
        }

        /* --- 2.5 Navegación (Footer) --- */
        .viewer-nav {
            display: flex;
            justify-content: space-between;
            padding: 1rem 2.5rem;
            background-color: var(--gris-panel);
            border-top: 1px solid #333;
            box-shadow: 0 -4px 15px rgba(0,0,0,0.3);
            z-index: 10;
            flex-shrink: 0; /* Evita que se encoja */
        }
        
        /* ========================================================================= */
        /* === 3. ESTILOS DE COMPONENTES (Botones, Texto, Spinner) === */
        /* ========================================================================= */

        /* --- Botón "Volver" (Estilo Aurora) --- */
        .aurora-button {
            all: unset;
            display: inline-block;
            box-sizing: border-box;
            background: transparent;
            border-width: 0;
            transition: transform 0.2s ease;
            text-decoration: none;
        }
        .aurora-button > div {
            display: block;
            padding: 0.8em 1.2em;
            background: var(--burdeos);
            color: var(--crema);
            font-weight: bold;
            border-radius: 8px;
            font-size: 0.9rem;
            position: relative;
            cursor: pointer;
            text-align: center;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }
        .aurora-button > div > span {
            color: var(--crema);
            font-weight: 600;
            font-family: 'Montserrat', sans-serif;
        }
        .aurora-button:hover > div {
            background-color: var(--burdeos-claro);
            transform: scale(1.05);
        }

        /* --- Texto del Panel de Información --- */
        .info-title {
            font-size: 2.5rem; /* Más grande */
            margin-bottom: 10px;
            color: var(--dorado);
            border-bottom: 2px solid var(--dorado); /* Línea dorada */
            padding-bottom: 10px;
            line-height: 1.2;
        }

        .info-subtitle {
            font-size: 1rem;
            color: #ccc;
            margin-bottom: 25px;
            font-weight: 300;
            font-family: 'Montserrat', sans-serif;
            opacity: 0.7;
        }

        .info-description {
            font-size: 1rem;
            line-height: 1.7; /* Más espacio entre líneas */
            margin-top: 20px;
            padding: 15px;
            border-left: 4px solid var(--dorado);
            background-color: var(--negro); /* Contraste oscuro */
            border-radius: 4px;
        }

        /* --- Spinner de Carga --- */
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid rgba(255, 255, 255, 0.2); /* Pista suave */
            border-top: 5px solid var(--dorado); /* Indicador dorado */
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* --- Botones de Navegación (Prev/Next) --- */
        .nav-arrow {
            color: var(--dorado);
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .nav-arrow:hover {
            background-color: var(--dorado);
            color: var(--negro);
        }
        /* Oculta el link si no existe (PHP lo omite) */
        .nav-arrow.disabled {
            opacity: 0.3;
            pointer-events: none;
        }

        /* ========================================================================= */
        /* === 4. DISEÑO RESPONSIVE (Móvil) === */
        /* ========================================================================= */
        @media (max-width: 900px) {
            body {
                height: auto; /* Permite scroll en móvil */
                overflow: auto;
            }
            .viewer-wrapper {
                height: auto; /* Altura automática */
            }
            .viewer-header {
                flex-direction: column;
                gap: 1rem;
                padding: 1rem;
                text-align: center;
            }
            .viewer-main-content {
                flex-direction: column; /* Apila el visor y la info */
            }
            #viewer-container {
                width: 100%;
                height: 60vh; /* El visor ocupa el 60% de la altura */
            }
            .model-info {
                width: 100%;
                height: auto; /* Altura automática para el contenido */
                padding: 1.5rem;
                border-left: none;
                border-top: 2px solid #333;
            }
            .info-title {
                font-size: 1.8rem;
            }
            .viewer-nav {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>

    <div class="viewer-wrapper">
        
        <header class="viewer-header">
            <h1 class="viewer-title">Visor de Modelos 3D</h1>
            <a href="index.php" class="aurora-button" style="text-decoration: none;">
                <div><span><i class="fas fa-th-large me-2"></i> Volver a la Galería</span></div>
            </a>
        </header>

        <div class="viewer-main-content">
            
            <main id="viewer-container">
                <div class="loading-spinner" id="loading-indicator"></div>
                </main>
            
            <aside class="model-info">
                <h2 class="info-title"><?php echo htmlspecialchars($current_model['nombre']); ?></h2>
                <h3 class="info-subtitle">ID de modelo: #<?php echo htmlspecialchars($current_model['id']); ?></h3>
                
                <p class="info-description">
                    <?php 
                    // Usamos nl2br para respetar los saltos de línea de la descripción
                    echo nl2br(htmlspecialchars($current_model['descripcion'])); 
                    ?>
                </p>
            </aside>
        </div>

        <nav class="viewer-nav">
            <?php if ($prev_model_id): ?>
                <a href="viewer.php?id=<?php echo $prev_model_id; ?>" class="nav-arrow prev" title="Anterior"><i class="fas fa-chevron-left"></i> Anterior</a>
            <?php else: ?>
                <span class="nav-arrow disabled"><i class="fas fa-chevron-left"></i> Anterior</span>
            <?php endif; ?>
            
            <?php if ($next_model_id): ?>
                <a href="viewer.php?id=<?php echo $next_model_id; ?>" class="nav-arrow next" title="Siguiente">Siguiente <i class="fas fa-chevron-right"></i></a>
            <?php else: ?>
                <span class="nav-arrow disabled">Siguiente <i class="fas fa-chevron-right"></i></span>
            <?php endif; ?>
        </nav>
    </div>

    <script type="importmap">
        { "imports": { "three": "https://unpkg.com/three@0.157.0/build/three.module.js", "three/addons/": "https://unpkg.com/three@0.157.0/examples/jsm/" } }
    </script>
    
    <script type="module">
        import * as THREE from 'three';
        import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
        import { OrbitControls } from 'three/addons/controls/OrbitControls.js';

        const container = document.getElementById('viewer-container');
        const loadingIndicator = document.getElementById('loading-indicator');
        const scene = new THREE.Scene();
        
        // Fondo del visor 3D (negro)
        scene.background = new THREE.Color(0x1C1C1C); 
        
        const camera = new THREE.PerspectiveCamera(50, container.clientWidth / container.clientHeight, 0.1, 1000);
        
        const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
        renderer.setSize(container.clientWidth, container.clientHeight);
        renderer.setPixelRatio(window.devicePixelRatio);
        
        const controls = new OrbitControls(camera, renderer.domElement);
        controls.enableDamping = true;
        controls.autoRotate = true; // Rotación automática
        controls.autoRotateSpeed = 0.85;

        // Iluminación
        const ambientLight = new THREE.AmbientLight(0xffffff, 1.5);
        scene.add(ambientLight);
        const keyLight = new THREE.DirectionalLight(0xffffff, 2.0);
        keyLight.position.set(-3, 4, 5);
        scene.add(keyLight);
        
        // Carga del modelo
        const loader = new GLTFLoader();
        loader.load(
            '<?php echo htmlspecialchars($ruta_relativa_modelo); ?>',
            function (gltf) {
                // ÉXITO: Ocultar el spinner y añadir el canvas
                loadingIndicator.style.display = 'none';
                container.appendChild(renderer.domElement);

                const model = gltf.scene;
                
                // --- LÓGICA DE CENTRADO (Mantenida) ---
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
                scene.add(model);
                // --- FIN DE LÓGICA DE CENTRADO ---
            },
            undefined, // onProgress
            function(error) { 
                console.error('Error al cargar el modelo:', error);
                // ERROR: Ocultar spinner y mostrar mensaje
                loadingIndicator.style.display = 'none';
                container.innerHTML = '<div style="text-align: center; color: #ff6b6b;"><i class="fas fa-exclamation-triangle fa-2x" style="margin-bottom: 10px;"></i><p>Error al cargar el modelo.<br>No se encontró el archivo o la ruta es incorrecta.</p></div>';
            }
        );

        function animate() {
            requestAnimationFrame(animate);
            controls.update();
            renderer.render(scene, camera);
        }
        animate();

        // Ajustar el visor si la ventana cambia de tamaño
        window.addEventListener('resize', () => {
            const newWidth = container.clientWidth;
            const newHeight = container.clientHeight;
            camera.aspect = newWidth / newHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(newWidth, newHeight);
        });
    </script>

</body>
</html>