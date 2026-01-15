<?php
/**
 * Test Login Script
 */

// Define BASEPATH to allow loading models
define('BASEPATH', '/home/st32477/domains/erp.chisarecubrimientos.com.mx/public_html/');
define('EXT', '.php');

// We need a minimal CI environment to test the model
// Since it's a bit complex to bootstrap full CI here, we will just test the logic manually or use the DB directly

$hostname = 'localhost';
$username = 'st32477_chisa';
$password = 'hADJXjtLjYp4ykTtRzEG';
$database = 'st32477_chisa';

$conn = new mysqli($hostname, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user = 'soporte2@especialistasweb.com.mx';
$pass = 'Prueba123@';

$sql = "SELECT username, password, estatus FROM administradores WHERE username = '$user'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo "User found: " . $row['username'] . "\n";
    echo "Status: " . $row['estatus'] . " (Expected 1)\n";
    
    if (password_verify($pass, $row['password'])) {
        echo "Password verification: SUCCESS\n";
    } else {
        echo "Password verification: FAILED\n";
    }
} else {
    echo "User not found.\n";
}

$conn->close();
?>
