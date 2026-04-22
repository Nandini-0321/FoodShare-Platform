<?php
require_once __DIR__ . '/../models/Chat.php';
require_once __DIR__ . '/../models/Donation.php';
require_once __DIR__ . '/../config/database.php';

class ChatController {
    private $chatModel;
    private $donationModel;
    
    public function __construct() {
        $this->chatModel = new Chat();
        $this->donationModel = new Donation();
        
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
    }
    
    // Handle AJAX requests
    public function handleRequest() {
        // Suppress errors to prevent HTML output in JSON responses
        ini_set('display_errors', 0);
        error_reporting(0);
        header('Content-Type: application/json');
        
        $action = $_GET['action'] ?? $_POST['action'] ?? '';
        
        switch ($action) {
            case 'get_conversations':
                $this->getConversations();
                break;
            case 'get_messages':
                $this->getMessages();
                break;
            case 'send_message':
                $this->sendMessage();
                break;
            case 'mark_read':
                $this->markAsRead();
                break;
            case 'check_new':
                $this->checkNewMessages();
                break;
            case 'get_unread_count':
                $this->getUnreadCount();
                break;
            case 'create_conversation':
                $this->createConversation();
                break;
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid action']);
        }
    }
    
    // Get all conversations for current user
    private function getConversations() {
        $user_id = $_SESSION['user_id'];
        $conversations = $this->chatModel->getUserConversations($user_id);
        
        echo json_encode([
        ]);
    }
    
    // Send a message
    private function sendMessage() {
        $conversation_id = $_POST['conversation_id'] ?? 0;
        $message = trim($_POST['message'] ?? '');
        $user_id = $_SESSION['user_id'];
        $user_name = $_SESSION['full_name'];
        
        // Log request
        file_put_contents('debug_chat.txt', date('Y-m-d H:i:s') . " - Sending message: User $user_id, Conv $conversation_id, Msg: $message\n", FILE_APPEND);
        
        if (empty($message)) {
            echo json_encode(['success' => false, 'error' => 'Message cannot be empty']);
            return;
        }
        
        // Check if user is participant
        if (!$this->chatModel->isParticipant($conversation_id, $user_id)) {
            file_put_contents('debug_chat.txt', date('Y-m-d H:i:s') . " - Access denied for User $user_id in Conv $conversation_id\n", FILE_APPEND);
            http_response_code(403);
            echo json_encode(['error' => 'Access denied']);
            return;
        }
        
        // Send user message
        $message_id = $this->chatModel->sendMessage($conversation_id, $user_id, $user_name, $message);
        
        if ($message_id) {
            file_put_contents('debug_chat.txt', date('Y-m-d H:i:s') . " - Message sent successfully. ID: $message_id\n", FILE_APPEND);
            
            // Check if bot should respond
            $botResponse = $this->generateBotResponse($message, $conversation_id);
            
            // AI Bot Logic for Delivery Issues
            if (!$botResponse) {
                $keywords = ['delay', 'late', 'problem', 'issue', 'damaged', 'missing', 'where', 'stuck', 'help'];
                $msgLower = strtolower($message);
                $foundKeyword = false;
                
                foreach ($keywords as $word) {
                    if (strpos($msgLower, $word) !== false) {
                        $foundKeyword = true;
                        break;
                    }
                }
                
                if ($foundKeyword) {
                    $botReplies = [
                        "I've noted a potential issue with this delivery. I'm notifying the support team immediately.",
                        "I understand there might be a delay or issue. I've flagged this for high-priority review.",
                        "Thanks for the update. I'll help coordinate with the other party to resolve this.",
                        "I'm sorry to hear about the trouble. Our support team has been alerted."
                    ];
                    $botResponse = $botReplies[array_rand($botReplies)];
                }
            }
            
            if ($botResponse) {
                // Send bot message after a short delay (simulated)
                $this->chatModel->sendMessage($conversation_id, 0, 'FoodShare Assistant', $botResponse, true);
            }
            
            echo json_encode([
                'success' => true,
                'message_id' => $message_id,
                'bot_response' => $botResponse ? true : false
            ]);
        } else {
            file_put_contents('debug_chat.txt', date('Y-m-d H:i:s') . " - Failed to insert message into DB\n", FILE_APPEND);
            echo json_encode(['success' => false, 'error' => 'Failed to send message']);
        }
    }
    
    // Mark messages as read
    private function markAsRead() {
        $conversation_id = $_POST['conversation_id'] ?? 0;
        $user_id = $_SESSION['user_id'];
        
        if (!$this->chatModel->isParticipant($conversation_id, $user_id)) {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied']);
            return;
        }
        
        $result = $this->chatModel->markAsRead($conversation_id, $user_id);
        
        echo json_encode(['success' => $result]);
    }
    
    // Check for new messages (polling)
    private function checkNewMessages() {
        $conversation_id = $_GET['conversation_id'] ?? 0;
        $since = $_GET['since'] ?? date('Y-m-d H:i:s', strtotime('-1 minute'));
        $user_id = $_SESSION['user_id'];
        
        if (!$this->chatModel->isParticipant($conversation_id, $user_id)) {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied']);
            return;
        }
        
        $newMessages = $this->chatModel->getNewMessages($conversation_id, $since);
        
        echo json_encode([
            'success' => true,
            'new_messages' => $newMessages,
            'count' => count($newMessages)
        ]);
    }
    
    // Get total unread count
    private function getUnreadCount() {
        $user_id = $_SESSION['user_id'];
        $count = $this->chatModel->getTotalUnreadCount($user_id);
        
        echo json_encode([
            'success' => true,
            'unread_count' => (int)$count
        ]);
    }
    
