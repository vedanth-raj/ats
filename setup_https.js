const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

console.log('🔒 Setting up HTTPS for UAS...\n');

// Create certificates directory
const certDir = path.join(__dirname, 'certificates');
if (!fs.existsSync(certDir)) {
    fs.mkdirSync(certDir);
    console.log('✅ Created certificates directory');
}

// Check if OpenSSL is available
try {
    execSync('openssl version', { stdio: 'ignore' });
    console.log('✅ OpenSSL found');
} catch (error) {
    console.log('❌ OpenSSL not found. Please install OpenSSL first.');
    console.log('   Download from: https://slproweb.com/products/Win32OpenSSL.html');
    process.exit(1);
}

// Generate self-signed certificate
try {
    console.log('🔑 Generating self-signed certificate...');
    
    const keyPath = path.join(certDir, 'server.key');
    const certPath = path.join(certDir, 'server.crt');
    
    // Generate private key
    execSync(`openssl genrsa -out "${keyPath}" 2048`, { stdio: 'inherit' });
    
    // Generate certificate
    const certCommand = `openssl req -new -x509 -key "${keyPath}" -out "${certPath}" -days 365 -subj "/C=US/ST=State/L=City/O=UAS/CN=localhost"`;
    execSync(certCommand, { stdio: 'inherit' });
    
    console.log('✅ Certificate generated successfully!');
    console.log(`   Key: ${keyPath}`);
    console.log(`   Certificate: ${certPath}`);
    
} catch (error) {
    console.error('❌ Failed to generate certificate:', error.message);
    process.exit(1);
}

// Create HTTPS server file
const httpsServerContent = `const express = require('express');
const https = require('https');
const fs = require('fs');
const path = require('path');

// Import your existing server configuration
const app = require('./server');

// HTTPS Configuration
const httpsOptions = {
    key: fs.readFileSync(path.join(__dirname, 'certificates', 'server.key')),
    cert: fs.readFileSync(path.join(__dirname, 'certificates', 'server.crt'))
};

const HTTPS_PORT = process.env.HTTPS_PORT || 3443;

// Create HTTPS server
const httpsServer = https.createServer(httpsOptions, app);

httpsServer.listen(HTTPS_PORT, '0.0.0.0', () => {
    console.log(\`🔒 HTTPS Server running on port \${HTTPS_PORT}\`);
    console.log(\`🌐 Local HTTPS access: https://localhost:\${HTTPS_PORT}\`);
    console.log(\`🌐 Network HTTPS access: https://10.180.133.44:\${HTTPS_PORT}\`);
    console.log(\`🌐 Network HTTPS access: https://192.168.56.1:\${HTTPS_PORT}\`);
    console.log('');
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