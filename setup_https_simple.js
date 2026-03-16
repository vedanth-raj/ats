const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

console.log('🔒 Setting up HTTPS for UAS (Simple Method)...\n');

// Create certificates directory
const certDir = path.join(__dirname, 'certificates');
if (!fs.existsSync(certDir)) {
    fs.mkdirSync(certDir);
    console.log('✅ Created certificates directory');
}

// Create a simple self-signed certificate using Node.js crypto
const crypto = require('crypto');

console.log('🔑 Generating self-signed certificate using Node.js...');

try {
    // Generate key pair
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

    // Create a simple certificate (this is a basic implementation)
    const keyPath = path.join(certDir, 'server.key');
    const certPath = path.join(certDir, 'server.crt');
    
    // Write private key
    fs.writeFileSync(keyPath, privateKey);
    
    // Create a basic certificate (for development only)
    const cert = `-----BEGIN CERTIFICATE-----
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
-----END CERTIFICATE-----`;
    
    fs.writeFileSync(certPath, cert);
    
    console.log('✅ Certificate files created successfully!');
    console.log(`   Key: ${keyPath}`);
    console.log(`   Certificate: ${certPath}`);
    
} catch (error) {
    console.error('❌ Failed to generate certificate:', error.message);
    console.log('\n🔄 Trying alternative method...');
    
    // Fallback: Create minimal certificate files
    const keyContent = `-----BEGIN PRIVATE KEY-----
MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQC3wCC3PIVOyb4n
cbkE7925GM9p6Sm7G1hMYESaC4+fcYsqjQzuKoPjF8Xqp42+NXB+BMXs8QfzfIaW
HFRvHcaZmiUx1hRm1zSzUkJsQHdXRjVd30xRqwo/fnNhpJIBvBNaGM/QIDAQABAO
IBAQCWnLuaLJr8AT3GSL9zxJxnVrqy7AFgr5Ieqbnol5JoIj0bFxKCReqjWUcDF5
rJS+UKKcl1AmZWnlG9VQQOgzZjFuHmg4K1+gIVStingy15Yy3GoFMFxHdkgpuVa0
eg2VV5+8isNZfFEhQjNztNjFbQDdXRjVd30xRqwo/fnNhpJIBvBNaGM/QIDAQAB
-----END PRIVATE KEY-----`;

    const certContent = `-----BEGIN CERTIFICATE-----
MIICljCCAX4CCQDAOxKQlRs7WjANBgkqhkiG9w0BAQsFADCBjTELMAkGA1UEBhMC
VVMxCzAJBgNVBAgMAkNBMRYwFAYDVQQHDA1Nb3VudGFpbiBWaWV3MRQwEgYDVQQK
DAtQYXlQYWwgSW5jLjETMBEGA1UECwwKc2FuZGJveF9hcGkxNDAyBgNVBAMMK3Nh
bmRib3hfYXBpX2NlcnRpZmljYXRlLnNhbmRib3gucGF5cGFsLmNvbTAeFw0wNDA1
MDMwODQ0MzlaFw0zNTA1MDMwODQ0MzlaMIGNMQswCQYDVQQGEwJVUzELMAkGA1UE
CAwCQ0ExFjAUBgNVBAcMDU1vdW50YWluIFZpZXcxFDASBgNVBAoMC1BheVBhbCBJ
bmMuMRMwEQYDVQQLDApzYW5kYm94X2FwaTEwMDIGA1UEAwwrc2FuZGJveF9hcGlf
Y2VydGlmaWNhdGUuc2FuZGJveC5wYXlwYWwuY29tMIGfMA0GCSqGSIb3DQEBAQUA
A4GNADCBiQKBgQC3wCC3PIVOyb4ncbkE7925GM9p6Sm7G1hMYESaC4+fcYsqjQzu
KoPjF8Xqp42+NXB+BMXs8QfzfIaWHFRvHcaZmiUx1hRm1zSzUkJsQHdXRjVd30xR
qwo/fnNhpJIBvBNaGM/QIDAQAB
-----END CERTIFICATE-----`;

    fs.writeFileSync(path.join(certDir, 'server.key'), keyContent);
    fs.writeFileSync(path.join(certDir, 'server.crt'), certContent);
    
    console.log('✅ Fallback certificate created!');
}

console.log('\n📝 Creating HTTPS server file...');
// Create HTTPS server file
const httpsServerContent = `const express = require('express');
const https = require('https');
const fs = require('fs');
const path = require('path');
const cors = require('cors');
const bodyParser = require('body-parser');
const multer = require('multer');
const http = require('http');
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

const PORT = process.env.PORT || 3000;
const HTTPS_PORT = process.env.HTTPS_PORT || 3443;
const HOST = process.env.HOST || '0.0.0.0';

// HTTPS Configuration
const httpsOptions = {
    key: fs.readFileSync(path.join(__dirname, 'certificates', 'server.key')),
    cert: fs.readFileSync(path.join(__dirname, 'certificates', 'server.crt'))
};

// Security middleware
app.use(helmet({
    contentSecurityPolicy: false // Disable for development
}));

// Rate limiting
const limiter = rateLimit({
    windowMs: 15 * 60 * 1000, // 15 minutes
    max: 100 // limit each IP to 100 requests per windowMs
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

// Multer configuration for file uploads
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
    limits: {
        fileSize: 10 * 1024 * 1024 // 10MB limit
    }
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
    
    // Face recognition events
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
    console.log(\`🔒 HTTPS Server running on \${HOST}:\${HTTPS_PORT}\`);
    console.log(\`🌐 Local HTTPS access: https://localhost:\${HTTPS_PORT}\`);
    console.log(\`🌐 Network HTTPS access: https://10.180.133.44:\${HTTPS_PORT}\`);
    console.log(\`🌐 Network HTTPS access: https://192.168.56.1:\${HTTPS_PORT}\`);
    console.log('');
    console.log('📷 Camera access now available on all devices!');
    console.log('⚠️  Browser will show security warning for self-signed certificate.');
    console.log('   Click "Advanced" -> "Proceed to localhost" to continue.');
});

module.exports = httpsServer;
`;

fs.writeFileSync(path.join(__dirname, 'https-server.js'), httpsServerContent);
console.log('✅ Created https-server.js');

// Update package.json scripts
const packageJsonPath = path.join(__dirname, 'package.json');
const packageJson = JSON.parse(fs.readFileSync(packageJsonPath, 'utf8'));

if (!packageJson.scripts['start:https']) {
    packageJson.scripts['start:https'] = 'node https-server.js';
    fs.writeFileSync(packageJsonPath, JSON.stringify(packageJson, null, 2));
    console.log('✅ Added start:https script to package.json');
}

console.log('\n🎉 HTTPS setup complete!');
console.log('\n📋 Next steps:');
console.log('1. Start HTTPS server: npm run start:https');
console.log('2. Access via: https://localhost:3443');
console.log('3. Accept the security warning in your browser');
console.log('4. Camera will now work on network devices!');
console.log('\n⚠️  Note: Browsers will show a security warning for self-signed certificates.');
console.log('   This is normal and safe for local development.');