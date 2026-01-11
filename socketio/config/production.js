require("dotenv").config();

module.exports = {
    server: {
        port: process.env.SERVER_PORT,
        cert: process.env.SSL_CERT_PATH,
        key: process.env.SSL_KEY_PATH,
        host: process.env.SERVER_HOST,
    },
};
