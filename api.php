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
$pass = 'Cloudcrew123';
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
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Get the action from the request
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'register':
        registerUser($pdo);
        break;
    case 'login':
        loginUser($pdo);
        break;
    case 'products':
        fetchProducts($pdo);
        break;
    default:
        echo json_encode(['message' => 'Invalid action']);
        break;
}

function registerUser($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $username = $data['username'];
    $email = $data['email'];
    $password = $data['password'];

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM customers WHERE email = ?");
    $stmt->execute([$email]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        echo json_encode(['message' => 'Email already registered']);
        return;
    }

    // Hash password before storing it
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Insert new user with hashed password
    $stmt = $pdo->prepare("INSERT INTO customers (username, email, password) VALUES (?, ?, ?)");
    if ($stmt->execute([$username, $email, $hashedPassword])) {
        echo json_encode(['message' => 'User registered successfully']);
    } else {
        echo json_encode(['message' => 'Registration failed']);
    }
}

function loginUser($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $email = $data['username'];
    $password = $data['password'];

    $stmt = $pdo->prepare("SELECT * FROM customers WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Verify hashed password
    if ($user && password_verify($password, $user['password'])) {
        echo json_encode(['message' => 'Login successful']);
    } else {
        echo json_encode(['message' => 'Invalid email or password']);
    }
}

function fetchProducts($pdo) {
    $stmt = $pdo->query("SELECT * FROM products");
    $products = $stmt->fetchAll();
    echo json_encode($products);
}
