<?php
require_once 'config.php';

function verificarLogin($username, $password) {
    global $db;
    
    $stmt = $db->prepare("SELECT id, username, password, rol, nombre FROM usuarios WHERE username = ? AND activo = 1");
    $stmt->execute([$username]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuario && password_verify($password, $usuario['password'])) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_username'] = $usuario['username'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        $_SESSION['usuario_rol'] = $usuario['rol'];
        return true;
    }
    
    return false;
}

function estaAutenticado() {
    return isset($_SESSION['usuario_id']);
}

function esAdmin() {
    return estaAutenticado() && $_SESSION['usuario_rol'] === 'admin';
}

function requerirAutenticacion() {
    if (!estaAutenticado()) {
        header("Location: " . BASE_URL . "/admin/login.php");
        exit;
    }
}

function requerirAdmin() {
    requerirAutenticacion();
    if (!esAdmin()) {
        header("Location: " . BASE_URL . "/admin/dashboard.php");
        exit;
    }
}
?>