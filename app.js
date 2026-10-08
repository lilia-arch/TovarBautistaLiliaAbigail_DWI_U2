// ======================================================
// AUTH MANAGER
// Registro, Login, Sesión y Recuperación
// ======================================================

class AuthManager {

    constructor() {

        this.modal = document.getElementById('loginModal');
        this.btnOpen = document.getElementById('btnLoginOpen');
        this.btnClose = document.getElementById('btnCloseModal');
        this.btnSubmit = document.getElementById('btnSubmitForm');
        this.btnLogout = document.getElementById('btnLogout');

        this.btnToggleMode =
            document.getElementById('btnToggleMode');

        this.btnRecoverMode =
            document.getElementById('btnRecoverMode');

        this.btnAdminPanel =
            document.getElementById('btnAdminPanel');

        this.btnMisCompras =
            document.getElementById('btnMisCompras');

        this.userInput =
            document.getElementById('usernameInput');

        this.passInput =
            document.getElementById('passwordInput');

        this.greeting =
            document.getElementById('userGreeting');

        this.modalTitle =
            document.getElementById('modalTitle');

        this.toggleText =
            document.getElementById('toggleText');

        this.alertMessage =
            document.getElementById('alertMessage');

        this.currentMode = 'login';

        this.initEvents();
    }


    // ==================================================
    // EVENTOS
    // ==================================================

    initEvents() {

        // Abrir login
        if (this.btnOpen) {

            this.btnOpen.addEventListener(
                'click',
                () => {

                    this.setMode('login');

                    this.modal.classList.remove(
                        'd-none'
                    );
                }
            );
        }


        // Cerrar modal
        if (this.btnClose) {

            this.btnClose.addEventListener(
                'click',
                () => {

                    this.modal.classList.add(
                        'd-none'
                    );
                }
            );
        }


        // Cambiar login / registro
        if (this.btnToggleMode) {

            this.btnToggleMode.addEventListener(
                'click',
                () => {

                    if (
                        this.currentMode === 'login' ||
                        this.currentMode === 'recover'
                    ) {

                        this.setMode('register');

                    } else {

                        this.setMode('login');
                    }
                }
            );
        }


        // Recuperar contraseña
        if (this.btnRecoverMode) {

            this.btnRecoverMode.addEventListener(
                'click',
                () => {

                    this.setMode('recover');
                }
            );
        }


        // Enviar formulario
        if (this.btnSubmit) {

            this.btnSubmit.addEventListener(
                'click',
                () => {

                    this.handleSubmit();
                }
            );
        }


        // Cerrar sesión
        if (this.btnLogout) {

            this.btnLogout.addEventListener(
                'click',
                () => {

                    this.logout();
                }
            );
        }
    }


    // ==================================================
    // CAMBIAR MODO
    // ==================================================

    setMode(mode) {

        this.currentMode = mode;

        this.clearAlerts();

        if (this.btnRecoverMode) {

            this.btnRecoverMode.classList.remove(
                'd-none'
            );
        }


        // LOGIN
        if (mode === 'login') {

            this.modalTitle.innerText =
                'Iniciar Sesión';

            this.btnSubmit.innerText =
                'Entrar';

            this.passInput.placeholder =
                'Contraseña';

            this.toggleText.innerText =
                '¿No tienes cuenta?';

            this.btnToggleMode.innerText =
                'Regístrate';
        }


        // REGISTRO
        else if (mode === 'register') {

            this.modalTitle.innerText =
                'Crear Cuenta';

            this.btnSubmit.innerText =
                'Registrarme';

            this.passInput.placeholder =
                'Crea una contraseña';

            this.toggleText.innerText =
                '¿Ya tienes cuenta?';

            this.btnToggleMode.innerText =
                'Inicia Sesión';
        }


        // RECUPERAR
        else if (mode === 'recover') {

            this.modalTitle.innerText =
                'Recuperar Contraseña';

            this.btnSubmit.innerText =
                'Restablecer';

            this.passInput.placeholder =
                'Escribe tu NUEVA contraseña';

            this.toggleText.innerText =
                '¿Recordaste tu clave?';

            this.btnToggleMode.innerText =
                'Inicia Sesión';

            this.btnRecoverMode.classList.add(
                'd-none'
            );
        }
    }


