require("dotenv").config();
var Echo = require("laravel-echo-server");

/**
 * The Laravel Echo Server options.
 */

var options = {
    "authHost": process.env.ECHO_AUTH_HOST,
    "authEndpoint": process.env.ECHO_AUTH_ENDPOINT,
    "clients": [],
    "database": process.env.ECHO_DATABASE,
    "databaseConfig": {
        "redis": process.env.REDIS_URL,
    },
    "devMode": process.env.ECHO_DEV_MODE,
    "host": process.env.ECHO_HOST,
    "port": process.env.ECHO_PORT,
    "protocol": process.env.ECHO_PROTOCOL,
    "socketio": {},
    "secureOptions": 67108864,
    "sslCertPath": "",
    "sslKeyPath": "",
    "sslCertChainPath": "",
    "sslPassphrase": "",
    "subscribers": {
        "http": true,
        "redis": true
    },
    "apiOriginAllow": {
        "allowCors": true,
        "allowOrigin": "*",
        "allowMethods": "GET, POST",
        "allowHeaders": "Origin, Content-Type, X-Auth-Token, X-Requested-With, Accept, Authorization, X-CSRF-TOKEN, X-Socket-Id"
    }
};

/**
 * Run the Laravel Echo Server.
 */
Echo.run(options);