const express = require("express");
const app = express();
const fs = require("fs");
const productionData = require("./config/production.js");

const cert = productionData.server.cert;
const key = productionData.server.key;

const options = {
    cert: fs.readFileSync(cert),
    key: fs.readFileSync(key),
};

const server = require("https").createServer(options, app);
const io = require("socket.io")(server, {
    cors: {
        methods: ["GET", "PATCH", "POST", "PUT"],
        origin: true,
        credentials: true,
        transports: ["websocket", "polling"],
    },
});

const redis = require("redis");
var redisClient = redis.createClient();
const redisPublisher = redis.createClient();


redisClient.subscribe("socketio-channel");
io.on("connection", (socket) => {

    // 🔥 JOIN ORDER ROOM
    socket.on("join-order-room", (data) => {
        if (typeof data === "string") {
            data = JSON.parse(data);
        }
        const room = `order-${data.orderId}`;
        socket.join(room);
        console.log(`Socket ${socket.id} joined ${room}`);
    });

    socket.on("leave-order-room", (data) => {
        const room = `order-${data.orderId}`;
        socket.leave(room);
        console.log(`Socket ${socket.id} left ${room}`);
    });

    socket.on('order-location-update',(data) => {
        if (typeof data === "string") {
            data = JSON.parse(data);
        }
        const { orderId, lat, lng } = data;
        const room = `order-${orderId}`;
        io.to(room).emit('order-location', data);

        const payload = {
            event: "order-location-update",
            data: { orderId, lat, lng }
        };

        redisPublisher.publish(
            "aaleyat-node-publisher",
            JSON.stringify(payload)
        );
    });

    socket.on("typing", (data) => {

        if (typeof data === "string") {
            data = JSON.parse(data);
        }
        const { orderId, user_type, typing } = data;
        const room = `order-${orderId}`;

        io.to(room).emit("typing", {
            user_type,
            typing,
        });
    });

    socket.on('message-read',(data) => {
        if (typeof data === "string") data = JSON.parse(data);
        const { orderId, messageId, reader_type } = data;
        const room = `order-${orderId}`;
        io.to(room).emit('message-read', data);
    });

    socket.on("disconnect", function () {
        console.log("Client disconnected");
    });
});

redisClient.on("message", (channel, message) => {
    try {
        const { event, data = {}, room } = JSON.parse(message);

        if (!event) {
            console.warn("Redis payload missing event:", message);
            return;
        }

        if (room && typeof room === "string" && room.trim()) {
            io.to(room).emit(event, data);
            console.log(`Received ${event}`);
            return;
        }

        console.log(`Received ${event}`);
        // Otherwise, emit globally
        io.emit(event, data);

    } catch (err) {
        console.error("[Redis] Invalid message:", message, err);
    }
});


const host = productionData.server.host;
const port = productionData.server.port;

server.listen(port, host, () => {
    console.log(`Server running at https://${host}:${port}`);
});
