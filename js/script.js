// Mostrar próximos cumpleaños
function actualizarProximosCumpleanos() {
    const hoy = new Date();
    const cumpleanosProximos = [];
    
    for (const id in equipoData) {
        const [dia, mes] = equipoData[id].cumpleanos.split('/').map(Number);
        const fechaCumple = new Date(hoy.getFullYear(), mes - 1, dia);
        
        // Si el cumpleaños ya pasó este año, verificar el próximo año
        if (fechaCumple < hoy) {
            fechaCumple.setFullYear(hoy.getFullYear() + 1);
        }
        
        const diasRestantes = Math.floor((fechaCumple - hoy) / (1000 * 60 * 60 * 24));
        
        if (diasRestantes <= 30) {
            cumpleanosProximos.push({
                nombre: equipoData[id].nombre,
                dias: diasRestantes,
                fecha: `${dia}/${mes}`
            });
        }
    }
    
    // Ordenar por días restantes
    cumpleanosProximos.sort((a, b) => a.dias - b.dias);

    const notificacion = document.getElementById('cumpleanos-notificacion');
    notificacion.innerHTML = ''; // Limpiar contenido previo

    if (cumpleanosProximos.length > 0) {
        const lista = document.createElement('ul');
        lista.style.paddingLeft = '20px';

        cumpleanosProximos.slice(0, 3).forEach(persona => {
            const item = document.createElement('li');
            item.textContent = `${persona.nombre} (${persona.fecha}) - ${persona.dias} días`;
            lista.appendChild(item);
        });

        notificacion.appendChild(lista);

        if (cumpleanosProximos.length > 3) {
            const ellipsis = document.createElement('p');
            ellipsis.textContent = '...';
            notificacion.appendChild(ellipsis);
        }
    } else {
        notificacion.textContent = 'No hay cumpleaños próximos en los próximos 30 días';
    }
}

// Calcular tiempo en la empresa
function calcularTiempoEmpresa(fechaIngreso) {
    const [dia, mes, anio] = fechaIngreso.split('/').map(Number);
    const fechaIng = new Date(anio, mes - 1, dia);
    const hoy = new Date();
    
    let años = hoy.getFullYear() - fechaIng.getFullYear();
    let meses = hoy.getMonth() - fechaIng.getMonth();
    
    if (meses < 0 || (meses === 0 && hoy.getDate() < fechaIng.getDate())) {
        años--;
        meses += 12;
    }
    
    return `${años} año${años !== 1 ? 's' : ''} y ${meses} mes${meses !== 1 ? 'es' : ''}`;
}

// Mostrar perfil con foto
function mostrarPerfil(id) {
    const miembro = equipoData[id];
    if (!miembro) return;
    
    document.getElementById('perfil-nombre').textContent = miembro.nombre;
    document.getElementById('perfil-cargo').textContent = miembro.cargo;
    document.getElementById('perfil-descripcion').textContent = miembro.descripcion;
    document.getElementById('perfil-hobby').textContent = miembro.hobby;
    document.getElementById('perfil-cumpleanos').textContent = miembro.cumpleanos;
    document.getElementById('perfil-ingreso').textContent = miembro.ingreso;
    
    // Mostrar la foto del perfil
    const fotoPerfil = document.getElementById('perfil-foto');
    fotoPerfil.src = miembro.foto || 'img/fotos/default.png';
    fotoPerfil.alt = `Foto de ${miembro.nombre}`;
    
    const tiempoEmpresa = calcularTiempoEmpresa(miembro.ingreso);
    document.getElementById('tiempo-empresa').textContent = 
        `Este colaborador lleva ${tiempoEmpresa} en la empresa`;
    
    document.getElementById('perfil-modal').style.display = 'block';
}

// Cerrar modal
function cerrarModal() {
    document.getElementById('perfil-modal').style.display = 'none';
}

// Cerrar al hacer clic fuera del modal
window.onclick = function(event) {
    const modal = document.getElementById('perfil-modal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    actualizarProximosCumpleanos();
});