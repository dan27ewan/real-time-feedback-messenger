<?php
include 'db.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$uID = (int)($_GET['user_id'] ?? 0);
$tID = (int)($_GET['target_id'] ?? 0);

if ($action == 'update_name') {
    $name = $conn->real_escape_string($_POST['display_name']);
    $conn->query("INSERT INTO users (id, display_name) VALUES ($uID, '$name') 
                  ON DUPLICATE KEY UPDATE display_name = '$name'");
    echo json_encode(['status' => 'success']);
} 

elseif ($action == 'fetch') {
    $msgs = $conn->query("SELECT sender_id, message, DATE_FORMAT(timestamp, '%h:%i %p') as time_sent 
                          FROM messages 
                          WHERE (sender_id=$uID AND receiver_id=$tID) 
                          OR (sender_id=$tID AND receiver_id=$uID) 
                          ORDER BY id ASC");
    
    $target = $conn->query("SELECT display_name, is_typing FROM users WHERE id = $tID")->fetch_assoc();
    
    echo json_encode([
        'messages' => $msgs->fetch_all(MYSQLI_ASSOC),
        'target_name' => $target['display_name'] ?? 'Waiting for Partner...',
        'is_typing' => (int)($target['is_typing'] ?? 0)
    ]);
}

elseif ($action == 'send') {
    $msg = $conn->real_escape_string($_POST['message']);
    if(!empty($msg)) {
        $conn->query("INSERT INTO messages (sender_id, receiver_id, message) VALUES ($uID, $tID, '$msg')");
    }
}

elseif ($action == 'set_typing') {
    $status = (int)$_POST['status'];
    $conn->query("UPDATE users SET is_typing = $status WHERE id = $uID");
}

elseif ($action == 'clear_chat') {
    $conn->query("DELETE FROM messages WHERE (sender_id=$uID AND receiver_id=$tID) OR (sender_id=$tID AND receiver_id=$uID)");
}
?>