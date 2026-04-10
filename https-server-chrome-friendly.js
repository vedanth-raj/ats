const express = require('express');
const https = require('https');
const fs = require('fs');
const path = require('path');
const selfsigned = require('selfsigned');

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

// Generate Chrome-friendly self-signed certificate
const attrs = [
    { name: 'commonName', value: 'localhost' },
    { name: 'countryName', value: 'US' },
    { name: 'stateOrProvinceName', value: 'CA' },
    { name: 'localityName', value: 'San Francisco' },
    { name: 'organizationName', value: 'Development' },
    { name: 'organizationalUnitName', value: 'IT' }
];

const pems = selfsigned.generate(attrs, { 
    keySize: 2048, 
    days: 365,
    algorithm: 'sha256',
    extensions: [{
        name: 'basicConstraints',
        cA: true
    }, {
        name: 'keyUsage',
        keyCertSign: true,
        digitalSignature: true,
        nonRepudiation: true,
        keyEncipherment: true,
        dataEncipherment: true
    }, {
        name: 'extKeyUsage',
        serverAuth: true,
        clientAuth: true,
        codeSigning: true,
        emailProtection: true,
        timeStamping: true
    }, {
        name: 'subjectAltName',
        altNames: [{
            type: 2, // DNS
            value: 'localhost'
        }, {
            type: 2, // DNS  
            value: '*.localhost'
        }, {
            type: 7, // IP
            ip: '127.0.0.1'
        }, {
            type: 7, // IP
            ip: '10.180.133.44'
        }, {
            type: 7, // IP
            ip: '192.168.56.1'
        }, {
            type: 7, // IP
            ip: '0.0.0.0'
        }]
    }]
});

// HTTPS options
const httpsOptions = {
    key: pems.private,
    cert: pems.cert
};
// Security middleware - more permissive for development
app.use(helmet({
    contentSecurityPolicy: false,
    crossOriginEmbedderPolicy: false,
    crossOriginOpenerPolicy: false,
    crossOriginResourcePolicy: false,
    hsts: false
}));

// Rate limiting
const limiter = rateLimit({
    windowMs: 15 * 60 * 1000,
    max: 1000 // More permissive for development
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

// More permissive CORS configuration
app.use(cors({
    origin: true, // Allow all origins for development
    credentials: true,
    methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    allowedHeaders: ['Content-Type', 'Authorization', 'X-Requested-With']
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

// Socket.io for HTTPS with permissive CORS
const io = socketIo(httpsServer, {
    cors: {
        origin: true, // Allow all origins
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
    console.log('🔒 Chrome-Friendly HTTPS Server running!');
    console.log('');
    console.log('📍 HTTPS Access URLs:');
    console.log(`   🌐 Local HTTPS: https://localhost:${HTTPS_PORT}`);
    console.log(`   🌐 Local HTTPS: https://127.0.0.1:${HTTPS_PORT}`);
    console.log(`   🌐 Network HTTPS: https://10.180.133.44:${HTTPS_PORT}`);
    console.log(`   🌐 Network HTTPS: https://192.168.56.1:${HTTPS_PORT}`);
    console.log('');
    console.log('🚀 Quick Access Instructions:');
    console.log('   1. Open browser and go to: https://localhost:3443');
    console.log('   2. Click "Advanced" when you see security warning');
    console.log('   3. Click "Proceed to localhost (unsafe)"');
    console.log('   4. Or run chrome-fix.bat for automatic bypass');
    console.log('');
    console.log('📷 Camera access enabled for face recognition!');
    console.log('✅ Face Recognition Service initialized');
});

module.exports = httpsServer;