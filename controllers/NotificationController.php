<?php
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../config/database.php';

class NotificationController {
    private $notificationModel;

    public function __construct() {
        $this->notificationModel = new Notification();
        
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function handleRequest() {
        // Suppress errors for JSON output
        ini_set('display_errors', 0);
        error_reporting(0);
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }

        $action = $_GET['action'] ?? $_POST['action'] ?? '';
        $user_id = $_SESSION['user_id'];

        switch ($action) {
            case 'get_unread':
                $notifications = $this->notificationModel->getUnread($user_id);
                echo json_encode(['success' => true, 'notifications' => $notifications]);
                break;
                
            case 'get_recent':
                $notifications = $this->notificationModel->getRecent($user_id);
                echo json_encode(['success' => true, 'notifications' => $notifications]);
                break;

            case 'get_count':
                $count = $this->notificationModel->getUnreadCount($user_id);
                echo json_encode(['success' => true, 'count' => $count]);
                break;

            case 'mark_read':
                $id = $_POST['id'] ?? 0;
                if ($id) {
                    $success = $this->notificationModel->markAsRead($id, $user_id);
                    echo json_encode(['success' => $success]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Missing ID']);
                }
                break;

            case 'mark_all_read':
                $success = $this->notificationModel->markAllAsRead($user_id);
                echo json_encode(['success' => $success]);
                break;

            default:
                echo json_encode(['success' => false, 'error' => 'Invalid action']);
                break;
        }
    }
}

// Initialize and handle request if accessed directly
if (basename($_SERVER['PHP_SELF']) == 'NotificationController.php') {
    $controller = new NotificationController();
    $controller->handleRequest();
}
