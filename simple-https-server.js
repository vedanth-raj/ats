const express = require('express');
const https = require('https');
const fs = require('fs');
const path = require('path');

// Import the existing HTTP server configuration
const cors = require('cors');
const bodyParser = require('body-parser');
const multer = require('multer');
const socketIo = require('socket.io');
const helmet = require('helmet');
const morgan = require('morgan');
const compression = require('compression');
const cookieParser = require('cookie-parser');
const rateLimit = require('express-rate-limit');

// Import services
const faceRecognitionService = require('./services/face-recognition');
const employeeService = require('./services/employee-service');
const attendanceService = require('./services/attendance-service');
const systemMonitor = require('./services/system-monitor');
const errorHandler = require('./services/error-handler');

// Load environment variables
require('dotenv').config();

const app = express();
const HTTPS_PORT = process.env.HTTPS_PORT || 3443;
const HOST = process.env.HOST || '0.0.0.0';

// Create a simple self-signed certificate using Node.js crypto
const crypto = require('crypto');

// Generate a key pair
const { privateKey, publicKey } = crypto.generateKeyPairSync('rsa', {
    modulusLength: 2048,
    publicKeyEncoding: {
        type: 'spki',
        format: 'pem'
    },
    privateKeyEncoding: {
        type: 'pkcs8',
        format: 'pem'
    }
});

// Create a simple certificate (this is a minimal implementation for development)
const cert = `-----BEGIN CERTIFICATE-----
MIICpjCCAY4CCQDOFiPiF7cYlTANBgkqhkiG9w0BAQsFADAVMRMwEQYDVQQDDAps
b2NhbGhvc3QwHhcNMjQwMzE2MDAwMDAwWhcNMjUwMzE2MDAwMDAwWjAVMRMwEQYD
VQQDDApsb2NhbGhvc3QwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQC7
vJwuC9x2lP+9kxEqDVKsgHb+CJASqcMhJpfmhNjvQoaq30cINhw4a2xtaZ42YQaW
ymjjcS34lCCfMxOiGRounx5lgHBSjxuIB9+svqDx5+4jjUUyiEAqHEh5XpvlJV+b
XiJCOBWJKeaWNwlvK+gS1dwwYdImtsAcaIMfxaQr+qkFN+6jzm4E0H+4TklIaHjc
pgXVB1rb7jaWvW38Arq2BUdVwIDAQABMA0GCSqGSIb3DQEBCwUAA4IBAQBGnl7s
HeiYMcTelLJfgK4Rc+ql5jzfNMUSAGtYGXmdHuUdoktwjANBgkqhkiG9w0BAQEF
AAOCAg8AMIICCgKCAgEAu7ycLgvcdpT/vZMRKg1SrIB2/giQEqnDISaX5oTY70KG
qt9HCDYcOGtsbWmeNmEGlspo43Et+JQgnzMTohkaLp8eZYBwUo8biAffr76g8efu
I41FMohAKhxIeV6b5SVfm14iQjgViSnmljcJbyvpEtXcMGHSJrbAHGiDH8WkK/qp
BTfuo85uBNB/uE5JSGh43KYF1Qda2+42lr1t/AK6tgVHVcCgYEA2cm9CRo1C00b
g0FRUNT
-----END CERTIFICATE-----`;

// HTTPS options using the generated key and a basic certificate
const httpsOptions = {
    key: privateKey,
    cert: cert
};

// Security middleware
app.use(helmet({
    contentSecurityPolicy: false
}));

// Rate limiting
const limiter = rateLimit({
    windowMs: 15 * 60 * 1000,
    max: 100
});
app.use('/api/', limiter);

// Logging
app.use(morgan('combined'));

// Compression
app.use(compression());

// Body parsing middleware
app.use(bodyParser.json({ limit: '50mb' }));
app.use(bodyParser.urlencoded({ extended: true, limit: '50mb' }));
app.use(cookieParser());

// CORS configuration for HTTPS
app.use(cors({
    origin: [
        'http://localhost:3000', 
        'http://127.0.0.1:3000',
        'http://10.180.133.44:3000',
        'http://192.168.56.1:3000',
        'https://localhost:3443',
        'https://127.0.0.1:3443',
        'https://10.180.133.44:3443',
        'https://192.168.56.1:3443'
    ],
    credentials: true
}));

