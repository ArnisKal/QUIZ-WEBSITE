<?php
if(session_status() !== PHP_SESSION_ACTIVE){
    session_start();
}

if (($_SESSION['role'] ?? '') === 'admin') {
    header('Location: admin/admin.php');
    exit;
}