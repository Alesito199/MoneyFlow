<?php
/**
 * MoneyFlow - Verificación de Autenticación
 * Incluir en todas las páginas protegidas
 */

session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: ../login.php');
    exit;
}

// Función para obtener el ID del usuario
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Función para obtener el nombre del usuario
function getUsername() {
    return $_SESSION['nombre'] ?? 'Usuario';
}

// Función para verificar si el usuario es admin
function isAdmin() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
}
