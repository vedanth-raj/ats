#!/usr/bin/env python3
"""
Train face recognition model from dataset images
"""

import face_recognition
import cv2
import os
import json
import numpy as np
from pathlib import Path

def train_model(employee_id, dataset_path, output_path):
    """
    Train a face recognition model from dataset images
    
    Args:
        employee_id: Employee ID (e.g., "0002")
        dataset_path: Path to dataset images
        output_path: Path where JSON model should be saved
    """
    try:
        # Get all image files
        image_files = list(Path(dataset_path).glob("*.jpg")) + \
                     list(Path(dataset_path).glob("*.jpeg")) + \
                     list(Path(dataset_path).glob("*.png"))
        
        if not image_files:
            print(f"[ERROR] No images found in {dataset_path}")
            return False
        
        print(f"[INFO] Found {len(image_files)} images for employee {employee_id}")
        
        # Extract face encodings from all images
        encodings = []
        for i, image_path in enumerate(image_files):
            print(f"[INFO] Processing image {i+1}/{len(image_files)}: {image_path.name}")
            
            # Load image
            image = cv2.imread(str(image_path))
            if image is None:
                print(f"[WARNING] Could not load image: {image_path}")
                continue
            
            # Convert BGR to RGB
            rgb = cv2.cvtColor(image, cv2.COLOR_BGR2RGB)
            
            # Detect faces
            boxes = face_recognition.face_locations(rgb, model="hog")
            
            if not boxes:
                print(f"[WARNING] No face detected in: {image_path.name}")
                continue
            
            # Get face encodings
            face_encodings = face_recognition.face_encodings(rgb, boxes)
            
            if face_encodings:
                encodings.append(face_encodings[0])
                print(f"  ✓ Face encoding extracted")
        
        if not encodings:
            print(f"[ERROR] No face encodings extracted for employee {employee_id}")
            return False
        
        print(f"[INFO] Successfully extracted {len(encodings)} face encodings")
        
        # Compute average descriptor
        avg_descriptor = np.mean(encodings, axis=0).tolist()
        
        # Create JSON structure
        model_data = {
            "employee_id": employee_id,
            "descriptor": avg_descriptor,
            "num_samples": len(encodings),
            "descriptor_length": len(avg_descriptor),
            "all_descriptors": [enc.tolist() for enc in encodings]
        }
        
        # Create output directory
        os.makedirs(os.path.dirname(output_path), exist_ok=True)
        
        # Save as JSON
        print(f"[INFO] Saving model to {output_path}...")
        with open(output_path, 'w') as f:
            json.dump(model_data, f, indent=2)
        
        print(f"[SUCCESS] Model trained for employee {employee_id}")
        print(f"  - Samples: {len(encodings)}")
        print(f"  - Descriptor length: {len(avg_descriptor)}")
        
        return True
        
    except Exception as e:
        print(f"[ERROR] Failed to train model for {employee_id}: {str(e)}")
        import traceback
        traceback.print_exc()
        return False

def main():
    # Train model for employee 0002
    employee_id = "0002"
    dataset_path = "0-management-auto-attendance-system/Management_Auto_Attendance_System/bin/Debug/Datasets/0002"
    output_path = f"web-app/models/{employee_id}/model.json"
    
    if not os.path.exists(dataset_path):
        print(f"[ERROR] Dataset not found: {dataset_path}")
        return
    
    train_model(employee_id, dataset_path, output_path)

if __name__ == "__main__":
    main()
