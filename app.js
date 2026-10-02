// Clase para gestionar el Registro, Login, Sesión y Recuperación
class AuthManager {
    constructor() {
        this.modal = document.getElementById('loginModal');
        this.btnOpen = document.getElementById('btnLoginOpen');
        this.btnClose = document.getElementById('btnCloseModal');
        this.btnSubmit = document.getElementById('btnSubmitForm');
        this.btnLogout = document.getElementById('btnLogout');
        this.btnToggleMode = document.getElementById('btnToggleMode');
        this.btnRecoverMode = document.getElementById('btnRecoverMode');
        this.btnAdminPanel = document.getElementById('btnAdminPanel'); 
        
        this.userInput = document.getElementById('usernameInput');
        this.passInput = document.getElementById('passwordInput');
        this.greeting = document.getElementById('userGreeting');
        this.modalTitle = document.getElementById('modalTitle');
        this.toggleText = document.getElementById('toggleText');
        this.alertMessage = document.getElementById('alertMessage');

        this.currentMode = 'login'; 
        this.initEvents();
    }

    initEvents() {
        this.btnOpen.addEventListener('click', () => {
            this.setMode('login');
            this.modal.classList.remove('d-none');
        });
        
        this.btnClose.addEventListener('click', () => this.modal.classList.add('d-none'));
        
        // Alternar entre Login y Registro
        this.btnToggleMode.addEventListener('click', () => {
            this.setMode(this.currentMode === 'login' ? 'register' : 'login');
        });

        // Alternar a Recuperación de contraseña
        this.btnRecoverMode.addEventListener('click', () => {
            this.setMode('recover');
        });
        
        this.btnSubmit.addEventListener('click', () => this.handleSubmit());
        this.btnLogout.addEventListener('click', () => this.logout());
    }

    setMode(mode) {
        this.currentMode = mode;
        this.clearAlerts();
        this.btnRecoverMode.classList.remove('d-none');
        
        if (mode === 'login') {
            this.modalTitle.innerText = "Iniciar Sesión";
            this.btnSubmit.innerText = "Entrar";
            this.passInput.placeholder = "Contraseña";
            this.toggleText.innerText = "¿No tienes cuenta?";
            this.btnToggleMode.innerText = "Regístrate";
        } else if (mode === 'register') {
            this.modalTitle.innerText = "Crear Cuenta";
            this.btnSubmit.innerText = "Registrarme";
            this.passInput.placeholder = "Crea una contraseña";
            this.toggleText.innerText = "¿Ya tienes cuenta?";
            this.btnToggleMode.innerText = "Inicia Sesión";
        } else if (mode === 'recover') {
            this.modalTitle.innerText = "Recuperar Contraseña";
            this.btnSubmit.innerText = "Restablecer";
            this.passInput.placeholder = "Escribe tu NUEVA contraseña";
            this.toggleText.innerText = "¿Recordaste tu clave?";
            this.btnToggleMode.innerText = "Inicia Sesión";
            this.btnRecoverMode.classList.add('d-none'); // Oculta este mismo botón
        }
    }

    clearAlerts() {
        this.alertMessage.classList.add('d-none');
        this.alertMessage.className = 'alert d-none small';
        this.alertMessage.innerText = '';
        this.userInput.value = '';
        this.passInput.value = '';
    }

    showAlert(message, isSuccess) {
        this.alertMessage.classList.remove('d-none');
        this.alertMessage.classList.add(isSuccess ? 'alert-success' : 'alert-danger');
        this.alertMessage.innerText = message;
    }

