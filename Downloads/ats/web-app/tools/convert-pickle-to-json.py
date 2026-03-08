#!/usr/bin/env python3
"""
Convert pickle face recognition models to JSON format for web-based face-api.js
This script reads the pickle models from the old system and converts them to JSON
"""

import pickle
import json
import numpy as np
import os
import sys

def convert_pickle_to_json(employee_id, pickle_path, output_path):
    """
    Convert a pickle model file to JSON format
    
    Args:
        employee_id: Employee ID (e.g., "0001")
        pickle_path: Path to the pickle file
        output_path: Path where JSON should be saved
    """
    try:
        # Load the pickle file
        print(f"[INFO] Loading pickle model for employee {employee_id}...")
        with open(pickle_path, 'rb') as f:
            data = pickle.load(f)
        
        # Extract encodings (face descriptors)
        encodings = data['encodings']
        
        if not encodings:
            print(f"[ERROR] No encodings found in pickle file for {employee_id}")
            return False
        
        # Convert numpy arrays to lists and compute average descriptor
        descriptor_list = [encoding.tolist() for encoding in encodings]
        
        # Compute average descriptor (this is what face-api.js expects)
        avg_descriptor = np.mean(encodings, axis=0).tolist()
        
        # Create JSON structure
        model_data = {
            "employee_id": employee_id,
            "descriptor": avg_descriptor,
            "num_samples": len(encodings),
            "descriptor_length": len(avg_descriptor),
            "all_descriptors": descriptor_list  # Keep all for reference
        }
        
        # Create output directory if it doesn't exist
        os.makedirs(os.path.dirname(output_path), exist_ok=True)
        
        # Save as JSON
        print(f"[INFO] Saving JSON model to {output_path}...")
        with open(output_path, 'w') as f:
            json.dump(model_data, f, indent=2)
        
        print(f"[SUCCESS] Converted model for employee {employee_id}")
        print(f"  - Samples: {len(encodings)}")
        print(f"  - Descriptor length: {len(avg_descriptor)}")
        
        return True
        
    except Exception as e:
        print(f"[ERROR] Failed to convert model for {employee_id}: {str(e)}")
        return False

def main():
    # Paths (relative to project root)
    old_system_path = "0-management-auto-attendance-system/Management_Auto_Attendance_System/bin/Debug"
    trained_models_path = os.path.join(old_system_path, "Trained_Models")
    web_models_path = "web-app/models"
    
    # Get all employee IDs with trained models
    if not os.path.exists(trained_models_path):
        print(f"[ERROR] Trained models directory not found: {trained_models_path}")
        return
    
    employee_ids = [d for d in os.listdir(trained_models_path) 
                    if os.path.isdir(os.path.join(trained_models_path, d))]
    
    if not employee_ids:
        print("[ERROR] No trained models found")
        return
    
    print(f"[INFO] Found {len(employee_ids)} trained models")
    
    success_count = 0
    for employee_id in employee_ids:
        pickle_file = os.path.join(trained_models_path, employee_id, f"{employee_id}_(Model).pickle")
        json_file = os.path.join(web_models_path, employee_id, "model.json")
        
        if os.path.exists(pickle_file):
            if convert_pickle_to_json(employee_id, pickle_file, json_file):
                success_count += 1
        else:
            print(f"[WARNING] Pickle file not found for employee {employee_id}")
    
    print(f"\n[SUMMARY] Successfully converted {success_count}/{len(employee_ids)} models")

if __name__ == "__main__":
    main()
