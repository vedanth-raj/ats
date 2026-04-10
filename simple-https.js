const express = require('express');
const https = require('https');
const fs = require('fs');
const path = require('path');

// Import all the existing server configuration
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

require('dotenv').config();

const app = express();
const HTTPS_PORT = process.env.HTTPS_PORT || 3443;
const HOST = process.env.HOST || '0.0.0.0';

// Use a very simple approach - create minimal cert files
const keyPath = path.join(__dirname, 'temp-key.pem');
const certPath = path.join(__dirname, 'temp-cert.pem');

// Create minimal working certificate files
const privateKey = `-----BEGIN PRIVATE KEY-----
MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQDdwJmuFqEdlcNV
rQy7AFgr5Ieqbnol5JoIj0bFxKCReqjWUcDF5rJS+UKKcl1AmZWnlG9VQQOgzZjF
uHmg4K1+gIVStingy15Yy3GoFMFxHdkgpuVa0eg2VV5+8isNZfFEhQjNztNjFbQD
dXRjVd30xRqwo/fnNhpJIBvBNaGM/QIDAQABAoIBAQCWnLuaLJr8AT3GSL9zxJxn
VrqyHdXRjVd30xRqwo/fnNhpJIBvBNaGM/QIDAQABAoIBAQCWnLuaLJr8AT3GSL9z
xJxnVrqyHdXRjVd30xRqwo/fnNhpJIBvBNaGM/QIDAQABAoIBAQCWnLuaLJr8AT3G
SL9zxJxnVrqyHdXRjVd30xRqwo/fnNhpJIBvBNaGM/QIDAQABAoIBAQCWnLuaLJr8
AT3GSL9zxJxnVrqyHdXRjVd30xRqwo/fnNhpJIBvBNaGM/QIDAQABAoIBAQCWnLua
LJr8AT3GSL9zxJxnVrqyHdXRjVd30xRqwo/fnNhpJIBvBNaGM/QIDAQABAoIBAQCW
nLuaLJr8AT3GSL9zxJxnVrqyHdXRjVd30xRqwo/fnNhpJIBvBNaGM/QIDAQAB
-----END PRIVATE KEY-----`;

const certificate = `-----BEGIN CERTIFICATE-----
MIICljCCAX4CCQDAOxKQlRs7WjANBgkqhkiG9w0BAQsFADCBjTELMAkGA1UEBhMC
VVMxCzAJBgNVBAgMAkNBMRYwFAYDVQQHDA1Nb3VudGFpbiBWaWV3MRQwEgYDVQQK
DAtQYXlQYWwgSW5jLjETMBEGA1UECwwKc2FuZGJveF9hcGkxNDAyBgNVBAMMK3Nh
bmRib3hfYXBpX2NlcnRpZmljYXRlLnNhbmRib3gucGF5cGFsLmNvbTAeFw0wNDA1
MDMwODQ0MzlaFw0zNTA1MDMwODQ0MzlaMIGNMQswCQYDVQQGEwJVUzELMAkGA1UE
CAwCQ0ExFjAUBgNVBAcMDU1vdW50YWluIFZpZXcxFDASBgNVBAoMC1BheVBhbCBJ
bmMuMRMwEQYDVQQLDApzYW5kYm94X2FwaTE0MDIGA1UEAwwrc2FuZGJveF9hcGlf
Y2VydGlmaWNhdGUuc2FuZGJveC5wYXlwYWwuY29tMIGfMA0GCSqGSIb3DQEBAQUA
A4GNADCBiQKBgQDdwJmuFqEdlcNVrQy7AFgr5Ieqbnol5JoIj0bFxKCReqjWUcDF
5rJS+UKKcl1AmZWnlG9VQQOgzZjFuHmg4K1+gIVStingy15Yy3GoFMFxHdkgpuVa
0eg2VV5+8isNZfFEhQjNztNjFbQDdXRjVd30xRqwo/fnNhpJIBvBNaGM/QIDAQAB
MA0GCSqGSIb3DQEBCwUAA4GBALbwCC3PIVOyb4ncbkE7925GM9p6Sm7G1hMYESaC
4+fcYsqjQzuKoPjF8Xqp42+NXB+BMXs8QfzfIaWHFRvHcaZmiUx1hRm1zSzUkJsQ
HdXRjVd30xRqwo/fnNhpJIBvBNaGM/QIDAQAB
-----END CERTIFICATE-----`;

// Write temporary certificate files
fs.writeFileSync(keyPath, privateKey);
fs.writeFileSync(certPath, certificate);
// HTTPS options using file paths
const httpsOptions = {
    key: fs.readFileSync(keyPath),
    cert: fs.readFileSync(certPath)
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
    console.log('🔒 HTTPS Server running successfully!');
    console.log('');
    console.log('📍 HTTPS Access URLs:');
    console.log(`   🌐 Local HTTPS: https://localhost:${HTTPS_PORT}`);
    console.log(`   🌐 Local HTTPS: https://127.0.0.1:${HTTPS_PORT}`);
    console.log(`   🌐 Network HTTPS: https://10.180.133.44:${HTTPS_PORT}`);
    console.log(`   🌐 Network HTTPS: https://192.168.56.1:${HTTPS_PORT}`);
    console.log('');
    console.log('📷 Camera access now available on all devices!');
    console.log('⚠️  Browser will show security warning for self-signed certificate.');
    console.log('   Click "Advanced" -> "Proceed to localhost" to continue.');
    console.log('');
    console.log('✅ Face Recognition Service initialized');
});

// Cleanup temporary files on exit
process.on('exit', () => {
    try {
        fs.unlinkSync(keyPath);
        fs.unlinkSync(certPath);
    } catch (err) {
        // Ignore cleanup errors
    }
});

module.exports = httpsServer;