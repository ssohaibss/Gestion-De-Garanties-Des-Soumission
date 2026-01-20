<?php
$host = 'localhost';
$port = 3306;
$dbname = 'garantie_de_soumission';
$user = 'root';
$pass = '';
$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";


$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       
    PDO::ATTR_EMULATE_PREPARES   => false,                  
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    throw new RuntimeException('Database connection failed: ' . $e->getMessage());
}


function getDBConnection() {
    global $pdo;
    return $pdo;
}
?>