    async handleSubmit() {
        const username = this.userInput.value.trim();
        const password = this.passInput.value.trim(); 

        if (!username || !password) {
            this.showAlert("Por favor, llena todos los campos.", false);
            return;
        }

        this.btnSubmit.disabled = true;
        this.btnSubmit.innerText = "Procesando...";
        this.alertMessage.classList.add('d-none');

        let endpoint = '';
        let payload = {};

        if (this.currentMode === 'login') {
            endpoint = 'login.php';
            payload = { username, password };
        } else if (this.currentMode === 'register') {
            endpoint = 'registro.php';
            payload = { username, password };
        } else if (this.currentMode === 'recover') {
            endpoint = 'recuperar.php';
            payload = { username, new_password: password }; 
        }

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (data.success) {
                if (this.currentMode === 'login') {
                    this.modal.classList.add('d-none');
                    this.btnOpen.classList.add('d-none');
                    this.btnLogout.classList.remove('d-none');
                    this.greeting.classList.remove('d-none');
                    this.greeting.innerText = `Hola, ${data.username}`; 
                    
                    if (data.rol === 'admin') {
                        this.btnAdminPanel.classList.remove('d-none');
                    }
                } else {
                    this.showAlert(data.mensaje, true);
                    // Regresa al login después de 2 segundos si se registró o recuperó exitosamente
                    setTimeout(() => this.setMode('login'), 2000);
                }
            } else {
                this.showAlert(data.mensaje, false);
            }
        } catch (error) {
            this.showAlert("Error de conexión con el servidor local.", false);
        } finally {
            this.btnSubmit.disabled = false;
            // Restaurar solo el texto del botón, sin borrar las alertas
            if (this.currentMode === 'login') this.btnSubmit.innerText = "Entrar";
            else if (this.currentMode === 'register') this.btnSubmit.innerText = "Registrarme";
            else if (this.currentMode === 'recover') this.btnSubmit.innerText = "Restablecer";
        }
    }

    logout() {
        this.btnOpen.classList.remove('d-none');
        this.btnLogout.classList.add('d-none');
        this.greeting.classList.add('d-none');
        this.btnAdminPanel.classList.add('d-none');
        this.greeting.innerText = "";
        this.clearAlerts();
    }
}

// Clase para gestionar el Catálogo y las Animaciones
class GameStore {
    constructor() {
        this.productGrid = document.getElementById('productGrid');
        this.loadingIndicator = document.getElementById('loadingIndicator');
        this.loadCatalog();
    }

    async loadCatalog() {
        try {
            const products = await this.fetchMockProducts();
            this.renderProducts(products);
            this.setupScrollAnimation();
            this.setupMouseEvents();
        } catch (error) {
            console.error("Error al cargar productos");
        }
    }

    fetchMockProducts() {
        return new Promise(resolve => {
            setTimeout(() => {
                const dbGames = [
                    { title: "Cyberpunk 2077", price: "$899", img: "https://store-images.s-microsoft.com/image/apps.47379.63407868131364914.bcaa868c-407e-42c2-baeb-48a3c9f29b54.89bb995b-b066-4a53-9fe4-0260ce07e894" },
                    { title: "Forza Horizon 6", price: "$1,299", img: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRIBCkNiv39XiKndKlHbXMWfmlXFPAn7VxZq_uVoTzcTY0OfqDvWBDOm8I&s=10" },
                    { title: "Devil May Cry 5", price: "$599", img: "https://store-images.s-microsoft.com/image/apps.19896.67980834390914492.12ed9840-6570-4b28-8c1a-145f4fc183b5.e6468f84-d52b-4c3d-8483-b5c5e2825a9f" },
                    { title: "Resident Evil 4 Remake", price: "$1,100", img: "https://i.3djuegos.com/juegos/18541/resident_evil_4_remake/fotos/ficha/resident_evil_4_remake-5789986.jpg" },
                    { title: "Dark Souls 3", price: "$450", img: "https://store-images.s-microsoft.com/image/apps.24212.71827372323164480.6e97c7d2-899a-404f-8660-d622a7fc9162.daea9ed5-4921-45a6-a188-637bfab7176c" },
                    { title: "Grand Theft Auto lV", price: "$1,870", img: "https://media.vandal.net/i/620x685/6-2026/18/20266181662926_1.jpg" }
                ];
                resolve(dbGames);
            }, 1000); 
        });
    }

    renderProducts(products) {
        this.loadingIndicator.classList.add('d-none');
        let html = '';
        
        products.forEach(game => {
            html += `
                <div class="col-6 col-md-4 col-lg-4 scroll-item">
                    <div class="product-card p-3">
                        <img src="${game.img}" class="img-fluid rounded mb-3 w-100" alt="${game.title}">
                        <h5 class="text-truncate" style="max-width: 100%;">${game.title}</h5>
                        <h4 class="fw-bold">${game.price}</h4>
                        <p class="text-success small fw-bold mb-0">Llega gratis mañana</p>
                    </div>
                </div>
            `;
        });
        
        this.productGrid.innerHTML = html;
    }

    setupScrollAnimation() {
        const items = document.querySelectorAll('.scroll-item');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('scroll-visible');
                }
            });
        }, { threshold: 0.1 }); 

        items.forEach(item => observer.observe(item));
    }

    setupMouseEvents() {
        const cards = document.querySelectorAll('.product-card');
        cards.forEach(card => {
            card.addEventListener('mouseenter', () => card.classList.add('product-card-hover'));
            card.addEventListener('mouseleave', () => card.classList.remove('product-card-hover'));
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new AuthManager();
    new GameStore();
});