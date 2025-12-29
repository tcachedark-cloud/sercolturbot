<?php
/**
 * AUTH - Verificación de sesión
 * Incluir al inicio de páginas protegidas
 */
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Función para verificar rol
function tieneRol($roles) {
    if (!is_array($roles)) $roles = [$roles];
    return in_array($_SESSION['rol'] ?? '', $roles);
}

// Función para obtener nombre de usuario
function getNombreUsuario() {
    return $_SESSION['nombre'] ?? $_SESSION['username'] ?? 'Usuario';
}

// Función para obtener rol
function getRolUsuario() {
    return $_SESSION['rol'] ?? 'operador';
}