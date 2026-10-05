<?php
session_start();
require_once __DIR__ . '/../database.php';

if (($_SESSION['role'] ?? '') !== 'admin') {
    exit('Access denied!');
}