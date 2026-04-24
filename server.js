const io = require("socket.io")(3000, {
    cors: { origin: "*" }
});

io.engine.on("connection_error", (err) => {
    console.log("Socket error:", err);
});

io.on("connection", socket => {

    // Join personal user room
    socket.on("join-user", userId => {
        socket.join("user-" + userId);
        console.log("User joined:", "user-" + userId);
    });

    // Join chat room
    socket.on("join-room", room => {
        socket.join(room);
        console.log("Joined room:", room);
    });

    // OFFER
    socket.on("offer", data => {
        console.log(" OFFER RECEIVED:", data);
        if (data.targetUserId) {
            console.log(" Sending offer to user room:", `user-${data.targetUserId}`);
            socket.to(`user-${data.targetUserId}`).emit("offer", data);
            socket.to(data.room).emit("offer", data);
            return;
        }

        console.log(" Sending offer to shared room:", data.room);
        socket.to(data.room).emit("offer", data);
    });

    // ANSWER
    socket.on("answer", data => {
        console.log(" ANSWER RECEIVED:", data);

        // Send to shared room
        if (data.room) {
            console.log(" Sending answer to shared room:", data.room);
            socket.to(data.room).emit("answer", data);
        }

        // Also send to target user's personal room for reliability
        if (data.targetUserId) {
            console.log(" Sending answer to user room:", `user-${data.targetUserId}`);
            socket.to(`user-${data.targetUserId}`).emit("answer", data);
        }

        // Fallback: if callerUserId provided, send to that room too
        if (data.callerUserId) {
            console.log(" Sending answer to caller room:", `user-${data.callerUserId}`);
            socket.to(`user-${data.callerUserId}`).emit("answer", data);
        }
    });

    // ICE
    socket.on("ice-candidate", data => {
        console.log("📡 ICE CANDIDATE RECEIVED for room:", data.room);

        // Send to shared room
        if (data.room) {
            socket.to(data.room).emit("ice-candidate", data);
        }

        // Also send to target user's personal room
        if (data.targetUserId) {
            socket.to(`user-${data.targetUserId}`).emit("ice-candidate", data);
        }
    });

});