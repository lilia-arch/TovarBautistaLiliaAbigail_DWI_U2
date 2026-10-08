
"use strict";

// ============================================
// TENISSTORE - SISTEMA PRINCIPAL
// ============================================

class TenisStoreApp {
    constructor() {
        this.usuario = null;
        this.productos = [];
        this.carrito = [];
        this.totalCarrito = 0;
        this.modoRegistro = false;

        if (document.readyState === "loading") {
            document.addEventListener(
                "DOMContentLoaded",
                () => this.iniciar()
            );
        } else {
            this.iniciar();
        }
    }

    elemento(id) {
        return document.getElementById(id);
    }

    escapar(valor) {
        return String(valor ?? "").replace(
            /[&<>"']/g,
            caracter => ({
                "&": "&amp;",
                "<": "&lt;",
                ">": "&gt;",
                '"': "&quot;",
                "'": "&#39;"
            })[caracter]
        );
    }

    moneda(valor) {
        return new Intl.NumberFormat("es-MX", {
            style: "currency",
            currency: "MXN"
        }).format(Number(valor) || 0);
    }

    mostrar(elemento, visible, display = "") {
        if (!elemento) return;

        elemento.classList.toggle("d-none", !visible);
        elemento.style.display = visible ? display : "none";
    }

    async peticion(url, opciones = {}) {
        let respuesta;

        try {
            respuesta = await fetch(url, {
                credentials: "same-origin",
                cache: "no-store",
                ...opciones
            });
        } catch (error) {
            throw new Error(
                "No se pudo conectar con el servidor."
            );
        }

        let datos;

        try {
            datos = await respuesta.json();
        } catch (error) {
            throw new Error(
                "El servidor no devolvió una respuesta JSON válida."
            );
        }

        if (!respuesta.ok || datos.success === false) {
            throw new Error(
                datos.mensaje || "Ocurrió un error."
            );
        }

        return datos;
    }

    enviarPOST(url, datos) {
        return this.peticion(url, {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(datos)
        });
    }

    async iniciar() {
        this.prepararCarrito();
        this.configurarEventos();
        this.configurarFormulario();

        await this.verificarSesion();
        await this.cargarProductos();
        await this.cargarCarrito();
    }

    // ========================================
    // EVENTOS
    // ========================================

    configurarEventos() {
        const asociar = (id, evento, funcion) => {
            const elemento = this.elemento(id);

            if (elemento) {
                elemento.addEventListener(evento, funcion);
            }
        };

        asociar("btnLoginOpen", "click", () => {
            this.abrirModal();
        });

        asociar("btnCloseModal", "click", () => {
            this.cerrarModal();
        });

        asociar("btnToggleMode", "click", () => {
            this.cambiarModo();
        });

        asociar("btnRecoverMode", "click", () => {
            alert(
                "La recuperación de contraseña todavía no está configurada de forma segura."
            );
        });

        asociar("authForm", "submit", evento => {
            evento.preventDefault();
            this.enviarAutenticacion();
        });

        asociar("btnLogout", "click", () => {
            this.cerrarSesion();
        });

        asociar("btnMisCompras", "click", () => {
            this.mostrarMisCompras();
        });

        asociar("btnVolverCatalogo", "click", () => {
            this.mostrarCatalogo();
        });

        asociar("btnAbrirCarrito", "click", () => {
            this.mostrarCarrito();
        });

        asociar("btnCerrarCarrito", "click", () => {
            this.ocultarCarrito();
        });

        asociar("btnVaciarCarrito", "click", () => {
            this.vaciarCarrito();
        });

        asociar("btnComprarCarrito", "click", () => {
            alert(
                "Todavía falta configurar la compra conjunta del carrito. Puedes utilizar Comprar ahora para registrar compras individuales."
            );
        });
    }

    // ========================================
    // FORMULARIO DE AUTENTICACIÓN
    // ========================================

    configurarFormulario() {
        this.modoRegistro = false;
        this.actualizarModoFormulario();
        this.limpiarAlerta();
    }

    abrirModal() {
        const modal = this.elemento("loginModal");

        this.mostrar(modal, true, "flex");
        this.limpiarAlerta();
    }

    cerrarModal() {
        const modal = this.elemento("loginModal");

        this.mostrar(modal, false);
        this.limpiarAlerta();
    }

    cambiarModo() {
        this.modoRegistro = !this.modoRegistro;
        this.actualizarModoFormulario();
        this.limpiarAlerta();
    }

    actualizarModoFormulario() {
        const titulo = this.elemento("modalTitle");
        const boton = this.elemento("btnSubmitForm");
        const alternar = this.elemento("btnToggleMode");
        const texto = this.elemento("toggleText");
        const email = this.elemento("emailField");
        const password = this.elemento("passwordInput");

        if (titulo) {
            titulo.textContent = this.modoRegistro
                ? "Crear Cuenta"
                : "Iniciar Sesión";
        }

        if (boton) {
            boton.textContent = this.modoRegistro
                ? "Registrarme"
                : "Entrar";
        }

        if (texto) {
            texto.textContent = this.modoRegistro
                ? "¿Ya tienes cuenta?"
                : "¿No tienes cuenta?";
        }

        if (alternar) {
            alternar.textContent = this.modoRegistro
                ? "Inicia Sesión"
                : "Regístrate";
        }

        // El registro actual utiliza usuario y contraseña.
        if (email) {
            this.mostrar(email, false);
        }

        if (password) {
            password.autocomplete = this.modoRegistro
                ? "new-password"
                : "current-password";
        }
    }

    mostrarAlerta(mensaje, tipo = "danger") {
        const alerta = this.elemento("alertMessage");

        if (!alerta) {
            alert(mensaje);
            return;
        }

        alerta.className = `alert alert-${tipo} small`;
        alerta.textContent = mensaje;
        this.mostrar(alerta, true);
    }

    limpiarAlerta() {
        const alerta = this.elemento("alertMessage");

        if (alerta) {
            alerta.textContent = "";
            this.mostrar(alerta, false);
        }
    }

    async enviarAutenticacion() {
        const username = this.elemento("usernameInput")
            ?.value.trim();

        const password = this.elemento("passwordInput")
            ?.value;

        if (!username || !password) {
            this.mostrarAlerta(
                "Completa usuario y contraseña."
            );
            return;
        }

        if (this.modoRegistro) {
            if (!/^[a-zA-Z0-9_]{3,30}$/.test(username)) {
                this.mostrarAlerta(
                    "El usuario debe tener entre 3 y 30 caracteres y utilizar solo letras, números o guion bajo."
                );
                return;
            }

            if (
                new TextEncoder().encode(password).length < 8
            ) {
                this.mostrarAlerta(
                    "La contraseña debe tener al menos 8 caracteres."
                );
                return;
            }
        }

        const esRegistro = this.modoRegistro;

        const url = esRegistro
            ? "registro.php"
            : "login.php";

        const boton = this.elemento("btnSubmitForm");

        try {
            if (boton) boton.disabled = true;

            this.limpiarAlerta();

            const resultado = await this.enviarPOST(url, {
                username,
                password
            });

            if (esRegistro) {
                this.modoRegistro = false;
                this.actualizarModoFormulario();

                const passwordInput = this.elemento(
                    "passwordInput"
                );

                if (passwordInput) {
                    passwordInput.value = "";
                }

                this.mostrarAlerta(
                    resultado.mensaje ||
                    "Cuenta creada correctamente. Ahora inicia sesión.",
                    "success"
                );

                return;
            }

            await this.verificarSesion();

            if (!this.usuario) {
                throw new Error(
                    "El servidor respondió, pero no se pudo confirmar la sesión."
                );
            }

            this.cerrarModal();
            await this.cargarCarrito();

        } catch (error) {
            this.mostrarAlerta(error.message);
        } finally {
            if (boton) boton.disabled = false;
        }
    }

    // ========================================
    // SESIÓN DEL USUARIO
    // ========================================

    async verificarSesion() {
        try {
            const datos = await this.peticion(
                "check_session.php"
            );

            this.usuario = datos.loggedIn
                ? {
                    username: datos.username,
                    rol: datos.rol
                }
                : null;

        } catch (error) {
            console.error(
                "Error al verificar sesión:",
                error
            );

            this.usuario = null;
        }

        this.actualizarInterfaz();
    }

    actualizarInterfaz() {
        const conectado = Boolean(this.usuario);

        this.mostrar(
            this.elemento("btnLoginOpen"),
            !conectado
        );

        this.mostrar(
            this.elemento("btnLogout"),
            conectado
        );

        this.mostrar(
            this.elemento("btnMisCompras"),
            conectado
        );

        this.mostrar(
            this.elemento("btnAdminPanel"),
            conectado && this.usuario?.rol === "admin"
        );

        const saludo = this.elemento("userGreeting");

        if (saludo) {
            saludo.textContent = conectado
                ? `Hola, ${this.usuario.username}`
                : "";

            this.mostrar(saludo, conectado);
        }
    }

    async cerrarSesion() {
        try {
            await this.enviarPOST("logout.php", {});

            this.usuario = null;
            this.actualizarInterfaz();
            this.mostrarCatalogo();
            this.ocultarCarrito();

            alert("Sesión cerrada correctamente.");

        } catch (error) {
            alert(error.message);
        }
    }

    // ========================================
    // CATÁLOGO DE PRODUCTOS
    // ========================================

    async cargarProductos() {
        const contenedor = this.elemento("productGrid");
        const cargando = this.elemento("loadingIndicator");

        this.mostrar(cargando, true, "block");

        try {
            const datos = await this.peticion(
                "productos.php"
            );

            this.productos = Array.isArray(datos.productos)
                ? datos.productos
                : [];

            this.mostrarProductos();

        } catch (error) {
            if (contenedor) {
                contenedor.textContent = error.message;
            }
        } finally {
            this.mostrar(cargando, false);
        }
    }

    mostrarProductos() {
        const contenedor = this.elemento("productGrid");

        if (!contenedor) return;

        contenedor.innerHTML = "";

        if (this.productos.length === 0) {
            contenedor.textContent =
                "No hay productos disponibles.";
            return;
        }

        this.productos.forEach(producto => {
            const tarjeta = document.createElement("div");

            tarjeta.className =
                "col-12 col-sm-6 col-lg-4 mb-4";

            const imagen = producto.imagen
                ? this.escapar(producto.imagen)
                : "";

            const id = Number(producto.id);
            const stock = Number(producto.stock);

            tarjeta.innerHTML = `
                <div class="card product-card h-100">
                    ${
                        imagen
                            ? `<img
                                src="${imagen}"
                                class="card-img-top"
                                alt="${this.escapar(producto.nombre)}"
                            >`
                            : ""
                    }

                    <div class="card-body">
                        <h5 class="card-title">
                            ${this.escapar(producto.nombre)}
                        </h5>

                        <p class="card-text">
                            ${this.escapar(producto.descripcion)}
                        </p>

                        <p>
                            Talla:
                            ${this.escapar(producto.talla)}
                        </p>

                        <p>
                            Existencias: ${stock}
                        </p>

                        <h5>
                            ${this.moneda(producto.precio)}
                        </h5>

                        <button
                            class="btn btn-primary w-100 mb-2"
                            data-accion="agregar"
                            data-id="${id}"
                            ${stock <= 0 ? "disabled" : ""}
                        >
                            Agregar al carrito
                        </button>

                        <button
                            class="btn btn-outline-dark w-100"
                            data-accion="comprar"
                            data-id="${id}"
                            ${stock <= 0 ? "disabled" : ""}
                        >
                            Comprar ahora
                        </button>
                    </div>
                </div>
            `;

            tarjeta.addEventListener("click", evento => {
                const boton = evento.target.closest(
                    "button[data-accion]"
                );

                if (!boton) return;

                const productoId = Number(boton.dataset.id);

                if (boton.dataset.accion === "agregar") {
                    this.agregarAlCarrito(productoId);
                }

                if (boton.dataset.accion === "comprar") {
                    this.comprarProducto(productoId);
                }
            });

            contenedor.appendChild(tarjeta);
        });
    }

    async comprarProducto(productoId) {
        if (!this.usuario) {
            alert("Primero debes iniciar sesión.");
            this.abrirModal();
            return;
        }

        if (!confirm(
            "¿Deseas registrar esta compra? Esta operación no procesa un pago real."
        )) {
            return;
        }

        try {
            const resultado = await this.enviarPOST(
                "comprar.php",
                {
                    producto_id: productoId
                }
            );

            alert(
                resultado.mensaje ||
                "Compra registrada correctamente."
            );

            await this.cargarProductos();
            await this.cargarCarrito();

        } catch (error) {
            alert(error.message);
        }
    }

    // ========================================
    // CREAR INTERFAZ DEL CARRITO
    // ========================================

    prepararCarrito() {
        if (!this.elemento("btnAbrirCarrito")) {
            const boton = document.createElement("button");

            boton.id = "btnAbrirCarrito";
            boton.type = "button";
            boton.className =
                "btn btn-outline-primary position-fixed";

            boton.style.cssText = `
                right: 20px;
                bottom: 20px;
                z-index: 1050;
                border-radius: 30px;
                background: white;
            `;

            boton.textContent = "🛒 Carrito (0)";
            document.body.appendChild(boton);
        }

        if (!this.elemento("panelCarrito")) {
            const panel = document.createElement("div");

            panel.id = "panelCarrito";

            panel.style.cssText = `
                display: none;
                position: fixed;
                right: 15px;
                top: 70px;
                width: min(420px, calc(100vw - 30px));
                max-height: 80vh;
                overflow-y: auto;
                z-index: 1100;
                background: white;
                color: #222;
                padding: 20px;
                border-radius: 12px;
                box-shadow: 0 5px 30px #0004;
            `;

            panel.innerHTML = `
                <div class="d-flex justify-content-between
                            align-items-center mb-3">
                    <h4>Mi carrito</h4>

                    <button
                        id="btnCerrarCarrito"
                        type="button"
                        class="btn btn-sm btn-outline-secondary"
                    >
                        Cerrar
                    </button>
                </div>

                <div id="listaCarrito"></div>

                <hr>

                <h5 id="totalCarrito">
                    Total: $0.00
                </h5>

                <button
                    id="btnVaciarCarrito"
                    type="button"
                    class="btn btn-outline-danger w-100 mb-2"
                >
                    Vaciar carrito
                </button>

                <button
                    id="btnComprarCarrito"
                    type="button"
                    class="btn btn-primary w-100"
                >
                    Finalizar compra
                </button>
            `;

            document.body.appendChild(panel);
        }
    }

    // ========================================
    // FUNCIONES DEL CARRITO
    // ========================================

    async cargarCarrito() {
        try {
            const datos = await this.peticion(
                "carrito.php"
            );

            this.carrito = Array.isArray(datos.productos)
                ? datos.productos
                : [];

            this.totalCarrito =
                Number(datos.total) || 0;

            this.actualizarCarrito();

        } catch (error) {
            console.error(
                "Error al cargar carrito:",
                error
            );
        }
    }

    async agregarAlCarrito(productoId) {
        try {
            const datos = await this.enviarPOST(
                "carrito.php",
                {
                    accion: "agregar",
                    producto_id: productoId,
                    cantidad: 1
                }
            );

            await this.cargarCarrito();

            alert(
                datos.mensaje ||
                "Producto agregado al carrito."
            );

        } catch (error) {
            alert(error.message);
        }
    }

    async actualizarCantidad(productoId, cantidad) {
        try {
            await this.enviarPOST(
                "carrito.php",
                {
                    accion: "actualizar",
                    producto_id: productoId,
                    cantidad
                }
            );

            await this.cargarCarrito();

        } catch (error) {
            alert(error.message);
        }
    }

    async eliminarDelCarrito(productoId) {
        try {
            await this.enviarPOST(
                "carrito.php",
                {
                    accion: "eliminar",
                    producto_id: productoId
                }
            );

            await this.cargarCarrito();

        } catch (error) {
            alert(error.message);
        }
    }

    async vaciarCarrito() {
        if (!confirm("¿Deseas vaciar el carrito?")) {
            return;
        }

        try {
            await this.enviarPOST(
                "carrito.php",
                {
                    accion: "vaciar"
                }
            );

            await this.cargarCarrito();

        } catch (error) {
            alert(error.message);
        }
    }

    actualizarCarrito() {
        const lista = this.elemento("listaCarrito");
        const total = this.elemento("totalCarrito");
        const boton = this.elemento("btnAbrirCarrito");

        const cantidadTotal = this.carrito.reduce(
            (suma, producto) =>
                suma + Number(producto.cantidad),
            0
        );

        if (boton) {
            boton.textContent =
                `🛒 Carrito (${cantidadTotal})`;
        }

        if (total) {
            total.textContent =
                `Total: ${this.moneda(this.totalCarrito)}`;
        }

        if (!lista) return;

        lista.innerHTML = "";

        if (this.carrito.length === 0) {
            lista.textContent = "Tu carrito está vacío.";
            return;
        }

        this.carrito.forEach(producto => {
            const fila = document.createElement("div");

            fila.className = "border-bottom py-3";

            fila.innerHTML = `
                <strong>
                    ${this.escapar(producto.nombre)}
                </strong>

                <p class="mb-1">
                    ${this.moneda(producto.precio)}
                </p>

                <p class="mb-2">
                    Subtotal:
                    ${this.moneda(producto.subtotal)}
                </p>

                <div class="d-flex gap-2 align-items-center">
                    <button
                        type="button"
                        class="btn btn-sm btn-outline-secondary"
                        data-accion="menos"
                    >−</button>

                    <span>
                        ${Number(producto.cantidad)}
                    </span>

                    <button
                        type="button"
                        class="btn btn-sm btn-outline-secondary"
                        data-accion="mas"
                    >+</button>

                    <button
                        type="button"
                        class="btn btn-sm btn-danger ms-auto"
                        data-accion="eliminar"
                    >
                        Eliminar
                    </button>
                </div>
            `;

            fila.addEventListener("click", evento => {
                const boton = evento.target.closest(
                    "button[data-accion]"
                );

                if (!boton) return;

                const accion = boton.dataset.accion;
                const id = Number(producto.id);
                const cantidad = Number(producto.cantidad);

                if (accion === "menos") {
                    this.actualizarCantidad(
                        id,
                        cantidad - 1
                    );
                }

                if (accion === "mas") {
                    this.actualizarCantidad(
                        id,
                        cantidad + 1
                    );
                }

                if (accion === "eliminar") {
                    this.eliminarDelCarrito(id);
                }
            });

            lista.appendChild(fila);
        });
    }

    mostrarCarrito() {
        this.mostrar(
            this.elemento("panelCarrito"),
            true,
            "block"
        );

        this.cargarCarrito();
    }

    ocultarCarrito() {
        this.mostrar(
            this.elemento("panelCarrito"),
            false
        );
    }

    // ========================================
    // HISTORIAL DE COMPRAS
    // ========================================

    async mostrarMisCompras() {
        if (!this.usuario) {
            this.abrirModal();
            return;
        }

        const seccion = this.elemento(
            "misComprasSection"
        );

        const catalogo = this.elemento(
            "productGrid"
        );

        const contenedor = this.elemento(
            "comprasContainer"
        );

        this.mostrar(seccion, true, "block");
        this.mostrar(catalogo, false);

        if (!contenedor) return;

        contenedor.textContent = "Cargando compras...";

        try {
            const datos = await this.peticion(
                "mis_compras.php"
            );

            const compras = Array.isArray(datos.compras)
                ? datos.compras
                : [];

            contenedor.innerHTML = "";

            if (compras.length === 0) {
                contenedor.textContent =
                    "Todavía no tienes compras registradas.";
                return;
            }

            compras.forEach(compra => {
                const tarjeta = document.createElement("div");

                tarjeta.className =
                    "card mb-3 shadow-sm";

                const fecha = compra.fecha
                    ? this.escapar(compra.fecha)
                    : "Fecha no disponible";

                tarjeta.innerHTML = `
                    <div class="card-body">
                        <h5>
                            Compra #${Number(compra.id)}
                        </h5>

                        <p>
                            Fecha: ${fecha}
                        </p>

                        <p>
                            Producto:
                            ${this.escapar(compra.nombre_producto)}
                        </p>

                        <p>
                            Cantidad:
                            ${Number(compra.cantidad)}
                        </p>

                        <h5>
                            Total:
                            ${this.moneda(compra.total)}
                        </h5>
                    </div>
                `;

                contenedor.appendChild(tarjeta);
            });

        } catch (error) {
            contenedor.textContent = error.message;
        }
    }

    mostrarCatalogo() {
        this.mostrar(
            this.elemento("misComprasSection"),
            false
        );

        this.mostrar(
            this.elemento("productGrid"),
            true,
            ""
        );
    }
}

// ============================================
// INICIAR TENISSTORE
// ============================================

const tenisStore = new TenisStoreApp();