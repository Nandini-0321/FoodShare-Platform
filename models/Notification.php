<?php
require_once __DIR__ . '/../config/database.php';

class Notification {
    private $conn;
    private $table = 'notifications';

    public function __construct() {
        $this->conn = getDBConnection();
    }

    // Create a notification
    public function create($user_id, $type, $title, $message, $link = null) {
        $stmt = $this->conn->prepare("INSERT INTO " . $this->table . " (user_id, type, title, message, link) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $user_id, $type, $title, $message, $link);
        return $stmt->execute();
    }

    // Get unread notifications for a user
    public function getUnread($user_id) {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table . " WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Get recent notifications (read and unread)
    public function getRecent($user_id, $limit = 10) {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table . " WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
        $stmt->bind_param("ii", $user_id, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Mark a notification as read
    public function markAsRead($id, $user_id) {
        $stmt = $this->conn->prepare("UPDATE " . $this->table . " SET is_read = 1 WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $user_id);
        return $stmt->execute();
    }

    // Mark all notifications as read for a user
    public function markAllAsRead($user_id) {
        $stmt = $this->conn->prepare("UPDATE " . $this->table . " SET is_read = 1 WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        return $stmt->execute();
    }
    
    // Get unread count
    public function getUnreadCount($user_id) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM " . $this->table . " WHERE user_id = ? AND is_read = 0");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row['count'];
    }
}
