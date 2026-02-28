<?php
session_start();
header('Content-Type: application/json');
require 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$uid = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

// --- MODIFY ALL QUERIES TO USE $uid ---
// Example for update_progress:
if ($action === 'update_progress' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $conn->real_escape_string($data['id']);
    $status = $data['status'] ? 1 : 0;
    
    $sql = "INSERT INTO study_progress (user_id, item_id, is_completed) VALUES ($uid, '$id', $status) 
            ON DUPLICATE KEY UPDATE is_completed = $status";
    echo json_encode(["success" => $conn->query($sql)]);
    exit;
}

// ... Repeat similar $uid logic for scores and motivation ...
// Example for load_all:
if ($action === 'load_all') {
    $response = ["progress" => [], "scores" => [], "motivation" => ""];
    
    $res = $conn->query("SELECT item_id FROM study_progress WHERE user_id = $uid AND is_completed = 1");
    while($r = $res->fetch_assoc()) $response['progress'][] = $r['item_id'];
    
    $res = $conn->query("SELECT subject_id, score FROM exam_scores WHERE user_id = $uid");
    while($r = $res->fetch_assoc()) $response['scores'][$r['subject_id']] = $r['score'];

    $res = $conn->query("SELECT setting_value FROM app_settings WHERE user_id = $uid AND setting_key = 'motivation'");
    if($r = $res->fetch_assoc()) $response['motivation'] = $r['setting_value'];

    echo json_encode($response);
    exit;
}





header('Content-Type: application/json');
require 'db_connect.php';

$action = $_GET['action'] ?? '';

// --- SAVE PROGRESS (Checkbox) ---
if ($action === 'update_progress' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $conn->real_escape_string($data['id']);
    $status = $data['status'] ? 1 : 0;
    
    $sql = "INSERT INTO study_progress (item_id, is_completed) VALUES ('$id', $status) 
            ON DUPLICATE KEY UPDATE is_completed = $status";
            
    echo json_encode(["success" => $conn->query($sql)]);
    exit;
}

// --- SAVE SCORE ---
if ($action === 'update_score' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $conn->real_escape_string($data['id']);
    $score = intval($data['score']);
    
    $sql = "INSERT INTO exam_scores (subject_id, score) VALUES ('$id', $score) 
            ON DUPLICATE KEY UPDATE score = $score";
            
    echo json_encode(["success" => $conn->query($sql)]);
    exit;
}

// --- SAVE MOTIVATION ---
if ($action === 'update_motivation' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $text = $conn->real_escape_string($data['text']);
    
    $sql = "UPDATE app_settings SET setting_value = '$text' WHERE setting_key = 'motivation'";
    echo json_encode(["success" => $conn->query($sql)]);
    exit;
}

// --- LOAD ALL DATA (On Page Load) ---
if ($action === 'load_all') {
    $response = ["progress" => [], "scores" => [], "motivation" => ""];

    // Get Progress
    $result = $conn->query("SELECT * FROM study_progress WHERE is_completed = 1");
    while($row = $result->fetch_assoc()) {
        $response['progress'][] = $row['item_id'];
    }

    // Get Scores
    $result = $conn->query("SELECT * FROM exam_scores");
    while($row = $result->fetch_assoc()) {
        $response['scores'][$row['subject_id']] = $row['score'];
    }

    // Get Motivation
    $result = $conn->query("SELECT setting_value FROM app_settings WHERE setting_key = 'motivation'");
    if($row = $result->fetch_assoc()) {
        $response['motivation'] = $row['setting_value'];
    }

    echo json_encode($response);
    exit;
}


if ($action === 'get_leaderboard') {
    $total_items = 68; // Adjust this to match your total number of checkboxes
    $sql = "SELECT users.username, 
            ROUND((COUNT(study_progress.item_id) / $total_items) * 100) as progress 
            FROM users 
            LEFT JOIN study_progress ON users.id = study_progress.user_id AND study_progress.is_completed = 1 
            GROUP BY users.id 
            ORDER BY progress DESC LIMIT 10";
            
    $result = $conn->query($sql);
    $lb = [];
    while($row = $result->fetch_assoc()) {
        $lb[] = $row;
    }
    echo json_encode($lb);
    exit;
}


$conn->close();
?>
