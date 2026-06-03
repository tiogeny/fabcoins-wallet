/**
 * 🤖 MOTOR DE TRANSICIONES DE INTERFAZ DE NUEVA GENERACIÓN (LAB HUBS)
 */
function abrirWorkspaceHub(workspaceId, tarjetaElemento) {
    const homeHub = document.getElementById("main-home-hub-view");
    const targetWorkspace = document.getElementById(workspaceId);

    // 1. Efecto Fade-Out y Scale en los 3 Botones Gigantes
    homeHub.style.opacity = "0";
    homeHub.style.transform = "scale(0.95)";
    
    setTimeout(() => {
        homeHub.style.display = "none";
        
        // 2. Encender y deslizar el Espacio de Trabajo seleccionado
        targetWorkspace.style.display = "block";
        setTimeout(() => {
            targetWorkspace.classList.add("active");
        }, 50);
    }, 350);
}

function regresarAlHubCentral(workspaceId) {
    const homeHub = document.getElementById("main-home-hub-view");
    const activeWorkspace = document.getElementById(workspaceId);

    // 1. Desvanecer el Espacio de Trabajo activo
    activeWorkspace.classList.remove("active");
    
    setTimeout(() => {
        activeWorkspace.style.display = "none";
        
        // 2. Restaurar y reanimar la cabina de los 3 botones principales
        homeHub.style.display = "block";
        setTimeout(() => {
            homeHub.style.opacity = "1";
            homeHub.style.transform = "scale(1)";
        }, 50);
    }, 350);
}