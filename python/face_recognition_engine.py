#!/usr/bin/env python3
"""
Face Recognition Engine for Unified Attendance System
Handles face registration and verification using face_recognition library
Supports multiple photos for better training accuracy
"""

import sys
import json
import os
import pickle
import face_recognition
import numpy as np
from pathlib import Path

class FaceRecognitionEngine:
    def __init__(self):
        self.encodings_file = Path(__file__).parent / "face_encodings.pkl"
        self.face_encodings = {}
        self.load_encodings()
    
    def load_encodings(self):
        """Load existing face encodings from file"""
        if self.encodings_file.exists():
            try:
                with open(self.encodings_file, 'rb') as f:
                    self.face_encodings = pickle.load(f)
            except Exception as e:
                print(f"Error loading encodings: {e}", file=sys.stderr)
                self.face_encodings = {}
    
    def save_encodings(self):
        """Save face encodings to file"""
        try:
            os.makedirs(self.encodings_file.parent, exist_ok=True)
            with open(self.encodings_file, 'wb') as f:
                pickle.dump(self.face_encodings, f)
        except Exception as e:
            print(f"Error saving encodings: {e}", file=sys.stderr)
    
    def register_face(self, image_path, employee_id):
        """Register a new face for an employee (supports multiple photos)"""
        try:
            # Load image
            image = face_recognition.load_image_file(image_path)
            
            # Find face locations
            face_locations = face_recognition.face_locations(image)
            
            if not face_locations:
                return {
                    "success": False,
                    "error": "No face detected in the image"
                }
            
            if len(face_locations) > 1:
                return {
                    "success": False,
                    "error": "Multiple faces detected. Please use an image with only one face."
                }
            
            # Get face encoding
            face_encodings = face_recognition.face_encodings(image, face_locations)
            
            if not face_encodings:
                return {
                    "success": False,
                    "error": "Could not generate face encoding"
                }
            
            new_encoding = face_encodings[0]
            
            # If employee already has encodings, add to the list for better accuracy
            if employee_id in self.face_encodings:
                if isinstance(self.face_encodings[employee_id], list):
                    self.face_encodings[employee_id].append(new_encoding)
                else:
                    # Convert single encoding to list
                    self.face_encodings[employee_id] = [self.face_encodings[employee_id], new_encoding]
            else:
                # First photo for this employee
                self.face_encodings[employee_id] = [new_encoding]
            
            # Limit to 5 photos per employee for performance
            if len(self.face_encodings[employee_id]) > 5:
                self.face_encodings[employee_id] = self.face_encodings[employee_id][-5:]
            
            self.save_encodings()
            
            return {
                "success": True,
                "message": f"Face registered successfully for employee {employee_id}",
                "encoding": new_encoding.tolist(),
                "total_photos": len(self.face_encodings[employee_id])
            }
            
        except Exception as e:
            return {
                "success": False,
                "error": f"Error processing image: {str(e)}"
            }
    
    def verify_face(self, image_path, tolerance=0.4):
        """Verify a face against all registered faces (stricter threshold to prevent false matches)"""
        try:
            # Load image
            image = face_recognition.load_image_file(image_path)
            
            # Find face locations
            face_locations = face_recognition.face_locations(image)
            
            if not face_locations:
                return {
                    "success": False,
                    "error": "No face detected in the image"
                }
            
            # Get face encoding
            face_encodings = face_recognition.face_encodings(image, face_locations)
            
            if not face_encodings:
                return {
                    "success": False,
                    "error": "Could not generate face encoding"
                }
            
            unknown_encoding = face_encodings[0]
            
            # Compare with all registered faces
            best_match = None
            best_distance = float('inf')
            
            for employee_id, known_encodings in self.face_encodings.items():
                # Handle both single encoding and multiple encodings
                if isinstance(known_encodings, list):
                    encodings_list = known_encodings
                else:
                    encodings_list = [known_encodings]
                
                # Calculate distances to all encodings for this employee
                distances = face_recognition.face_distance(encodings_list, unknown_encoding)
                min_distance = min(distances)
                
                if min_distance < tolerance and min_distance < best_distance:
                    best_distance = min_distance
                    best_match = employee_id
            
            if best_match:
                confidence = 1 - best_distance  # Convert distance to confidence
                return {
                    "success": True,
                    "employeeId": best_match,
                    "confidence": round(confidence, 3),
                    "distance": round(best_distance, 3)
                }
            else:
                return {
                    "success": True,
                    "employeeId": "unknown",
                    "confidence": 0.0,
                    "message": "Unknown person detected"
                }
                
        except Exception as e:
            return {
                "success": False,
                "error": f"Error processing image: {str(e)}"
            }

def main():
    if len(sys.argv) < 3:
        print(json.dumps({
            "success": False,
            "error": "Usage: python face_recognition_engine.py <action> <image_path> [employee_id]"
        }))
        sys.exit(1)
    
    action = sys.argv[1]
    image_path = sys.argv[2]
    
    engine = FaceRecognitionEngine()
    
    if action == "register":
        if len(sys.argv) < 4:
            print(json.dumps({
                "success": False,
                "error": "Employee ID required for registration"
            }))
            sys.exit(1)
        
        employee_id = sys.argv[3]
        result = engine.register_face(image_path, employee_id)
        
    elif action == "verify":
        result = engine.verify_face(image_path)
        
    else:
        result = {
            "success": False,
            "error": f"Unknown action: {action}. Use 'register' or 'verify'"
        }
    
    print(json.dumps(result))

if __name__ == "__main__":
    main()