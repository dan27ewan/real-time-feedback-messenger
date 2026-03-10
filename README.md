CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY,
    display_name VARCHAR(15),
    is_typing TINYINT(1) DEFAULT 0
);

CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT,
    receiver_id INT,
    message TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
