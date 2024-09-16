<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORS headers
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Database configuration
$host = 'cloudcrew-rds-database.cluster-ctwcqkmykzvj.ap-south-1.rds.amazonaws.com';
$db = 'cloudcrewdatabase';
$user = 'admin';
// Ensure to store sensitive information like passwords in environment variables
$pass = getenv('DB_PASSWORD');
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    handleError($e);
}

$action = $_GET['action'] ?? '';

if ($action === 'register') {
    handleRegister($pdo);
} elseif ($action === 'login') {
    handleLogin($pdo);
} elseif ($action === 'products') {
    handleProducts($pdo);
} else {
    echo json_encode(['message' => 'Invalid action']);
}

function handleRegister($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!validateRegisterData($data)) {
        return;
    }

    $username = trim($data['username']);
    $email = filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL);
    $password = trim($data['password']);

    if (checkEmailExists($pdo, $email)) {
        echo json_encode(['message' => 'Email already registered']);
        return;
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO customers (username, email, password) VALUES (?, ?, ?)");
    $success = $stmt->execute([$username, $email, $hashedPassword]);
    echo json_encode(['message' => $success ? 'User registered successfully' : 'Registration failed']);
}

function validateRegisterData($data) {
    if (empty($data['username']) || empty($data['email']) || empty($data['password']) || empty($data['confirmPassword'])) {
        echo json_encode(['message' => 'Missing required fields']);
        return false;
    }

    if (trim($data['password']) !== trim($data['confirmPassword'])) {
        echo json_encode(['message' => 'Passwords do not match']);
        return false;
    }

    if (!filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['message' => 'Invalid email format']);
        return false;
    }

    return true;
}

function checkEmailExists($pdo, $email) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM customers WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetchColumn() > 0;
}

function handleLogin($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['username']) || empty($data['password'])) {
        echo json_encode(['message' => 'Missing required fields']);
        return;
    }

    $email = trim($data['username']);
    $password = trim($data['password']);

    $stmt = $pdo->prepare("SELECT * FROM customers WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        echo json_encode(['message' => 'Login successful']);
    } else {
        echo json_encode(['message' => 'Invalid email or password']);
    }
}

function handleProducts($pdo) {
    $stmt = $pdo->query("SELECT * FROM products");
    $products = $stmt->fetchAll();
    echo json_encode($products);
}

function handleError($exception) {
    // Log error details to a file (adjust path and method as needed)
    $logFile = __DIR__ . '/error.log';
    $errorMessage = sprintf(
        "[%s] %s: %s\n",
        date('Y-m-d H:i:s'),
        $exception->getFile() . ':' . $exception->getLine(),
        $exception->getMessage()
    );
    file_put_contents($logFile, $errorMessage, FILE_APPEND);

    // Respond with a generic error message
    echo json_encode(['message' => 'Internal server error']);
}
