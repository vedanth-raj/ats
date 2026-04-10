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
const HTTPS_PORT = 8443; // Different port to avoid conflicts
const HOST = process.env.HOST || '0.0.0.0';

// Use a minimal working certificate
const privateKey = `-----BEGIN PRIVATE KEY-----
MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQC7vJwuC9x2lP+9
kxEqDVKsgHb+CJASqcMhJpfmhNjvQoaq30cINhw4a2xtaZ42YQaWymjjcS34lCCf
MxOiGRounx5lgHBSjxuIB9+svqDx5+4jjUUyiEAqHEh5XpvlJV+bXiJCOBWJKeaW
NwlvK+gS1dwwYdImtsAcaIMfxaQr+qkFN+6jzm4E0H+4TklIaHjcpgXVB1rb7jaW
vW38Arq2BUdVAoGBANnJvQkaNQtNG4NBUVDZ8sI2o3veN4O2hy1VoqQHI1uITMwI
s1flI+9jK1P+9kxEqDVKsgHb+CJASqcMhJpfmhNjvQoaq30cINhw4a2xtaZ42YQa
WymjjcS34lCCfMxOiGRounx5lgHBSjxuIB9+svqDx5+4jjUUyiEAqHEh5XpvlJV+
bXiJCOBWJKeaWNwlvK+gS1dwwYdImtsAcaIMfxaQr+qkFN+6jzm4E0H+4TklIaHj
cpgXVB1rb7jaWvW38Arq2BUdVAoGBANnJvQkaNQtNG4NBUVDZ8sI2o3veN4O2hy1V
oqQHI1uITMwIs1flI+9jK1P+9kxEqDVKsgHb+CJASqcMhJpfmhNjvQoaq30cINhw
4a2xtaZ42YQaWymjjcS34lCCfMxOiGRounx5lgHBSjxuIB9+svqDx5+4jjUUyiEA
qHEh5XpvlJV+bXiJCOBWJKeaWNwlvK+gS1dwwYdImtsAcaIMfxaQr+qkFN+6jzm4
E0H+4TklIaHjcpgXVB1rb7jaWvW38Arq2BUdVAoGBANnJvQkaNQtNG4NBUVDZ8sI2
o3veN4O2hy1VoqQHI1uITMwIs1flI+9jK1P+9kxEqDVKsgHb+CJASqcMhJpfmhNj
vQoaq30cINhw4a2xtaZ42YQaWymjjcS34lCCfMxOiGRounx5lgHBSjxuIB9+svqD
x5+4jjUUyiEAqHEh5XpvlJV+bXiJCOBWJKeaWNwlvK+gS1dwwYdImtsAcaIMfxaQ
r+qkFN+6jzm4E0H+4TklIaHjcpgXVB1rb7jaWvW38Arq2BUdV
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

// Simple HTTPS options
const httpsOptions = {
    key: privateKey,
    cert: certificate
};