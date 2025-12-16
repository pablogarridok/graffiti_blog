const URL_API_SERVIDOR = '/public/api.php';
const nodoCuerpoTablaUsuarios = document.getElementById('tbody');
const nodoFilaEstadoVacio = document.getElementById('fila-estado-vacio');
const formularioAltaUsuario = document.getElementById('formCreate');
const nodoZonaMensajesEstado = document.getElementById('msg');
const nodoBotonAgregarUsuario = document.getElementById('boton-agregar-usuario');
const nodoIndicadorCargando = document.getElementById('indicador-cargando');

let usuariosGlobales = [];

function mostrarMensajeDeEstado(tipoEstado, textoMensaje) {
  nodoZonaMensajesEstado.className = tipoEstado;
  nodoZonaMensajesEstado.textContent = textoMensaje;
  if (tipoEstado !== '') setTimeout(() => {
    nodoZonaMensajesEstado.className = '';
    nodoZonaMensajesEstado.textContent = '';
  }, 2000);
}

function activarEstadoCargando() {
  if (nodoBotonAgregarUsuario) nodoBotonAgregarUsuario.disabled = true;
  if (nodoIndicadorCargando) nodoIndicadorCargando.hidden = false;
}
function desactivarEstadoCargando() {
  if (nodoBotonAgregarUsuario) nodoBotonAgregarUsuario.disabled = false;
  if (nodoIndicadorCargando) nodoIndicadorCargando.hidden = true;
}

function convertirATextoSeguro(entrada) {
  return String(entrada).replaceAll('&', '&amp;').replaceAll('<', '&lt;')
                        .replaceAll('>', '&gt;').replaceAll('"', '&quot;')
                        .replaceAll("'", '&#39;');
}

function renderizarTablaDeUsuarios(arrayUsuarios) {
  nodoCuerpoTablaUsuarios.innerHTML = '';
  if (Array.isArray(arrayUsuarios) && arrayUsuarios.length > 0) {
    if (nodoFilaEstadoVacio) nodoFilaEstadoVacio.hidden = true;
  } else {
    if (nodoFilaEstadoVacio) nodoFilaEstadoVacio.hidden = false;
    return;
  }

  arrayUsuarios.forEach((usuario, idx) => {
    const nodoFila = document.createElement('tr');
    nodoFila.innerHTML = `
      <td>${idx + 1}</td>
      <td>${convertirATextoSeguro(usuario?.nombre ?? '')}</td>
      <td>${convertirATextoSeguro(usuario?.email ?? '')}</td>
      <td>
        <button type="button" data-posicion="${idx}" class="btn-eliminar">Eliminar</button>
        <button type="button" data-posicion="${idx}" class="btn-editar">Editar</button>
      </td>
    `;
    nodoCuerpoTablaUsuarios.appendChild(nodoFila);
  });
}

async function obtenerYMostrarListadoDeUsuarios() {
  try {
    const respuestaHttp = await fetch(`${URL_API_SERVIDOR}?action=list`);
    const cuerpoJson = await respuestaHttp.json();
    if (!cuerpoJson.ok) throw new Error(cuerpoJson.error || 'No fue posible obtener el listado.');
    usuariosGlobales = cuerpoJson.data;
    renderizarTablaDeUsuarios(cuerpoJson.data);
  } catch (error) {
    mostrarMensajeDeEstado('error', error.message);
  }
}

formularioAltaUsuario?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const datos = new FormData(formularioAltaUsuario);
  const nuevoUsuario = {
    nombre: String(datos.get('nombre') || '').trim(),
    email: String(datos.get('email') || '').trim(),
    password: String(datos.get('password') || '').trim(),
    role: String(datos.get('role') || '').trim(),
  };

  if (!nuevoUsuario.nombre || !nuevoUsuario.email || !nuevoUsuario.role) {
    mostrarMensajeDeEstado('error', 'Nombre, email y rol son obligatorios.');
    return;
  }

  const editIndex = nodoBotonAgregarUsuario.dataset.editIndex;

  if (editIndex !== undefined) {
    // Actualizar usuario
    try {
      activarEstadoCargando();
      const respuestaHttp = await fetch(`${URL_API_SERVIDOR}?action=update`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ index: parseInt(editIndex, 10), ...nuevoUsuario }),
      });
      const cuerpoJson = await respuestaHttp.json();
      if (!cuerpoJson.ok) throw new Error(cuerpoJson.error || 'No fue posible actualizar el usuario.');
      renderizarTablaDeUsuarios(cuerpoJson.data);
      formularioAltaUsuario.reset();
      nodoBotonAgregarUsuario.textContent = 'Agregar usuario';
      delete nodoBotonAgregarUsuario.dataset.editIndex;
      mostrarMensajeDeEstado('ok', 'Usuario actualizado correctamente.');
    } catch (error) {
      mostrarMensajeDeEstado('error', error.message);
    } finally { desactivarEstadoCargando(); }
    return;
  }

  // Crear usuario
  if (!nuevoUsuario.password) {
    mostrarMensajeDeEstado('error', 'La contraseña es obligatoria.');
    return;
  }

  try {
    activarEstadoCargando();
    const respuestaHttp = await fetch(`${URL_API_SERVIDOR}?action=create`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(nuevoUsuario),
    });
    const cuerpoJson = await respuestaHttp.json();
    if (!cuerpoJson.ok) throw new Error(cuerpoJson.error || 'No fue posible crear el usuario.');
    renderizarTablaDeUsuarios(cuerpoJson.data);
    formularioAltaUsuario.reset();
    mostrarMensajeDeEstado('ok', 'Usuario agregado correctamente.');
  } catch (error) {
    mostrarMensajeDeEstado('error', error.message);
  } finally { desactivarEstadoCargando(); }
});

nodoCuerpoTablaUsuarios?.addEventListener('click', async (e) => {
  // Eliminar
  const btnEliminar = e.target.closest('button.btn-eliminar');
  if (btnEliminar) {
    const idx = parseInt(btnEliminar.dataset.posicion, 10);
    if (!Number.isInteger(idx)) return;
    if (!window.confirm('¿Deseas eliminar este usuario?')) return;
    try {
      const respuestaHttp = await fetch(`${URL_API_SERVIDOR}?action=delete`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ index: idx }),
      });
      const cuerpoJson = await respuestaHttp.json();
      if (!cuerpoJson.ok) throw new Error(cuerpoJson.error || 'No fue posible eliminar el usuario.');
      renderizarTablaDeUsuarios(cuerpoJson.data);
      mostrarMensajeDeEstado('ok', 'Usuario eliminado correctamente.');
    } catch (error) {
      mostrarMensajeDeEstado('error', error.message);
    }
    return;
  }

  // Editar
  const btnEditar = e.target.closest('button.btn-editar');
  if (btnEditar) {
    const idx = parseInt(btnEditar.dataset.posicion, 10);
    if (!Number.isInteger(idx)) return;
    const usuario = usuariosGlobales[idx];
    formularioAltaUsuario['nombre'].value = usuario.nombre;
    formularioAltaUsuario['email'].value = usuario.email;
    formularioAltaUsuario['password'].value = '';
    formularioAltaUsuario['role'].value = usuario.rol;
    nodoBotonAgregarUsuario.textContent = 'Actualizar usuario';
    nodoBotonAgregarUsuario.dataset.editIndex = idx;
    formularioAltaUsuario['nombre'].focus();
    return;
  }
});

obtenerYMostrarListadoDeUsuarios();