    // ==================================================
    // LIMPIAR ALERTAS
    // ==================================================

    clearAlerts() {

        if (!this.alertMessage) {
            return;
        }

        this.alertMessage.className =
            'alert d-none small';

        this.alertMessage.innerText = '';

        if (this.userInput) {
            this.userInput.value = '';
        }

        if (this.passInput) {
            this.passInput.value = '';
        }
    }


    // ==================================================
    // MOSTRAR ALERTA
    // ==================================================

    showAlert(message, isSuccess) {

        this.alertMessage.className =
            'alert small ' +
            (isSuccess
                ? 'alert-success'
                : 'alert-danger');

        this.alertMessage.classList.remove(
            'd-none'
        );

        this.alertMessage.innerText =
            message;
    }


    // ==================================================
    // LOGIN / REGISTRO / RECUPERACIÓN
    // ==================================================

    async handleSubmit() {

        const username =
            this.userInput.value.trim();

        const password =
            this.passInput.value.trim();


        // Validar campos
        if (!username || !password) {

            this.showAlert(
                'Por favor, llena todos los campos.',
                false
            );

            return;
        }


        this.btnSubmit.disabled = true;

        this.btnSubmit.innerText =
            'Procesando...';


        let endpoint = '';

        let payload = {};


        // LOGIN
        if (this.currentMode === 'login') {

            endpoint = 'login.php';

            payload = {
                username: username,
                password: password
            };
        }


        // REGISTRO
        else if (
            this.currentMode === 'register'
        ) {

            endpoint = 'registro.php';

            payload = {
                username: username,
                password: password
            };
        }


        // RECUPERACIÓN
        else if (
            this.currentMode === 'recover'
        ) {

            endpoint = 'recuperar.php';

            payload = {
                username: username,
                new_password: password
            };
        }


        try {

            const response =
                await fetch(
                    endpoint,
                    {
                        method: 'POST',

                        headers: {
                            'Content-Type':
                                'application/json'
                        },

                        body:
                            JSON.stringify(payload)
                    }
                );


            if (!response.ok) {

                throw new Error(
                    'Error HTTP: ' +
                    response.status
                );
            }


            const data =
                await response.json();


            console.log(
                'Respuesta de ' +
                endpoint +
                ':',
                data
            );


            // ==========================================
            // OPERACIÓN CORRECTA
            // ==========================================

            if (data.success) {


                // ======================================
                // LOGIN CORRECTO
                // ======================================

                if (
                    this.currentMode === 'login'
                ) {

                    // Cerrar modal
                    this.modal.classList.add(
                        'd-none'
                    );


                    // Ocultar botón entrar
                    this.btnOpen.classList.add(
                        'd-none'
                    );


                    // Mostrar botón salir
                    this.btnLogout.classList.remove(
                        'd-none'
                    );


                    // Mostrar saludo
                    this.greeting.classList.remove(
                        'd-none'
                    );


                    this.greeting.innerText =
                        `Hola, ${data.username}`;


                    // ==================================
                    // MOSTRAR MIS COMPRAS
                    // ==================================

                    if (this.btnMisCompras) {

                        this.btnMisCompras.classList.remove(
                            'd-none'
                        );
                    }


                    const comprasSection =
                        document.getElementById(
                            'misComprasSection'
                        );


                    if (comprasSection) {

                        comprasSection.classList.remove(
                            'd-none'
                        );
                    }


                    // ==================================
                    // PANEL ADMIN
                    // ==================================

                    if (
                        data.rol === 'admin'
                    ) {

                        this.btnAdminPanel.classList.remove(
                            'd-none'
                        );

                    } else {

                        this.btnAdminPanel.classList.add(
                            'd-none'
                        );
                    }


                    // ==================================
                    // CARGAR COMPRAS
                    // ==================================

                    cargarMisCompras();
                }


                // ======================================
                // REGISTRO / RECUPERACIÓN
                // ======================================

                else {

                    this.showAlert(
                        data.mensaje ||
                        'Operación realizada correctamente.',
                        true
                    );


                    setTimeout(
                        () => {

                            this.setMode(
                                'login'
                            );

                        },
                        2000
                    );
                }


            }

            // ==========================================
            // ERROR DE PHP
            // ==========================================

            else {

                this.showAlert(
                    data.mensaje ||
                    'Ocurrió un error.',
                    false
                );
            }


        } catch (error) {

            console.error(
                'Error de conexión:',
                error
            );


            this.showAlert(
                'Error de conexión con el servidor local.',
                false
            );


        } finally {

            this.btnSubmit.disabled =
                false;


            if (
                this.currentMode === 'login'
            ) {

                this.btnSubmit.innerText =
                    'Entrar';

            }

            else if (
                this.currentMode === 'register'
            ) {

                this.btnSubmit.innerText =
                    'Registrarme';

            }

            else if (
                this.currentMode === 'recover'
            ) {

                this.btnSubmit.innerText =
                    'Restablecer';
            }
        }
    }


