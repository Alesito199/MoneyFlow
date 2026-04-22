<?php
/**
 * MoneyFlow - Cerrar Sesión
 */
session_start();
session_destroy();
header('Location: login.php');
exit;
