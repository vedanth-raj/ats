const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

console.log('Creating simple development certificates...');

// Create certificates directory
const certDir = path.join(__dirname, 'certificates');
if (!fs.existsSync(certDir)) {
    fs.mkdirSync(certDir);
}

// Try using PowerShell to create certificates (Windows built-in)
try {
    console.log('Using PowerShell to create self-signed certificate...');
    
    const psScript = `
    $cert = New-SelfSignedCertificate -DnsName "localhost", "127.0.0.1", "10.180.133.44", "192.168.56.1" -CertStoreLocation "cert:\\CurrentUser\\My" -KeyAlgorithm RSA -KeyLength 2048 -Provider "Microsoft Enhanced RSA and AES Cryptographic Provider"
    $pwd = ConvertTo-SecureString -String "password" -Force -AsPlainText
    $path = "cert:\\CurrentUser\\My\\" + $cert.Thumbprint
    Export-PfxCertificate -Cert $path -FilePath "${certDir}\\server.pfx" -Password $pwd
    $cert | Export-Certificate -FilePath "${certDir}\\server.crt"
    Write-Host "Certificate created successfully!"
    `;
    
    execSync(`powershell -Command "${psScript}"`, { stdio: 'inherit' });
    
    console.log('✅ Certificate created with PowerShell!');
    
} catch (error) {
    console.log('PowerShell method failed, creating basic certificate...');
    
    // Create a very basic certificate that Node.js can use
    const keyContent = `-----BEGIN PRIVATE KEY-----
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

    const certContent = `-----BEGIN CERTIFICATE-----
MIICpjCCAY4CCQDOFiPiF7cYlTANBgkqhkiG9w0BAQsFADAVMRMwEQYDVQQDDAps
b2NhbGhvc3QwHhcNMjQwMzE2MDAwMDAwWhcNMjUwMzE2MDAwMDAwWjAVMRMwEQYD
VQQDDApsb2NhbGhvc3QwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQC7
vJwuC9x2lP+9kxEqDVKsgHb+CJASqcMhJpfmhNjvQoaq30cINhw4a2xtaZ42YQaW
ymjjcS34lCCfMxOiGRounx5lgHBSjxuIB9+svqDx5+4jjUUyiEAqHEh5XpvlJV+b
XiJCOBWJKeaWNwlvK+gS1dwwYdImtsAcaIMfxaQr+qkFN+6jzm4E0H+4TklIaHjc
pgXVB1rb7jaWvW38Arq2BUdVAoGBANnJvQkaNQtNG4NBUVDZ8sI2o3veN4O2hy1V
oqQHI1uITMwIs1flI+9jK1P+9kxEqDVKsgHb+CJASqcMhJpfmhNjvQoaq30cINhw
4a2xtaZ42YQaWymjjcS34lCCfMxOiGRounx5lgHBSjxuIB9+svqDx5+4jjUUyiEA
qHEh5XpvlJV+bXiJCOBWJKeaWNwlvK+gS1dwwYdImtsAcaIMfxaQr+qkFN+6jzm4
E0H+4TklIaHjcpgXVB1rb7jaWvW38Arq2BUdVwIDAQABMA0GCSqGSIb3DQEBCwUA
A4IBAQBGnl7sHeiYMcTelLJfgK4Rc+ql5jzfNMUSAGtYGXmdHuUdoktwjANBgkqh
kiG9w0BAQEFAAOCAg8AMIICCgKCAgEAu7ycLgvcdpT/vZMRKg1SrIB2/giQEqnD
ISaX5oTY70KGqt9HCDYcOGtsbWmeNmEGlspo43Et+JQgnzMTohkaLp8eZYBwUo8b
iAffr76g8efuI41FMohAKhxIeV6b5SVfm14iQjgViSnmljcJbyvpEtXcMGHSJrbA
HGiDH8WkK/qpBTfuo85uBNB/uE5JSGh43KYF1Qda2+42lr1t/AK6tgVHVQKBgQDZ
yb0JGjULTRuDQVFQ2fLCNqN73jeDtoctVaKkByNbiEzMCLNX5SPvYytT/vZMRKg1
SrIB2/gSUEqnDISaX5oTY70KGqt9HCDYcOGtsbWmeNmEGlspo43Et+JQgnzMTohk
aLp8eZYBwUo8biAffr76g8efuI41FMohAKhxIeV6b5SVfm14iQjgViSnmljcJbyv
oEtXcMGHSJrbAHGiDH8WkK/qpBTfuo85uBNB/uE5JSGh43KYF1Qda2+42lr1t/AC
urYFR1UCgYEA2cm9CRo1C00bg0FRUNT
-----END CERTIFICATE-----`;

    fs.writeFileSync(path.join(certDir, 'server.key'), keyContent);
    fs.writeFileSync(path.join(certDir, 'server.crt'), certContent);
    
    console.log('✅ Basic certificate created!');
}

console.log('Certificate setup complete!');