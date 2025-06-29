<?php
require_once 'config.php';

function obtenerColaboradoresPorNivel($nivel, $soloActivos = true) {
    global $db;
    
    $sql = "SELECT * FROM colaboradores WHERE nivel = :nivel";
    if ($soloActivos) {
        $sql .= " AND activo = 1";
    }
    $sql .= " ORDER BY nombre";
    
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':nivel', $nivel);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function obtenerIconoPorNivel($nivel) {
    switch ($nivel) {
        case 'gerente':
        case 'jefe':
            return 'fas fa-user-tie';
        case 'supervisor':
            return 'fas fa-user-shield';
        case 'tecnico':
            return 'fas fa-user-cog';
        case 'bo':
            return 'fas fa-headset';
        case 'noc':
            return 'fas fa-network-wired';
        case 'hd':
            return 'fas fa-headset';
        default:
            return 'fas fa-user';
    }
}

function calcularTiempoEmpresa($fechaIngreso) {
    $fechaIng = new DateTime($fechaIngreso);
    $hoy = new DateTime();
    $intervalo = $hoy->diff($fechaIng);
    
    $años = $intervalo->y;
    $meses = $intervalo->m;
    
    return "$años año".($años != 1 ? 's' : '')." y $meses mes".($meses != 1 ? 'es' : '');
}

function actualizarProximosCumpleanos() {
    global $db;
    
    $hoy = new DateTime();
    $sql = "SELECT nombre, DATE_FORMAT(cumpleanos, '%d/%m') as fecha 
            FROM colaboradores 
            WHERE activo = 1 AND DATE_FORMAT(cumpleanos, '%m-%d') BETWEEN DATE_FORMAT(NOW(), '%m-%d') 
            AND DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 30 DAY), '%m-%d')
            ORDER BY DATE_FORMAT(cumpleanos, '%m-%d') LIMIT 3";
    
    $stmt = $db->query($sql);
    $cumpleanos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($cumpleanos)) {
        return 'No hay cumpleaños próximos en los próximos 30 días';
    }
    
    return implode(', ', array_map(function($persona) {
        return "{$persona['nombre']} ({$persona['fecha']})";
    }, $cumpleanos));
}

function obtenerColaborador($id) {
    global $db;
    
    $stmt = $db->prepare("SELECT * FROM colaboradores WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function obtenerColaboradores($soloActivos = false) {
    global $db;
    
    $sql = "SELECT * FROM colaboradores";
    if ($soloActivos) {
        $sql .= " WHERE activo = 1";
    }
    $sql .= " ORDER BY nivel, nombre";
    
    return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

function agregarColaborador($datos) {
    global $db;
    
    $sql = "INSERT INTO colaboradores (identificador, nombre, cargo, descripcion, hobby, cumpleanos, ingreso, foto, nivel, activo) 
            VALUES (:identificador, :nombre, :cargo, :descripcion, :hobby, :cumpleanos, :ingreso, :foto, :nivel, :activo)";
    
    $stmt = $db->prepare($sql);
    return $stmt->execute($datos);
}

function actualizarColaborador($id, $datos) {
    global $db;
    
    $sql = "UPDATE colaboradores SET 
            identificador = :identificador,
            nombre = :nombre,
            cargo = :cargo,
            descripcion = :descripcion,
            hobby = :hobby,
            cumpleanos = :cumpleanos,
            ingreso = :ingreso,
            foto = :foto,
            nivel = :nivel,
            activo = :activo
            WHERE id = :id";
    
    $datos['id'] = $id;
    $stmt = $db->prepare($sql);
    return $stmt->execute($datos);
}

function cambiarEstadoColaborador($id, $activo) {
    global $db;
    
    $stmt = $db->prepare("UPDATE colaboradores SET activo = ? WHERE id = ?");
    return $stmt->execute([$activo, $id]);
}

function eliminarColaborador($id) {
    global $db;
    
    $stmt = $db->prepare("DELETE FROM colaboradores WHERE id = ?");
    return $stmt->execute([$id]);
}

function subirFoto($archivo) {
    $directorio = '../img/';
    $nombreArchivo = uniqid() . '-' . basename($archivo['name']);
    $rutaCompleta = $directorio . $nombreArchivo;
    
    // Verificar tipo de archivo
    $tipoPermitido = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($archivo['type'], $tipoPermitido)) {
        return ['error' => 'Solo se permiten imágenes JPEG, PNG o GIF'];
    }
    
    // Verificar tamaño (máximo 2MB)
    if ($archivo['size'] > 2000000) {
        return ['error' => 'La imagen no debe superar los 2MB'];
    }
    
    // Mover el archivo
    if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
        return ['success' => 'img/' . $nombreArchivo];
    } else {
        return ['error' => 'Error al subir la imagen'];
    }
}
?>