    // Create a new conversation (called when donation is confirmed)
    private function createConversation() {
        $donation_id = $_POST['donation_id'] ?? 0;
        
        if (!$donation_id) {
            echo json_encode(['success' => false, 'error' => 'Donation ID required']);
            return;
        }
        
        // Create conversation
        $conversation_id = $this->chatModel->createConversation($donation_id);
        
        if ($conversation_id) {
            // Get donation details to add participants
            $conn = getDBConnection();
            $stmt = $conn->prepare("
                SELECT donor_id, assigned_ngo_id, assigned_volunteer_id 
                FROM donations 
                WHERE id = ?
            ");
            $stmt->bind_param("i", $donation_id);
            $stmt->execute();
            $donation = $stmt->get_result()->fetch_assoc();
            
            // Add participants
            if ($donation['donor_id']) {
                $this->chatModel->addParticipant($conversation_id, $donation['donor_id'], 'donor');
            }
            if ($donation['assigned_ngo_id']) {
                $this->chatModel->addParticipant($conversation_id, $donation['assigned_ngo_id'], 'ngo');
            }
            if ($donation['assigned_volunteer_id']) {
                $this->chatModel->addParticipant($conversation_id, $donation['assigned_volunteer_id'], 'volunteer');
            }
            
            // Send welcome message from bot
            $welcomeMessage = "👋 Welcome to the FoodShare chat! I'm your assistant. Feel free to discuss pickup details, timing, or location. You can ask me about:\n\n• Pickup time suggestions\n• Location sharing tips\n• Food safety guidelines\n• Storage recommendations\n\nJust type your question and I'll help!";
            $this->chatModel->sendMessage($conversation_id, 0, 'FoodShare Assistant', $welcomeMessage, true);
            
            echo json_encode([
                'success' => true,
                'conversation_id' => $conversation_id
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to create conversation']);
        }
    }
    
    // Generate bot response based on keywords
    private function generateBotResponse($message, $conversation_id) {
        $message = strtolower($message);
        
        // Pickup time keywords
        if (preg_match('/\b(pickup|pick up|time|when|schedule)\b/i', $message)) {
            return "⏰ **Pickup Time Tips:**\n\n" .
                   "• Coordinate a specific time that works for everyone\n" .
                   "• Consider the food expiry time\n" .
                   "• Allow buffer time for traffic\n" .
                   "• Confirm 30 minutes before pickup\n\n" .
                   "Donor and NGO can decide on the best time!";
        }
        
        // Location keywords
        if (preg_match('/\b(location|address|where|place|directions|map)\b/i', $message)) {
            return "📍 **Location Sharing Tips:**\n\n" .
                   "• Share the complete address with landmarks\n" .
                   "• Provide contact number for directions\n" .
                   "• Mention parking availability\n" .
                   "• Use live tracking once volunteer starts delivery\n\n" .
                   "Clear communication ensures smooth pickup!";
        }
        
        // Food safety keywords
        if (preg_match('/\b(safety|fresh|safe|quality|expiry|expire)\b/i', $message)) {
            return "🛡️ **Food Safety Guidelines:**\n\n" .
                   "• Check expiry date before donation\n" .
                   "• Ensure proper packaging\n" .
                   "• Keep hot food hot (>60°C) and cold food cold (<5°C)\n" .
                   "• Transport in clean, covered containers\n" .
                   "• Deliver within recommended time\n\n" .
                   "Safety first for healthy meals!";
        }
        
        // Storage keywords
        if (preg_match('/\b(storage|store|keep|preserve|refrigerat)\b/i', $message)) {
            return "🏪 **Storage Recommendations:**\n\n" .
                   "• Cooked food: Refrigerate within 2 hours\n" .
                   "• Raw food: Keep in cool, dry place\n" .
                   "• Packaged food: Follow label instructions\n" .
                   "• Dairy: Always refrigerate\n" .
                   "• Fruits/vegetables: Store in ventilated area\n\n" .
                   "Proper storage extends food life!";
        }
        
        // Volunteer keywords
        if (preg_match('/\b(volunteer|driver|delivery)\b/i', $message)) {
            return "🚗 **Volunteer Delivery Info:**\n\n" .
                   "• Volunteer will be assigned for pickup\n" .
                   "• Live tracking available during delivery\n" .
                   "• Contact volunteer directly if needed\n" .
                   "• Confirm handover with photo proof\n\n" .
                   "Our volunteers ensure safe delivery!";
        }
        
        // Thank you / gratitude
        if (preg_match('/\b(thank|thanks|grateful|appreciate)\b/i', $message)) {
            return "🙏 Thank you for using FoodShare! Together we're making a difference by reducing food waste and feeding those in need. Keep up the great work! ❤️";
        }
        
        // Help keywords
        if (preg_match('/\b(help|assist|support|question)\b/i', $message)) {
            return "💡 **I can help you with:**\n\n" .
                   "• Pickup time coordination\n" .
                   "• Location sharing tips\n" .
                   "• Food safety guidelines\n" .
                   "• Storage recommendations\n" .
                   "• Volunteer delivery info\n\n" .
                   "Just ask me anything related to your donation!";
        }
        
        // No specific keyword matched - return null (no bot response)
        return null;
    }
}

// Handle request if called directly
if (basename($_SERVER['PHP_SELF']) == 'ChatController.php') {
    $controller = new ChatController();
    $controller->handleRequest();
}
?>
