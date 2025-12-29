<?php
require_once '../config/database.php';
require_once '../controllers/DashboardController.php';
header('Content-Type: application/json');
echo json_encode((new DashboardController)->datos($pdo));
?>