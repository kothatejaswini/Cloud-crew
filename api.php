<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Database configuration
$host = 'cloudcrew-rds-database.cluster-ctwcqkmykzvj.ap-south-1.rds.amazonaws.com';
$db = 'cloudcrewdatabase';
$user = 'admin';
$pass = 'Cloudcrew123'; // Store this securely in environment variables
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

// Get the action from the request
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'register':
        handleRegister($pdo);
        break;
    case 'login':
        handleLogin($pdo);
        break;
    case 'products':
        handleProducts($pdo);
        break;
    default:
        echo json_encode(['message' => 'Invalid action']);
        break;
}

function handleRegister($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['username'], $data['email'], $data['password'], $data['confirmPassword'])) {
        echo json_encode(['message' => 'Missing required fields']);
        return;
    }

    $username = trim($data['username']);
    $email = filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL);
    $password = trim($data['password']);
    $confirmPassword = trim($data['confirmPassword']);

    if (!$email) {
        echo json_encode(['message' => 'Invalid email format']);
        return;
    }

    if ($password !== $confirmPassword) {
        echo json_encode(['message' => 'Passwords do not match']);
        return;
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM customers WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['message' => 'Email already registered']);
        return;
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("INSERT INTO customers (username, email, password) VALUES (?, ?, ?)");
    if ($stmt->execute([$username, $email, $hashedPassword])) {
        echo json_encode(['message' => 'User registered successfully']);
    } else {
        echo json_encode(['message' => 'Registration failed']);
    }
}

function handleLogin($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['username'], $data['password'])) {
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
