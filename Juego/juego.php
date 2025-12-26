<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Juego de Repostería - Sabores de Cristal</title>
    <link rel="stylesheet" href="../style.css"> 
    <style>
        /* Estilos básicos para el juego */
        #game-container {
            max-width: 900px;
            margin: 50px auto;
            padding: 30px;
            background: var(--gris-oscuro-panel);
            border-radius: 15px;
            box-shadow: 0 10px 20px var(--shadow-color);
            min-height: 60vh;
        }
        #game-area {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 20px;
            margin-top: 20px;
        }
        #ingredients-panel {
            background: var(--gris-oscuro-base);
            padding: 15px;
            border-radius: 10px;
            max-height: 500px;
            overflow-y: auto;
        }
        #recipe-area {
            background: #222;
            border-radius: 10px;
            padding: 20px;
            position: relative;
            text-align: center;
        }
        .draggable-ingredient {
            width: 80px; height: 80px; 
            margin: 10px;
            cursor: grab;
            border-radius: 50%;
            background-size: cover;
            display: inline-block;
            box-shadow: 0 4px 8px rgba(0,0,0,0.5);
            /* Necesitarás una imagen de fondo para cada ingrediente */
        }
        #timer, #score {
            font-size: 1.5rem;
            color: var(--dorado);
            margin-bottom: 15px;
        }
        /* Estilos para el Drop Zone (la base del pastel) */
        #cake-base {
            width: 300px; height: 300px; 
            background: #555; border-radius: 50%;
            margin: 50px auto 0;
            position: relative;
            border: 5px dashed var(--crema);
        }
    </style>
</head>
<body class="gallery-body">
    <header class="gallery-header">
        <h1 class="gallery-title">🎂 Constructor de Postres</h1>
    </header>

    <main id="game-container">
        <div style="text-align: center;">
            <div id="timer">Tiempo: 60s</div>
            <div id="score">Puntuación: 0</div>
            <button id="start-button" class="aurora-button">
                <div><span>Comenzar Juego</span></div>
            </button>
        </div>

        <div id="game-area" style="display: none;">
            <div id="ingredients-panel">
                <h3>Ingredientes</h3>
                <div id="ing1" class="draggable-ingredient" style="background-color: #f7a; border: 3px solid white;" data-type="frosting"></div>
                <div id="ing2" class="draggable-ingredient" style="background-color: #a44; border: 3px solid gold;" data-type="cherry"></div>
                <div id="ing3" class="draggable-ingredient" style="background-color: #3d3; border: 3px solid white;" data-type="sprinkles"></div>
                </div>
            
            <div id="recipe-area">
                <h3>Receta a Seguir</h3>
                <div id="cake-base">
                    <p style="color:white; padding-top: 100px;">Arrastra la base del pastel aquí</p>
                </div>
                </div>
        </div>
    </main>
    <script>
        /* Archivo: purple_place.php o juego.php (dentro de <script>) */

// Necesitarás las funciones de autenticación del usuario para saber a quién asignarle la recompensa.
// Asumo que tienes una variable global con el ID del usuario.
const USER_ID = 1; // **¡CAMBIA ESTO!** Debe ser el ID del usuario logueado.

const gameArea = document.getElementById('game-area');
const startButton = document.getElementById('start-button');
const timerDisplay = document.getElementById('timer');
const scoreDisplay = document.getElementById('score');
const ingredients = document.querySelectorAll('.draggable-ingredient');
const dropZone = document.getElementById('cake-base');

let score = 0;
let timeLeft = 60;
let gameInterval;
let gameRunning = false;

// 1. Iniciar el Juego
startButton.addEventListener('click', startGame);

function startGame() {
    if (gameRunning) return;
    gameRunning = true;
    score = 0;
    timeLeft = 60;
    scoreDisplay.textContent = `Puntuación: ${score}`;
    startButton.style.display = 'none';
    gameArea.style.display = 'grid';
    
    // Iniciar el temporizador
    gameInterval = setInterval(updateTimer, 1000);
    
    // Configurar los eventos de Drag and Drop
    setupDragDropEvents();
    // (Opcional) Generar una nueva receta aleatoria
    // generateRecipe();
}

function updateTimer() {
    timeLeft--;
    timerDisplay.textContent = `Tiempo: ${timeLeft}s`;
    
    if (timeLeft <= 0) {
        endGame();
    }
}

// 2. Lógica de Drag and Drop
function setupDragDropEvents() {
    ingredients.forEach(ing => {
        ing.addEventListener('dragstart', (e) => {
            e.dataTransfer.setData('text/plain', e.target.dataset.type);
        });
    });

    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault(); // Permitir el drop
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        const ingredientType = e.dataTransfer.getData('text/plain');
        
        // **Lógica de Puntuación:**
        // Si el ingrediente es el correcto para la siguiente capa/decoración:
        if (checkRecipeStep(ingredientType)) {
            score += 100;
            scoreDisplay.textContent = `Puntuación: ${score}`;
            // Crear elemento visual del ingrediente en el pastel
            // ... (código para dibujar el ingrediente en la dropZone) ...
        } else {
            // Penalización por ingrediente incorrecto
            score = Math.max(0, score - 50);
            scoreDisplay.textContent = `Puntuación: ${score}`;
        }
        
        // Si el pastel está terminado:
        // endGame(); 
    });
}

function checkRecipeStep(ingredientType) {
    // **AQUÍ VA TU LÓGICA DE RECETA**
    // Por ejemplo: si la receta pide 'frosting' y el usuario arrastró 'frosting', retorna true.
    return true; // Simplificado por ahora
}


// 3. Finalizar el Juego y Guardar Puntuación
function endGame() {
    clearInterval(gameInterval);
    gameRunning = false;
    
    // Mostrar el resultado final
    alert(`¡Juego Terminado! Puntuación final: ${score}`);
    
    // Ocultar el juego y mostrar el botón de inicio
    gameArea.style.display = 'none';
    startButton.style.display = 'block';

    // **Paso clave: Enviar la puntuación al backend para la recompensa**
    saveScoreAndGetReward(score);
}

// 4. Integración con el Backend (PHP)
function saveScoreAndGetReward(finalScore) {
    fetch('api_rewards.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            user_id: USER_ID, 
            score: finalScore 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`¡Felicidades! Has ganado ${data.reward_points} Puntos de Recompensa.`);
        } else {
            alert(`Error al guardar la puntuación: ${data.message}`);
        }
    })
    .catch(error => {
        console.error('Error de comunicación con el servidor:', error);
        alert('Ocurrió un error al canjear el premio.');
    });
}

// Cargar el juego al inicio
// document.addEventListener('DOMContentLoaded', ...); // No necesario si el script está al final
    </script>
    </body>
</html>