    // ==================================================
    // CERRAR SESIÓN
    // ==================================================

    async logout() {

        try {

            // Destruir sesión PHP
            await fetch(
                'logout.php',
                {
                    method: 'POST'
                }
            );

        } catch (error) {

            console.error(
                'Error al cerrar sesión:',
                error
            );
        }


        // Mostrar botón login
        this.btnOpen.classList.remove(
            'd-none'
        );


        // Ocultar botón salir
        this.btnLogout.classList.add(
            'd-none'
        );


        // Ocultar saludo
        this.greeting.classList.add(
            'd-none'
        );


        // Ocultar panel admin
        this.btnAdminPanel.classList.add(
            'd-none'
        );


        // Ocultar botón mis compras
        if (this.btnMisCompras) {

            this.btnMisCompras.classList.add(
                'd-none'
            );
        }


        // Ocultar sección mis compras
        const comprasSection =
            document.getElementById(
                'misComprasSection'
            );


        if (comprasSection) {

            comprasSection.classList.add(
                'd-none'
            );
        }


        // Limpiar saludo
        this.greeting.innerText =
            '';


        // Limpiar alertas
        this.clearAlerts();


        // Limpiar compras
        const comprasContainer =
            document.getElementById(
                'comprasContainer'
            );


        if (comprasContainer) {

            comprasContainer.innerHTML = `
                <div class="alert alert-info">
                    🛍️ Todavía no has realizado ninguna compra.
                </div>
            `;
        }


        console.log(
            'Sesión cerrada correctamente.'
        );
    }
}


// ======================================================
// TENIS STORE
// Catálogo conectado a MySQL
// ======================================================

class TenisStore {

    constructor() {

        this.productGrid =
            document.getElementById(
                'productGrid'
            );

        this.loadingIndicator =
            document.getElementById(
                'loadingIndicator'
            );

        this.loadCatalog();
    }


    // ==================================================
    // CARGAR CATÁLOGO
    // ==================================================

    async loadCatalog() {

        try {

            console.log(
                'Conectando con productos.php...'
            );


            const response =
                await fetch(
                    'productos.php',
                    {
                        method: 'GET',
                        cache: 'no-cache'
                    }
                );


            if (!response.ok) {

                throw new Error(
                    'Error HTTP: ' +
                    response.status
                );
            }


            const data =
                await response.json();


            console.log(
                'Productos recibidos:',
                data
            );


            if (!data.success) {

                throw new Error(
                    data.mensaje ||
                    'No se pudieron obtener los productos.'
                );
            }


            const productos =
                data.productos;


            if (!Array.isArray(productos)) {

                throw new Error(
                    'El formato de productos.php es incorrecto.'
                );
            }


            this.renderProducts(
                productos
            );


            this.setupScrollAnimation();

            this.setupMouseEvents();


        } catch (error) {

            console.error(
                'Error al cargar productos:',
                error
            );


            this.loadingIndicator.innerHTML = `

                <div class="alert alert-danger">

                    <strong>
                        Error al cargar el catálogo
                    </strong>

                    <br><br>

                    ${error.message}

                    <br><br>

                    Verifica que:

                    <ul class="text-start">

                        <li>
                            Apache esté iniciado.
                        </li>

                        <li>
                            MySQL esté iniciado.
                        </li>

                        <li>
                            Exista productos.php.
                        </li>

                        <li>
                            La base de datos sea tenisstore.
                        </li>

                    </ul>

                </div>

            `;
        }
    }


