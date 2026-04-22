const express = require('express');
const { Server } = require('socket.io');
const http = require('http');
const mysql = require('mysql2/promise');
const cors = require('cors');
require('dotenv').config();

const app = express();
app.use(cors());
const server = http.createServer(app);

const io = new Server(server, {
    cors: {
        origin: '*', // Adjust to your frontend domain in production
        methods: ['GET', 'POST']
    }
});

// Database connection
const pool = mysql.createPool({
    host: process.env.DB_HOST || 'localhost',
    user: process.env.DB_USER || 'root',
    password: process.env.DB_PASSWORD || '',
    database: process.env.DB_NAME || 'foodshare',
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
});

io.on('connection', (socket) => {
    console.log(`User connected: ${socket.id}`);

    // ============================
    // 1. CHAT FUNCTIONALITY
    // ============================
    
    // Join a specific chat conversation room
    socket.on('join_chat', (conversationId) => {
        socket.join(`chat_${conversationId}`);
        console.log(`Socket ${socket.id} joined chat_${conversationId}`);
    });

    // Leave a specific chat room
    socket.on('leave_chat', (conversationId) => {
        socket.leave(`chat_${conversationId}`);
        console.log(`Socket ${socket.id} left chat_${conversationId}`);
    });

    // Handle incoming message
    socket.on('send_message', async (data) => {
        try {
            const { conversation_id, sender_id, sender_name, message, is_bot } = data;
            
            // Validate input
            if(!conversation_id || !message) {
                return socket.emit('error', 'Missing required fields');
            }

            const isBotVal = is_bot ? 1 : 0;
            const senderIdVal = sender_id || null;

            // Save to database
            const [result] = await pool.execute(
                `INSERT INTO chat_messages (conversation_id, sender_id, sender_name, message, is_bot) VALUES (?, ?, ?, ?, ?)`,
                [conversation_id, senderIdVal, sender_name, message, isBotVal]
            );

            // Fetch the inserted timestamp or generate now
            const now = new Date().toISOString(); 
            // In a strict implementation we might SELECT to get exact timestamp
            const msgObj = {
                id: result.insertId,
                conversation_id,
                sender_id: senderIdVal,
                sender_name,
                message,
                is_bot: isBotVal,
                created_at: now
            };

            // Update conversation last_message
            await pool.execute(
                `UPDATE conversations SET last_message = ?, last_message_at = NOW() WHERE id = ?`,
                [message.substring(0, 100), conversation_id]
            );
            
            // Increment unread count for other participants
            await pool.execute(
                `UPDATE chat_participants SET unread_count = unread_count + 1 WHERE conversation_id = ? AND user_id != ?`,
                [conversation_id, senderIdVal]
            );

            // Broadcast to the specific chat room
            io.to(`chat_${conversation_id}`).emit('new_message', msgObj);
            
            // Also notify globally for the recipient badge update (optional simple implementation)
            socket.broadcast.emit('global_chat_update', { conversation_id, message });

        } catch (error) {
            console.error('Error saving message:', error);
            socket.emit('error', 'Failed to send message');
        }
    });

    // Mark messages as read
    socket.on('mark_read', async (data) => {
        try {
            const { conversation_id, user_id } = data;
            await pool.execute(
                `UPDATE chat_participants SET unread_count = 0, last_read_at = NOW() WHERE conversation_id = ? AND user_id = ?`,
                [conversation_id, user_id]
            );
            // Could also mark specific messages in 'chat_messages' if that was required
        } catch (error) {
            console.error('Error marking read:', error);
        }
    });


    // ============================
    // 2. LIVE TRACKING FUNCTIONALITY
    // ============================

    // Join tracking room
    socket.on('join_tracking', (trackingId) => {
        socket.join(`tracking_${trackingId}`);
        console.log(`Socket ${socket.id} joined tracking_${trackingId}`);
    });

    socket.on('leave_tracking', (trackingId) => {
        socket.leave(`tracking_${trackingId}`);
    });

    // Update Live Location
    socket.on('update_location', async (data) => {
        try {
            const { tracking_id, latitude, longitude, accuracy, speed, heading } = data;
            
            // Save to database
            await pool.execute(
                `INSERT INTO location_updates (tracking_id, latitude, longitude, accuracy, speed, heading) VALUES (?, ?, ?, ?, ?, ?)`,
                [tracking_id, latitude, longitude, accuracy || 0, speed || 0, heading || 0]
            );

            // Broadcast to everyone in the tracking room
            io.to(`tracking_${tracking_id}`).emit('location_updated', {
                latitude, longitude, speed, heading
            });
            
        } catch (error) {
            console.error('Error saving location:', error);
        }
    });

    socket.on('disconnect', () => {
        console.log(`User disconnected: ${socket.id}`);
    });
});

const PORT = process.env.PORT || 3000;
server.listen(PORT, () => {
    console.log(`Real-time server running on port ${PORT}`);
});
