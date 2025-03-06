<?php
// Configuración de la conexión a la base de datos
$host = 'localhost';
$dbname = 'sistema_ventas';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Configurar el modo de error PDO a excepciones
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Establecer el conjunto de caracteres a utf8
    $conn->exec("SET NAMES utf8");
} catch(PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
    die();
}
?>