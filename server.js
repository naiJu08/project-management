const io = require("socket.io")(3000, {
    cors: { origin: "*" }
});

io.engine.on("connection_error", (err) => {
    console.log("Socket error:", err);
});

io.on("connection", socket => {

    socket.on("join-user", userId => {
        socket.join(`user-${userId}`);
    });

    socket.on("join-room", room => {
        socket.join(room);
    });

    socket.on("offer", data => {
        console.log(" OFFER RECEIVED:", data);
        if (data.targetUserId) {
            console.log(" Sending offer to user room:", `user-${data.targetUserId}`);
            socket.to(`user-${data.targetUserId}`).emit("offer", data);
            return;
        }

        console.log(" Sending offer to shared room:", data.room);
        socket.to(data.room).emit("offer", data);
    });

    socket.on("answer", data => {
        socket.to(data.room).emit("answer", data);
    });

    socket.on("ice-candidate", data => {
        socket.to(data.room).emit("ice-candidate", data);
    });

});