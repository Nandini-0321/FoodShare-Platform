<?php
require_once __DIR__ . '/../config/database.php';

class Chat {
    private $conn;
    
    public function __construct() {
        $this->conn = getDBConnection();
    }
    
    // Create a new conversation for a donation
    public function createConversation($donation_id, $title = null) {
        try {
            // Check if conversation already exists
            $stmt = $this->conn->prepare("SELECT id FROM conversations WHERE donation_id = ?");
            $stmt->bind_param("i", $donation_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                return $row['id'];
            }
            
            // Get donation details for title
            if (!$title) {
                $donationStmt = $this->conn->prepare("SELECT food_name FROM donations WHERE id = ?");
                $donationStmt->bind_param("i", $donation_id);
                $donationStmt->execute();
                $donationResult = $donationStmt->get_result();
                $donation = $donationResult->fetch_assoc();
                $title = "Chat: " . $donation['food_name'];
            }
            
            // Create conversation
            $stmt = $this->conn->prepare("INSERT INTO conversations (donation_id, title) VALUES (?, ?)");
            $stmt->bind_param("is", $donation_id, $title);
            $stmt->execute();
            
            return $this->conn->insert_id;
        } catch (Exception $e) {
            error_log("Error creating conversation: " . $e->getMessage());
            return false;
        }
    }
    
    // Add participant to conversation
    public function addParticipant($conversation_id, $user_id, $user_role) {
        try {
            // Check if already a participant
            $stmt = $this->conn->prepare("SELECT id FROM chat_participants WHERE conversation_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $conversation_id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                return true; // Already a participant
            }
            
            $stmt = $this->conn->prepare("INSERT INTO chat_participants (conversation_id, user_id, user_role) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $conversation_id, $user_id, $user_role);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error adding participant: " . $e->getMessage());
            return false;
        }
    }
    
