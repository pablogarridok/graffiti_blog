// URL base de la API (ajustada a la nueva estructura)
const API_URL = 'api.php';

// Referencias a elementos del DOM
const formCreate = document.getElementById('formCreate');
const tbody = document.getElementById('tbody');
const msgDiv = document.getElementById('msg');
const filaVacia = document.getElementById('fila-estado-vacio');
const indicadorCargando = document.getElementById('indicador-cargando');
const botonAgregar = document.getElementById('boton-agregar-usuario');

// Variables para modo edición
let modoEdicion = false;
let indiceEditando = null;

/**
 * Muestra un mensaje temporal en la zona de mensajes
 */
function mostrarMensaje(texto, tipo = 'info') {
    msgDiv.textContent = texto;
    msgDiv.className = `mensajes-estado ${tipo}`;
    msgDiv.style.display = 'block';

    setTimeout(() => {
        msgDiv.style.display = 'none';
    }, 5000);
}

/**
 * Muestra u oculta el indicador de carga
 */
function toggleCargando(mostrar) {
    indicadorCargando.hidden = !mostrar;
    botonAgregar.disabled = mostrar;
}

/**
 * Carga y muestra la lista de usuarios
 */
async function cargarUsuarios() {
    try {
        toggleCargando(true);

        const response = await fetch(`${API_URL}?action=list`);
        const data = await response.json();

        if (!data.ok) {
            throw new Error(data.error || 'Error al cargar usuarios');
        }

        renderizarTabla(data.data);

    } catch (error) {
        console.error(error);
        mostrarMensaje('Error al cargar usuarios', 'error');
    } finally {
        toggleCargando(false);
    }
}

/**
 * Renderiza la tabla de usuarios
 */
function renderizarTabla(usuarios) {
    tbody.innerHTML = '';

    if (!usuarios || usuarios.length === 0) {
        filaVacia.hidden = false;
        tbody.appendChild(filaVacia);
        return;
    }

    filaVacia.hidden = true;

    usuarios.forEach((usuario, index) => {
        const tr = document.createElement('tr');

        const tdNumero = document.createElement('td');
        tdNumero.textContent = index + 1;

        const tdNombre = document.createElement('td');
        tdNombre.textContent = usuario.nombre || 'Sin nombre';

        const tdEmail = document.createElement('td');
        tdEmail.textContent = usuario.email || 'Sin email';

        const tdAccion = document.createElement('td');

        // Botón Editar
        const btnEditar = document.createElement('button');
        btnEditar.className = 'btn-editar';
        btnEditar.textContent = 'Editar';
        btnEditar.addEventListener('click', () => editarUsuario(index, usuario));

        // Botón Eliminar
        const btnEliminar = document.createElement('button');
        btnEliminar.className = 'btn-eliminar';
        btnEliminar.textContent = 'Eliminar';
        btnEliminar.addEventListener('click', () => eliminarUsuario(index, usuario.nombre));

        tdAccion.appendChild(btnEditar);
        tdAccion.appendChild(btnEliminar);

        tr.appendChild(tdNumero);
        tr.appendChild(tdNombre);
        tr.appendChild(tdEmail);
        tr.appendChild(tdAccion);

        tbody.appendChild(tr);
    });
}

/**
 * Crear o actualizar usuario
 */
async function crearUsuario(evento) {
    evento.preventDefault();

    const formData = new FormData(formCreate);
    const datos = {
        nombre: formData.get('nombre').trim(),
        email: formData.get('email').trim(),
        password: formData.get('password').trim(),
        rol: formData.get('role')
    };

    if (!datos.nombre || !datos.email || (!modoEdicion && !datos.password)) {
        mostrarMensaje('Completa todos los campos', 'error');
        return;
    }

    try {
        toggleCargando(true);

        const accion = modoEdicion ? 'update' : 'create';

        const response = await fetch(`${API_URL}?action=${accion}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ...datos, index: indiceEditando })
        });

        const data = await response.json();

        if (!data.ok) throw new Error(data.error);

        mostrarMensaje(
            modoEdicion ? 'Usuario actualizado correctamente' : 'Usuario creado correctamente',
            'ok'
        );

        resetFormulario();
        await cargarUsuarios();

    } catch (error) {
        console.error(error);
        mostrarMensaje(error.message, 'error');
    } finally {
        toggleCargando(false);
    }
}

/**
 * Eliminar usuario
 */
async function eliminarUsuario(index, nombre) {
    if (!confirm(`¿Eliminar a "${nombre}"?`)) return;

    try {
        toggleCargando(true);

        const response = await fetch(`${API_URL}?action=delete`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ index })
        });

        const data = await response.json();

        if (!data.ok) throw new Error(data.error);

        mostrarMensaje('Usuario eliminado', 'ok');
        await cargarUsuarios();

    } catch (error) {
        console.error(error);
        mostrarMensaje(error.message, 'error');
    } finally {
        toggleCargando(false);
    }
}

/**
 * Pone el formulario en modo edición
 */
function editarUsuario(index, usuario) {
    modoEdicion = true;
    indiceEditando = index;

    formCreate.nombre.value = usuario.nombre;
    formCreate.email.value = usuario.email;
    formCreate.password.value = '';
    formCreate.role.value = usuario.rol;

    botonAgregar.textContent = 'Guardar cambios';
}

/**
 * Resetea el formulario y sale del modo edición
 */
function resetFormulario() {
    modoEdicion = false;
    indiceEditando = null;
    formCreate.reset();
    botonAgregar.textContent = 'Agregar usuario';
}

// Eventos
formCreate.addEventListener('submit', crearUsuario);
document.addEventListener('DOMContentLoaded', cargarUsuarios);
