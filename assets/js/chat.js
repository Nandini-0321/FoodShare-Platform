class ChatSystem {
    constructor() {
        this.isOpen = false;
        this.currentConversationId = null;
        this.baseUrl = '/Foodshare3/controllers/ChatController.php';
        this.socket = (typeof io !== 'undefined') ? io('http://localhost:3000') : null;

        this.init();
    }

    init() {
        this.createChatModal();
        this.updateUnreadCount();

        // Connect Socket.io
        if (this.socket) {
            this.socket.on('new_message', (msg) => {
                if (this.currentConversationId == msg.conversation_id) {
                    this.appendMessage(msg);
                    this.scrollToBottom();
                    // Mark read when observing live
                    this.markAsRead(msg.conversation_id);
                } else {
                    this.updateUnreadCount();
                    if(this.isOpen && !this.currentConversationId) {
                        this.loadConversations(); // refresh list
                    }
                }
            });
            this.socket.on('global_chat_update', (data) => {
                this.updateUnreadCount();
                if(this.isOpen && !this.currentConversationId) {
                    this.loadConversations(); // refresh list
                }
            });
        }

        // Add global click listener for chat triggers
        document.addEventListener('click', (e) => {
            if (e.target.closest('.chat-trigger')) {
                this.toggleChat();
            }
        });
    }

    createChatModal() {
        const modal = document.createElement('div');
        modal.id = 'chat-modal';
        modal.className = 'chat-modal';
        modal.style.display = 'none';

        modal.innerHTML = `
            <div class="chat-container">
                <div class="chat-header">
                    <div class="header-title">
                        <i class="fas fa-comments"></i>
                        <span>Messages (Live) <span id="socket-status" style="font-size: 10px; color: ${this.socket ? '#4facfe' : 'red'};">• ${this.socket ? 'Connected' : 'Offline'}</span></span>
                    </div>
                    <button class="close-chat" onclick="window.chatSystem.toggleChat()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="chat-body">
                    <!-- List View -->
                    <div id="chat-list-view" class="chat-view">
                        <div class="search-bar">
                            <input type="text" placeholder="Search conversations...">
                            <i class="fas fa-search"></i>
                        </div>
                        <div id="conversation-list" class="conversation-list">
                            <!-- Conversations will be loaded here -->
                        </div>
                    </div>
                    
                    <!-- Thread View -->
                    <div id="chat-thread-view" class="chat-view" style="display: none;">
                        <div class="thread-header">
                            <button class="back-button" onclick="window.chatSystem.showListView()">
                                <i class="fas fa-arrow-left"></i>
                            </button>
                            <span id="chat-thread-title">User Name</span>
                        </div>
                        <div id="messages-container" class="messages-container">
                            <!-- Messages will be loaded here -->
                        </div>
                        <div class="chat-input-area">
                            <input type="text" id="chat-input" placeholder="Type a message...">
                            <button onclick="window.chatSystem.sendMessage()">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // Add enter key listener for input
        setTimeout(() => {
            const input = document.getElementById('chat-input');
            if (input) {
                input.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') this.sendMessage();
                });
            }
        }, 100);
    }

    toggleChat(show = null) {
        const modal = document.getElementById('chat-modal');
        if (show === null) {
            this.isOpen = !this.isOpen;
        } else {
            this.isOpen = show;
        }

        modal.style.display = this.isOpen ? 'flex' : 'none';

        if (this.isOpen) {
            this.loadConversations();
        }
    }

    showListView() {
        if(this.currentConversationId && this.socket) {
            this.socket.emit('leave_chat', this.currentConversationId);
        }

        document.getElementById('chat-list-view').style.display = 'flex';
        document.getElementById('chat-thread-view').style.display = 'none';
        this.currentConversationId = null;
        this.loadConversations();
    }

    showThreadView(conversationId, title) {
        this.currentConversationId = conversationId;
        document.getElementById('chat-list-view').style.display = 'none';
        document.getElementById('chat-thread-view').style.display = 'flex';
        document.getElementById('chat-thread-title').textContent = title;

        this.loadMessages(conversationId);
        
        if (this.socket) {
            this.socket.emit('join_chat', conversationId);
        }
    }

    async loadConversations() {
        try {
            const response = await fetch(`${this.baseUrl}?action=get_conversations`);
            const data = await response.json();

            if (data.success) {
                this.renderConversations(data.conversations);
            }
        } catch (error) {
            console.error('Error loading conversations:', error);
        }
    }

    renderConversations(conversations) {
        const list = document.getElementById('conversation-list');
        list.innerHTML = '';

        if (conversations.length === 0) {
            list.innerHTML = '<div style="text-align: center; padding: 20px; color: #999;">No conversations yet</div>';
            return;
        }

        conversations.forEach(conv => {
            const item = document.createElement('div');
            item.className = 'conversation-item';
            if (conv.unread_count > 0) item.classList.add('active');

            item.innerHTML = `
                <div class="conv-title">
                    ${conv.title}
                    ${conv.unread_count > 0 ? '<span class="unread-dot"></span>' : ''}
                </div>
                <div class="conv-preview">${conv.last_message || 'No messages yet'}</div>
                <div class="conv-meta">
                    <span>${this.formatTime(conv.last_message_at)}</span>
                </div>
            `;

            item.addEventListener('click', () => this.showThreadView(conv.id, conv.title));
            list.appendChild(item);
        });
    }

    async loadMessages(conversationId) {
        const container = document.getElementById('messages-container');
        container.innerHTML = '<div style="text-align: center; color: #999;">Loading...</div>';

        try {
            const response = await fetch(`${this.baseUrl}?action=get_messages&conversation_id=${conversationId}`);
            const data = await response.json();

            if (data.success) {
                container.innerHTML = '';
                data.messages.forEach(msg => this.appendMessage(msg));
                this.scrollToBottom();
                this.markAsRead(conversationId);
            }
        } catch (error) {
            console.error('Error loading messages:', error);
        }
    }

    appendMessage(msg) {
        const container = document.getElementById('messages-container');
        const div = document.createElement('div');

        // Determine class based on sender
        const isMe = msg.sender_id == window.CURRENT_USER_ID;

        let className = 'message';
        if (msg.is_bot == 1) className += ' bot';
        else className += isMe ? ' sent' : ' received';

        div.className = className;
        div.innerHTML = `
            ${!isMe && !msg.is_bot ? `<strong>${msg.sender_name}</strong><br>` : ''}
            ${this.formatMessage(msg.message)}
            <div class="message-time">${this.formatTime(msg.created_at)}</div>
        `;

        container.appendChild(div);
    }

    formatMessage(text) {
        // Convert newlines to <br> and bold markdown to <b>
        return text
            .replace(/\n/g, '<br>')
            .replace(/\*\*(.*?)\*\*/g, '<b>$1</b>');
    }

    async sendMessage() {
        const input = document.getElementById('chat-input');
        const message = input.value.trim();

        if (!message || !this.currentConversationId) return;

        input.value = ''; // Clear input immediately

        // Use WebSocket to send the message for real-time delivery
        if (this.socket) {
            this.socket.emit('send_message', {
                conversation_id: this.currentConversationId,
                sender_id: window.CURRENT_USER_ID,
                sender_name: window.CURRENT_USER_NAME || 'User',
                message: message,
                is_bot: 0
            });
            // We do not append locally right away because the socket will broadcast it back to us via 'new_message'.
            // Actually, server broadcasts to room including sender, or we can append optimistic UI here and ignore self broadcast.
            // Let's just let the server echo it back to ensure DB saved order!
        } else {
            // Fallback to fetch if Socket is offline
            try {
                const formData = new FormData();
                formData.append('action', 'send_message');
                formData.append('conversation_id', this.currentConversationId);
                formData.append('message', message);

                const response = await fetch(this.baseUrl, { method: 'POST', body: formData });
                const data = await response.json();
                if (data.success) {
                    this.appendMessage({
                        sender_id: window.CURRENT_USER_ID,
                        message: message,
                        created_at: new Date().toISOString(),
                        is_bot: 0
                    });
                    this.scrollToBottom();
                }
            } catch (error) {
                alert('Failed to send message: ' + error.message);
            }
        }
    }

    async markAsRead(conversationId) {
        if (this.socket) {
            this.socket.emit('mark_read', {
                conversation_id: conversationId,
                user_id: window.CURRENT_USER_ID
            });
            this.updateUnreadCount();
        } else {
            try {
                const formData = new FormData();
                formData.append('action', 'mark_read');
                formData.append('conversation_id', conversationId);
                await fetch(this.baseUrl, { method: 'POST', body: formData });
                this.updateUnreadCount(); 
            } catch (error) {
                console.error('Error marking read:', error);
            }
        }
    }

    async updateUnreadCount() {
        try {
            const response = await fetch(`${this.baseUrl}?action=get_unread_count`);
            const data = await response.json();

            if (data.success) {
                const badge = document.getElementById('chat-total-unread');
                if (data.unread_count > 0) {
                    badge.textContent = data.unread_count;
                    badge.style.display = 'block';
                } else {
                    badge.style.display = 'none';
                }
            }
        } catch (error) {
            console.error('Error updating badge:', error);
        }
    }

    scrollToBottom() {
        const container = document.getElementById('messages-container');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    }

    formatTime(timestamp) {
        if (!timestamp) return '';
        const date = new Date(timestamp);
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    openChat(donationId) {
        this.toggleChat(true);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.chatSystem = new ChatSystem();
});
