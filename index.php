<?php
/**
 * MoneyFlow - Página de Inicio
 * Redirige al login o dashboard según autenticación
 */

session_start();

if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header('Location: dashboard/index.php');
} else {
    header('Location: login.php');
}
exit;
