# Network Access Setup Guide

## 🌐 Running UAS on Your Network

Your Unified Attendance System is now configured to run on your local network, allowing other devices to access it.

## 📱 Network Access URLs

Based on your current network configuration, the server will be accessible at:

### Primary Network (WiFi):
- **http://10.180.133.44:3000** - Main network access
- **http://localhost:3000** - Local access only

### Secondary Network:
- **http://192.168.56.1:3000** - Alternative network access

## 🚀 How to Start the Server

1. **Stop the current server** (if running):
   ```bash
   # Press Ctrl+C in the terminal where server is running
   ```

2. **Start the server with network access**:
   ```bash
   cd unified-attendance-interface
   npm start
   ```

3. **Look for the network URLs** in the console output:
   ```
   Server running on 0.0.0.0:3000
   Local access: http://localhost:3000
   Network access: http://10.180.133.44:3000
   Network access: http://192.168.56.1:3000
   Server is accessible from other devices on your network!
   ```

## 📱 Accessing from Other Devices

### From Mobile Phones:
1. Connect your phone to the **same WiFi network**
2. Open your phone's browser
3. Go to: **http://10.180.133.44:3000**

### From Other Computers:
1. Ensure they're on the **same network**
2. Open any web browser
3. Go to: **http://10.180.133.44:3000**

### From Tablets:
1. Connect to the **same WiFi**
2. Open browser app
3. Go to: **http://10.180.133.44:3000**

## 🔧 Troubleshooting

### If devices can't connect:

1. **Check Windows Firewall**:
   ```bash
   # Run as Administrator in PowerShell:
   New-NetFirewallRule -DisplayName "UAS Server" -Direction Inbound -Protocol TCP -LocalPort 3000 -Action Allow
   ```

2. **Verify network connection**:
   - All devices must be on the same WiFi network
   - Check if your router allows device-to-device communication

3. **Test connectivity**:
   ```bash
   # From another device, ping your computer:
   ping 10.180.133.44
   ```

### If IP addresses change:
- Your IP might change when you reconnect to WiFi
- Run `ipconfig` to get the current IP address
- Update the CORS configuration if needed

## 🔒 Security Considerations

### For Production Use:
1. **Enable HTTPS** with SSL certificates
2. **Set up proper authentication** (currently using demo admin-token)
3. **Configure firewall rules** to restrict access
4. **Use environment variables** for sensitive data

### Current Security Status:
- ⚠️ **Development Mode**: Basic security only
- ⚠️ **Demo Authentication**: Admin token is "admin-token"
- ⚠️ **HTTP Only**: No encryption (fine for local network)

## 📋 Features Available on Network

All features work across the network:
- ✅ Employee Registration with Camera
- ✅ Face Recognition Attendance
- ✅ Admin Dashboard
- ✅ Employee Management
- ✅ Attendance Reports
- ✅ System Monitoring
- ✅ Real-time Updates

## 🎯 Quick Test

1. **Start the server** on your computer
2. **Get your phone** and connect to the same WiFi
3. **Open browser** on your phone
4. **Go to**: http://10.180.133.44:3000
5. **You should see** the UAS homepage!

## 📞 Support

If you encounter issues:
1. Check that all devices are on the same network
2. Verify Windows Firewall settings
3. Ensure the server is running and showing network URLs
4. Try both IP addresses if one doesn't work

---

**Note**: The IP addresses shown (10.180.133.44 and 192.168.56.1) are based on your current network configuration. They may change if you reconnect to WiFi or switch networks.