    // ==================================================
    // MOSTRAR PRODUCTOS
    // ==================================================

    renderProducts(productos) {

        this.loadingIndicator.classList.add(
            'd-none'
        );


        if (productos.length === 0) {

            this.productGrid.innerHTML = `

                <div class="col-12">

                    <div class="alert alert-info">

                        No hay tenis registrados
                        en la base de datos.

                    </div>

                </div>

            `;

            return;
        }


        let html = '';


        productos.forEach(
            producto => {

                const id =
                    producto.id;


                const nombre =
                    producto.nombre ||
                    'Tenis sin nombre';


                const descripcion =
                    producto.descripcion ||
                    'Sin descripción';


                const precio =
                    parseFloat(
                        producto.precio
                    ) || 0;


                const stock =
                    parseInt(
                        producto.stock
                    ) || 0;


                const talla =
                    producto.talla ||
                    'No disponible';


                const imagen =
                    producto.imagen ||
                    'https://via.placeholder.com/800x500?text=Sin+imagen';


                html += `

                    <div
                        class="col-12 col-sm-6 col-lg-4 scroll-item"
                    >

                        <div
                            class="product-card p-3 h-100"
                        >

                            <img
                                src="${imagen}"
                                class="img-fluid rounded mb-3 w-100"
                                alt="${nombre}"
                                style="
                                    height:250px;
                                    object-fit:cover;
                                "
                                onerror="
                                    this.onerror=null;
                                    this.src='https://via.placeholder.com/800x500?text=Sin+imagen';
                                "
                            >


                            <h5 class="fw-bold">
                                ${nombre}
                            </h5>


                            <p class="text-muted">
                                ${descripcion}
                            </p>


                            <h4
                                class="fw-bold text-primary"
                            >

                                $${precio.toLocaleString(
                                    'es-MX',
                                    {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    }
                                )}

                            </h4>


                            <p class="mb-1">

                                <strong>
                                    Talla:
                                </strong>

                                ${talla}

                            </p>


                            <p
                                class="${
                                    stock > 0
                                        ? 'text-success'
                                        : 'text-danger'
                                } fw-bold"
                            >

                                ${
                                    stock > 0
                                        ? 'Stock disponible: ' + stock
                                        : 'Agotado'
                                }

                            </p>


                            <button
                                class="btn btn-primary w-100"
                                onclick="seleccionarProducto(${id})"
                                ${
                                    stock <= 0
                                        ? 'disabled'
                                        : ''
                                }
                            >

                                ${
                                    stock > 0
                                        ? 'Comprar'
                                        : 'Agotado'
                                }

                            </button>

                        </div>

                    </div>

                `;
            }
        );


        this.productGrid.innerHTML =
            html;
    }


    // ==================================================
    // ANIMACIÓN AL HACER SCROLL
    // ==================================================

    setupScrollAnimation() {

        const items =
            document.querySelectorAll(
                '.scroll-item'
            );


        if (
            !(
                'IntersectionObserver'
                in window
            )
        ) {

            items.forEach(
                item => {

                    item.classList.add(
                        'scroll-visible'
                    );
                }
            );

            return;
        }


        const observer =
            new IntersectionObserver(
                entries => {

                    entries.forEach(
                        entry => {

                            if (
                                entry.isIntersecting
                            ) {

                                entry.target.classList.add(
                                    'scroll-visible'
                                );
                            }
                        }
                    );

                },
                {
                    threshold: 0.1
                }
            );


        items.forEach(
            item => {

                observer.observe(
                    item
                );
            }
        );
    }


