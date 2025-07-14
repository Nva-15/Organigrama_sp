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
    $hoy_md = $hoy->format('m-d');
    
    $sql = "SELECT nombre, cumpleanos 
            FROM colaboradores 
            WHERE activo = 1 
            AND (
                DATE_FORMAT(cumpleanos, '%m-%d') = DATE_FORMAT(NOW(), '%m-%d')
                OR DATE_FORMAT(cumpleanos, '%m-%d') BETWEEN DATE_FORMAT(NOW(), '%m-%d') 
                AND DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 30 DAY), '%m-%d')
            )
            ORDER BY 
                CASE WHEN DATE_FORMAT(cumpleanos, '%m-%d') = DATE_FORMAT(NOW(), '%m-%d') THEN 0 ELSE 1 END,
                DATE_FORMAT(cumpleanos, '%m-%d')
            LIMIT 3";
    
    $stmt = $db->query($sql);
    $cumpleanos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($cumpleanos)) {
        return '<div>No hay cumpleaños próximos en los próximos 30 días</div>';
    }
    
    $resultado = '<ul style="list-style-type: none; padding-left: 0; margin: 0;">';
    
    foreach ($cumpleanos as $persona) {
        $fechaCumple = new DateTime($persona['cumpleanos']);
        $fechaCumple->setDate($hoy->format('Y'), $fechaCumple->format('m'), $fechaCumple->format('d'));
        
        $diferencia = $hoy->diff($fechaCumple);
        $dias = $diferencia->days;
        
        if ($dias === 0) {
            // Estilo para el cumpleañero de hoy
            $resultado .= '<li style="background-color: #078181ff; color: #f18c07ff; padding: 5px; border-radius: 4px; margin-bottom: 3px;">';
            $resultado .= '🎉 <strong>'.$persona['nombre'].'</strong> 🎉 (HOY)';
            $resultado .= '</li>';
        } else {
            // Estilo normal para otros cumpleaños
            $resultado .= '<li style="padding: 5px; margin-bottom: 3px;">';
            $resultado .= $persona['nombre'].' (en '.$dias.' día'.($dias != 1 ? 's' : '').')';
            $resultado .= '</li>';
        }
    }
    
    $resultado .= '</ul>';
    return $resultado;
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