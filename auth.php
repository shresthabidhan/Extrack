<?php
session_start();
require 'db_connect.php';

$action = $_GET['action'] ?? '';

// --- REGISTRATION ---
if ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $conn->real_escape_string($_POST['username']);
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (username, password) VALUES ('$user', '$pass')";
    if ($conn->query($sql)) {
        echo json_encode(["success" => true, "message" => "Account created! Please login."]);
    } else {
        echo json_encode(["success" => false, "message" => "Username already exists."]);
    }
    exit;
}

// --- LOGIN ---
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $conn->real_escape_string($_POST['username']);
    $pass = $_POST['password'];

    $result = $conn->query("SELECT id, password FROM users WHERE username = '$user'");
    if ($row = $result->fetch_assoc()) {
        if (password_verify($pass, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $user;
            echo json_encode(["success" => true]);
            exit;
        }
    }
    echo json_encode(["success" => false, "message" => "Invalid credentials."]);
    exit;
}

// --- LOGOUT ---
if ($action === 'logout') {
    session_destroy();
    header("Location: index.php");
    exit;
}
?>