    // ==================================================
    // EFECTOS DEL MOUSE
    // ==================================================

    setupMouseEvents() {

        const cards =
            document.querySelectorAll(
                '.product-card'
            );


        cards.forEach(
            card => {

                card.addEventListener(
                    'mouseenter',
                    () => {

                        card.classList.add(
                            'product-card-hover'
                        );
                    }
                );


                card.addEventListener(
                    'mouseleave',
                    () => {

                        card.classList.remove(
                            'product-card-hover'
                        );
                    }
                );
            }
        );
    }
}


// ======================================================
// COMPRAR PRODUCTO
// ======================================================

async function seleccionarProducto(id) {


    // ==================================================
    // VERIFICAR SI ESTÁ LOGUEADO
    // ==================================================

    const btnLogout =
        document.getElementById(
            'btnLogout'
        );


    const usuarioLogueado =
        btnLogout &&
        !btnLogout.classList.contains(
            'd-none'
        );


    if (!usuarioLogueado) {

        alert(
            'Debes iniciar sesión para realizar una compra.'
        );


        const btnLoginOpen =
            document.getElementById(
                'btnLoginOpen'
            );


        if (btnLoginOpen) {

            btnLoginOpen.click();
        }


        return;
    }


    // ==================================================
    // CONFIRMAR COMPRA
    // ==================================================

    const confirmar =
        confirm(
            '¿Deseas comprar este tenis?'
        );


    if (!confirmar) {

        return;
    }


    try {

        const response =
            await fetch(
                'comprar.php',
                {
                    method: 'POST',

                    headers: {
                        'Content-Type':
                            'application/json'
                    },

                    body:
                        JSON.stringify({
                            producto_id: id
                        })
                }
            );


        const data =
            await response.json();


        console.log(
            'Respuesta de comprar.php:',
            data
        );


        // ==================================================
        // COMPRA CORRECTA
        // ==================================================

        if (data.success) {

            alert(
                '¡Compra realizada correctamente!\n\n' +

                'Producto: ' +
                data.producto +

                '\nPrecio: $' +
                parseFloat(
                    data.precio
                ).toFixed(2) +

                '\nCantidad: ' +
                data.cantidad +

                '\nTotal: $' +
                parseFloat(
                    data.total
                ).toFixed(2)
            );


            // Actualizar catálogo
            const tienda =
                new TenisStore();


            // Actualizar compras
            cargarMisCompras();


        } else {

            alert(
                'No se pudo realizar la compra:\n\n' +
                data.mensaje
            );
        }


    } catch (error) {

        console.error(
            'Error al comprar:',
            error
        );


        alert(
            'Error de conexión con el servidor.'
        );
    }
}


// ======================================================
// MOSTRAR MIS COMPRAS
// ======================================================

