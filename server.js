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
        if (data.targetUserId) {
            socket.to(`user-${data.targetUserId}`).emit("offer", data);
            return;
        }

        socket.to(data.room).emit("offer", data);
    });

    socket.on("answer", data => {
        socket.to(data.room).emit("answer", data);
    });

    socket.on("ice-candidate", data => {
        socket.to(data.room).emit("ice-candidate", data);
    });

});