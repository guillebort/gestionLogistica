class Cabecera extends HTMLElement {
    constructor() {
        super();
        this.innerHTML = `
        <header class="bg-primary text-white text-center py-4">
            <h1>🚚 LogisTFG - Gestión de Repartos</h1>
        </header>`;
    }
}
window.customElements.define('mi-cabecera', Cabecera);

class Menu extends HTMLElement {
    constructor() {
        super();

    }

    connectedCallback(){
        // Leemos si Java nos ha pasado un nombre por HTML
        let nombreUsu = this.getAttribute('data-user');
        
        // Si hay nombre, ponemos "Hola, Paco". Si no, "👤 Usuario"
        let textoEnlace = (nombreUsu && nombreUsu.trim() !== "") ? `👋 Hola, ${nombreUsu}` : '👤 Usuario';
        // Menú con Bootstrap para que sea responsive
        this.innerHTML = `
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
            <div class="container-fluid">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav mx-auto">
                        <li class="nav-item"><a class="nav-link" href="index.jsp">Inicio</a></li>
                        <li class="nav-item"><a class="nav-link" href="empresa.html">Empresa</a></li>
                        <li class="nav-item"><a class="nav-link" href="productos.jsp">Productos/Servicios</a></li>
                        <li class="nav-item"><a class="nav-link" href="contacto.jsp">Contacto</a></li>
                        <li class="nav-item"><a class="nav-link" href="carrito.jsp">🛒 Carrito</a></li>
                        <li class="nav-item"><a class="nav-link" href="usuario.jsp">👤 Usuario</a></li>
                    </ul>
                </div>
            </div>
        </nav>`;
    }
}
window.customElements.define('mi-menu', Menu);

class Pie extends HTMLElement {
    constructor() {
        super();
        this.innerHTML = `
        <footer class="bg-dark text-white text-center py-4 mt-5">
            <p>&copy; 2024 - LogisTFG. Tu socio logístico.</p>
            <p>
                <a href="https://twitter.com" target="_blank" class="text-info mx-2">Twitter</a> | 
                <a href="https://instagram.com" target="_blank" class="text-danger mx-2">Instagram</a>
            </p>
        </footer>`;
    }
}
window.customElements.define('mi-pie', Pie);