async function cargarMisCompras() {

    const container =
        document.getElementById(
            'comprasContainer'
        );


    const section =
        document.getElementById(
            'misComprasSection'
        );


    if (!container) {

        return;
    }


    // ==================================================
    // VERIFICAR SESIÓN VISUAL
    // ==================================================

    const btnLogout =
        document.getElementById(
            'btnLogout'
        );


    const usuarioLogueado =
        btnLogout &&
        !btnLogout.classList.contains(
            'd-none'
        );


    if (!usuarioLogueado) {

        if (section) {

            section.classList.add(
                'd-none'
            );
        }

        return;
    }


    // ==================================================
    // MOSTRAR SECCIÓN
    // ==================================================

    if (section) {

        section.classList.remove(
            'd-none'
        );
    }


    container.innerHTML = `

        <div class="text-center py-3">

            <div
                class="spinner-border text-primary"
            ></div>

            <p class="mt-2">
                Cargando compras...
            </p>

        </div>

    `;


    try {

        const response =
            await fetch(
                'mis_compras.php',
                {
                    method: 'GET',
                    cache: 'no-cache'
                }
            );


        // ==================================================
        // NO AUTORIZADO
        // ==================================================

        if (response.status === 401) {

            container.innerHTML = `

                <div class="alert alert-warning">

                    Debes iniciar sesión
                    para ver tus compras.

                </div>

            `;

            return;
        }


        if (!response.ok) {

            throw new Error(
                'Error HTTP: ' +
                response.status
            );
        }


        const data =
            await response.json();


        console.log(
            'Compras recibidas:',
            data
        );


        if (!data.success) {

            throw new Error(
                data.mensaje ||
                'No se pudieron obtener las compras.'
            );
        }


        const compras =
            data.compras;


        // ==================================================
        // SIN COMPRAS
        // ==================================================

        if (
            !Array.isArray(compras) ||
            compras.length === 0
        ) {

            container.innerHTML = `

                <div class="alert alert-info">

                    🛍️ Todavía no has realizado
                    ninguna compra.

                </div>

            `;

            return;
        }


        // ==================================================
        // MOSTRAR COMPRAS
        // ==================================================

        let html = '';


        compras.forEach(
            compra => {

                const precio =
                    parseFloat(
                        compra.precio
                    ) || 0;


                const cantidad =
                    parseInt(
                        compra.cantidad
                    ) || 1;


                const total =
                    parseFloat(
                        compra.total
                    ) || 0;


                html += `

                    <div
                        class="card shadow-sm mb-3"
                    >

                        <div class="card-body">

                            <div
                                class="row align-items-center"
                            >

                                <div
                                    class="col-md-6"
                                >

                                    <h5
                                        class="fw-bold"
                                    >

                                        👟
                                        ${compra.nombre_producto}

                                    </h5>


                                    <p
                                        class="text-muted mb-1"
                                    >

                                        Compra #${compra.id}

                                    </p>


                                    <small
                                        class="text-muted"
                                    >

                                        Fecha:
                                        ${compra.fecha}

                                    </small>

                                </div>


                                <div
                                    class="col-md-2"
                                >

                                    <strong>
                                        Precio
                                    </strong>

                                    <br>

                                    $${precio.toFixed(2)}

                                </div>


                                <div
                                    class="col-md-2"
                                >

                                    <strong>
                                        Cantidad
                                    </strong>

                                    <br>

                                    ${cantidad}

                                </div>


                                <div
                                    class="col-md-2"
                                >

                                    <strong>
                                        Total
                                    </strong>

                                    <br>

                                    <span
                                        class="text-success fw-bold"
                                    >

                                        $${total.toFixed(2)}

                                    </span>

                                </div>

                            </div>

                        </div>

                    </div>

                `;
            }
        );


        container.innerHTML =
            html;


    } catch (error) {

        console.error(
            'Error al cargar compras:',
            error
        );


        container.innerHTML = `

            <div class="alert alert-danger">

                <strong>
                    No se pudieron cargar tus compras.
                </strong>

                <br><br>

                ${error.message}

            </div>

        `;
    }
}


// ======================================================
// MOSTRAR SECCIÓN MIS COMPRAS
// ======================================================

function mostrarMisCompras() {

    const section =
        document.getElementById(
            'misComprasSection'
        );


    if (!section) {

        return;
    }


    // Verificar que esté logueado
    const btnLogout =
        document.getElementById(
            'btnLogout'
        );


    const usuarioLogueado =
        btnLogout &&
        !btnLogout.classList.contains(
            'd-none'
        );


    if (!usuarioLogueado) {

        alert(
            'Debes iniciar sesión para ver tus compras.'
        );

        return;
    }


    section.classList.remove(
        'd-none'
    );


    section.scrollIntoView({
        behavior: 'smooth'
    });


    cargarMisCompras();
}


// ======================================================
// INICIAR APLICACIÓN
// ======================================================

document.addEventListener(
    'DOMContentLoaded',
    function () {

        console.log(
            'TenisStore iniciado.'
        );


        // Iniciar autenticación
        new AuthManager();


        // Iniciar catálogo
        new TenisStore();


        // NO cargar compras aquí.
        // Solo se cargan después del login.
    }
);