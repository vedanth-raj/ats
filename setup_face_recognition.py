#!/usr/bin/env python3
"""
Setup script for Face Recognition dependencies
This script helps install the required Python libraries for face recognition
"""

import subprocess
import sys
import os

def run_command(command):
    """Run a command and return success status"""
    try:
        result = subprocess.run(command, shell=True, check=True, capture_output=True, text=True)
        print(f"✓ {command}")
        return True
    except subprocess.CalledProcessError as e:
        print(f"✗ {command}")
        print(f"Error: {e.stderr}")
        return False

def check_python():
    """Check if Python is available"""
    try:
        version = sys.version_info
        if version.major >= 3 and version.minor >= 7:
            print(f"✓ Python {version.major}.{version.minor}.{version.micro} found")
            return True
        else:
            print(f"✗ Python 3.7+ required, found {version.major}.{version.minor}.{version.micro}")
            return False
    except Exception as e:
        print(f"✗ Python check failed: {e}")
        return False

def install_dependencies():
    """Install Python dependencies"""
    print("\n📦 Installing Python dependencies...")
    
    # Check if pip is available
    if not run_command("python -m pip --version"):
        print("✗ pip not found. Please install pip first.")
        return False
    
    # Install dependencies
    requirements_file = os.path.join(os.path.dirname(__file__), "python", "requirements.txt")
    
    if os.path.exists(requirements_file):
        return run_command(f"python -m pip install -r {requirements_file}")
    else:
        # Install individual packages
        packages = [
            "face_recognition",
            "opencv-python",
            "numpy",
            "Pillow"
        ]
        
        success = True
        for package in packages:
            if not run_command(f"python -m pip install {package}"):
                success = False
        
        return success

def test_installation():
    """Test if face recognition is working"""
    print("\n🧪 Testing face recognition installation...")
    
    test_script = '''
import face_recognition
import cv2
import numpy as np
print("✓ All face recognition libraries imported successfully")
print("✓ Face recognition setup complete!")
'''
    
    try:
        result = subprocess.run([sys.executable, "-c", test_script], 
                              capture_output=True, text=True, check=True)
        print(result.stdout)
        return True
    except subprocess.CalledProcessError as e:
        print("✗ Face recognition test failed:")
        print(e.stderr)
        return False

def main():
    print("🚀 Face Recognition Setup for Unified Attendance System")
    print("=" * 60)
    
    # Check Python version
    if not check_python():
        print("\n❌ Setup failed: Python 3.7+ is required")
        sys.exit(1)
    
    # Install dependencies
    if not install_dependencies():
        print("\n❌ Setup failed: Could not install dependencies")
        print("\n💡 Try installing manually:")
        print("   pip install face_recognition opencv-python numpy Pillow")
        sys.exit(1)
    
    # Test installation
    if not test_installation():
        print("\n⚠️  Installation completed but testing failed")
        print("   Face recognition may not work properly")
        sys.exit(1)
    
    print("\n✅ Face recognition setup completed successfully!")
    print("\n📋 Next steps:")
    print("   1. Restart the Node.js server")
    print("   2. Try registering an employee with a photo")
    print("   3. Test face recognition attendance")

if __name__ == "__main__":
    main()