    // Get all conversations for a user
    public function getUserConversations($user_id) {
        $stmt = $this->conn->prepare("
            SELECT 
                c.id,
                c.donation_id,
                c.title,
                c.last_message,
                c.last_message_at,
                cp.unread_count,
                cp.user_role as my_role,
                d.food_name,
                d.status as donation_status,
                d.donor_id,
                d.assigned_ngo_id,
                d.assigned_volunteer_id,
                donor.full_name as donor_name,
                ngo.full_name as ngo_name,
                volunteer.full_name as volunteer_name
            FROM conversations c
            INNER JOIN chat_participants cp ON c.id = cp.conversation_id
            INNER JOIN donations d ON c.donation_id = d.id
            LEFT JOIN users donor ON d.donor_id = donor.id
            LEFT JOIN users ngo ON d.assigned_ngo_id = ngo.id
            LEFT JOIN users volunteer ON d.assigned_volunteer_id = volunteer.id
            WHERE cp.user_id = ?
            ORDER BY c.last_message_at DESC, c.created_at DESC
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $conversations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Build dynamic titles showing who the user is chatting with
        foreach ($conversations as &$conv) {
            $participants = [];
            
            // Add other participants (not the current user)
            if ($conv['donor_id'] && $conv['donor_id'] != $user_id) {
                $participants[] = $conv['donor_name'];
            }
            if ($conv['assigned_ngo_id'] && $conv['assigned_ngo_id'] != $user_id) {
                $participants[] = $conv['ngo_name'];
            }
            if ($conv['assigned_volunteer_id'] && $conv['assigned_volunteer_id'] != $user_id) {
                $participants[] = $conv['volunteer_name'];
            }
            
            // Build title
            if (!empty($participants)) {
                $conv['title'] = 'Chat with ' . implode(', ', $participants) . ' - ' . $conv['food_name'];
            } else {
                $conv['title'] = 'Chat: ' . $conv['food_name'];
            }
        }
        
        return $conversations;
    }
    
    // Get messages for a conversation
    public function getMessages($conversation_id, $limit = 100) {
        $stmt = $this->conn->prepare("
            SELECT 
                id,
                sender_id,
                sender_name,
                message,
                is_bot,
                is_read,
                created_at
            FROM chat_messages
            WHERE conversation_id = ?
            ORDER BY created_at ASC
            LIMIT ?
        ");
        $stmt->bind_param("ii", $conversation_id, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    // Get new messages since a timestamp
    public function getNewMessages($conversation_id, $since_timestamp) {
        $stmt = $this->conn->prepare("
            SELECT 
                id,
                sender_id,
                sender_name,
                message,
                is_bot,
                is_read,
                created_at
            FROM chat_messages
            WHERE conversation_id = ? AND created_at > ?
            ORDER BY created_at ASC
        ");
        $stmt->bind_param("is", $conversation_id, $since_timestamp);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    // Send a message
    public function sendMessage($conversation_id, $sender_id, $sender_name, $message, $is_bot = false) {
        try {
            $this->conn->begin_transaction();
            
            // Insert message
            $stmt = $this->conn->prepare("
                INSERT INTO chat_messages (conversation_id, sender_id, sender_name, message, is_bot) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("iissi", $conversation_id, $sender_id, $sender_name, $message, $is_bot);
            $stmt->execute();
            $message_id = $this->conn->insert_id;
            
            // Update conversation last message
            $stmt = $this->conn->prepare("
                UPDATE conversations 
                SET last_message = ?, last_message_at = NOW() 
                WHERE id = ?
            ");
            $stmt->bind_param("si", $message, $conversation_id);
            $stmt->execute();
            
            // Update unread counts for other participants
            $stmt = $this->conn->prepare("
                UPDATE chat_participants 
                SET unread_count = unread_count + 1 
                WHERE conversation_id = ? AND user_id != ?
            ");
            $stmt->bind_param("ii", $conversation_id, $sender_id);
            $stmt->execute();
            
            $this->conn->commit();
            return $message_id;
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Error sending message: " . $e->getMessage());
            return false;
        }
    }
    
    // Mark messages as read
    public function markAsRead($conversation_id, $user_id) {
        try {
            $this->conn->begin_transaction();
            
            // Update participant's last read time and reset unread count
            $stmt = $this->conn->prepare("
                UPDATE chat_participants 
                SET last_read_at = NOW(), unread_count = 0 
                WHERE conversation_id = ? AND user_id = ?
            ");
            $stmt->bind_param("ii", $conversation_id, $user_id);
            $stmt->execute();
            
            // Mark messages as read (optional - for global read status)
            $stmt = $this->conn->prepare("
                UPDATE chat_messages 
                SET is_read = 1 
                WHERE conversation_id = ? AND sender_id != ?
            ");
            $stmt->bind_param("ii", $conversation_id, $user_id);
            $stmt->execute();
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Error marking as read: " . $e->getMessage());
            return false;
        }
    }
    
    // Get conversation by donation ID
    public function getConversationByDonation($donation_id) {
        $stmt = $this->conn->prepare("SELECT id FROM conversations WHERE donation_id = ?");
        $stmt->bind_param("i", $donation_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['id'];
        }
        return null;
    }
    
    // Get total unread count for user
    public function getTotalUnreadCount($user_id) {
        $stmt = $this->conn->prepare("
            SELECT SUM(unread_count) as total 
            FROM chat_participants 
            WHERE user_id = ?
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['total'] ?? 0;
    }
    
    // Check if user is participant
    public function isParticipant($conversation_id, $user_id) {
        $stmt = $this->conn->prepare("
            SELECT id FROM chat_participants 
            WHERE conversation_id = ? AND user_id = ?
        ");
        $stmt->bind_param("ii", $conversation_id, $user_id);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }
    
    // Get conversation participants
    public function getParticipants($conversation_id) {
        $stmt = $this->conn->prepare("
            SELECT 
                cp.user_id,
                cp.user_role,
                u.full_name,
                u.email
            FROM chat_participants cp
            INNER JOIN users u ON cp.user_id = u.id
            WHERE cp.conversation_id = ?
        ");
        $stmt->bind_param("i", $conversation_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>