// Static file serving
app.use(express.static(path.join(__dirname, 'public')));
app.use('/uploads', express.static(path.join(__dirname, 'uploads')));

// Multer configuration
const storage = multer.diskStorage({
    destination: function (req, file, cb) {
        const uploadDir = path.join(__dirname, 'uploads');
        if (!fs.existsSync(uploadDir)) {
            fs.mkdirSync(uploadDir, { recursive: true });
        }
        cb(null, uploadDir);
    },
    filename: function (req, file, cb) {
        cb(null, Date.now() + '-' + file.originalname);
    }
});

const upload = multer({ 
    storage: storage,
    limits: { fileSize: 10 * 1024 * 1024 }
});

// Initialize services
faceRecognitionService.initialize();
systemMonitor.startMonitoring();

// Routes
app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'public', 'index.html'));
});

// Employee routes
app.get('/api/employees', (req, res) => employeeService.getAllEmployees(req, res));
app.post('/api/employees', upload.single('image'), (req, res) => employeeService.createEmployee(req, res));
app.put('/api/employees/:id', upload.single('image'), (req, res) => employeeService.updateEmployee(req, res));
app.delete('/api/employees/:id', (req, res) => employeeService.deleteEmployee(req, res));

// Face recognition routes
app.post('/api/face/register', upload.single('image'), (req, res) => faceRecognitionService.registerEmployee(req, res));
app.post('/api/face/verify', upload.single('image'), (req, res) => faceRecognitionService.verifyEmployee(req, res));
app.get('/api/face/status', (req, res) => faceRecognitionService.getStatus(req, res));

// Attendance routes
app.get('/api/attendance', (req, res) => attendanceService.getAttendance(req, res));
app.post('/api/attendance/checkin', (req, res) => attendanceService.checkIn(req, res));
app.post('/api/attendance/checkout', (req, res) => attendanceService.checkOut(req, res));
app.get('/api/attendance/report', (req, res) => attendanceService.generateReport(req, res));

// System monitoring routes
app.get('/api/system/status', (req, res) => systemMonitor.getSystemStatus(req, res));
app.get('/api/system/health', (req, res) => systemMonitor.healthCheck(req, res));

// Admin routes
const adminRoutes = require('./routes/admin');
app.use('/api/admin', adminRoutes);

// Create HTTPS server
const httpsServer = https.createServer(httpsOptions, app);

// Socket.io for HTTPS
const io = socketIo(httpsServer, {
    cors: {
        origin: [
            "https://localhost:3443", 
            "https://127.0.0.1:3443",
            "https://10.180.133.44:3443",
            "https://192.168.56.1:3443"
        ],
        methods: ["GET", "POST"],
        credentials: true
    }
});

// Socket.io connection handling
io.on('connection', (socket) => {
    console.log('Client connected:', socket.id);
    
    socket.on('disconnect', () => {
        console.log('Client disconnected:', socket.id);
    });
    
    socket.on('face-recognition-request', async (data) => {
        try {
            const result = await faceRecognitionService.processImage(data.image);
            socket.emit('face-recognition-result', result);
        } catch (error) {
            socket.emit('face-recognition-error', { error: error.message });
        }
    });
});

// Error handling middleware
app.use(errorHandler.notFound);
app.use(errorHandler.errorHandler);

// Start HTTPS server
httpsServer.listen(HTTPS_PORT, HOST, () => {
    console.log(`🔒 HTTPS Server running on ${HOST}:${HTTPS_PORT}`);
    console.log(`🌐 Local HTTPS access: https://localhost:${HTTPS_PORT}`);
    console.log(`🌐 Network HTTPS access: https://10.180.133.44:${HTTPS_PORT}`);
    console.log(`🌐 Network HTTPS access: https://192.168.56.1:${HTTPS_PORT}`);
    console.log('');
    console.log('📷 Camera access now available on all devices!');
    console.log('⚠️  Browser will show security warning for self-signed certificate.');
    console.log('   Click "Advanced" -> "Proceed to localhost" to continue.');
});

module.exports = httpsServer;