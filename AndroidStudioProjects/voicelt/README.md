# Campus Voting System

A comprehensive voting system for educational institutions built with Flutter (mobile app) and Firebase-only web admin panel.

## Features

### Mobile App (Flutter)
- Student registration with SAP ID, name, phone, password
- Secure login verification against Firebase database
- OTP verification (demo mode - accepts any 6-digit code)
- Vote casting for multiple positions
- Real-time nominee display from Firebase
- Vote tracking to prevent double voting
- Voter data stored with each vote

### Web Admin Panel (Firebase-only)
- Pure HTML/JavaScript admin interface
- No PHP/MySQL dependencies required
- Admin login system
- Add nominees with details
- View all nominees in real-time
- View live voting results with voter lists
- Can be hosted on Firebase Hosting

### Firebase Integration
- Realtime Database for all data storage
- No external databases needed
- Real-time updates across all interfaces
- Secure data structure

## Setup Instructions

### Prerequisites
- Flutter SDK (latest stable version)
- Android Studio or VS Code
- Firebase account
- Web browser (for admin panel)

### 1. Firebase Setup
Follow the detailed instructions in `firebase_setup.md`

### 2. Flutter App Setup
```bash
# Install dependencies
flutter pub get

# Update Firebase configuration in lib/firebase_options.dart

# Run the app
flutter run
```

### 3. Web Admin Setup
**No server setup required!**

- Open `web-admin/index.html` in any web browser
- Login with: admin / admin123
- Start adding nominees and viewing results

**Optional: Host online**
```bash
# Install Firebase CLI
npm install -g firebase-tools

# Login and deploy
firebase login
firebase init hosting
firebase deploy

# Access at: https://your-project-id.web.app
```

## Database Structure (Firebase Realtime Database)

```
{
  "admin": {
    "username": "admin",
    "password": "admin123"
  },
  "users": {
    "ST12345": {
      "name": "John Doe",
      "sap_id": "ST12345",
      "phone": "+91XXXXXXXXXX",
      "password": "user_password",
      "has_voted": true
    }
  },
  "nominees": {
    "President": {
      "nominee_id_1": {
        "name": "Alice Johnson",
        "sap_id": "ST12345",
        "department": "CSE",
        "manifesto": "Promote creativity and innovation..."
      }
    }
  },
  "votes": {
    "President": {
      "nominee_id_1": {
        "count": 5,
        "voters": ["ST12345", "ST67890", "ST11111", "ST22222", "ST33333"]
      }
    }
  }
}
```

## Usage Flow

### For Students:
1. **Register**: Enter SAP ID, name, phone, password
2. **Login**: Use SAP ID and password
3. **OTP**: Enter any 6-digit code (demo)
4. **Vote**: Select nominees for each position
5. **Thank You**: Confirmation screen

### For Admins:
1. **Login**: Use admin credentials
2. **Add Nominees**: Fill nominee details and select position
3. **View Nominees**: See all nominees organized by position
4. **View Results**: Monitor voting progress with voter details

## Security Features

- User registration with password verification
- Admin panel with secure login
- Vote tracking to prevent multiple voting
- Voter anonymity (only SAP IDs stored, not linked to votes publicly)
- Firebase security rules for data protection

## Development

### Project Structure
```
campus-voting-system/
├── lib/
│   ├── models/          # Data models
│   ├── screens/         # UI screens
│   ├── services/        # Firebase services
│   └── main.dart        # App entry point
├── web-admin/           # HTML admin panel
│   ├── index.html       # Login page
│   ├── dashboard.html   # Admin dashboard
│   ├── add-nominee.html # Add nominee form
│   ├── view-nominees.html # View nominees
│   └── view-results.html  # View results
├── android/             # Android config
├── firebase_setup.md    # Setup guide
└── README.md
```

### Key Files Modified
- `lib/main.dart`: Changed home screen to RegistrationScreen
- `lib/screens/registration_screen.dart`: New user registration
- `lib/screens/login_screen.dart`: Updated to verify against Firebase
- `lib/services/firebase_service.dart`: Added user management methods
- Web admin: Converted from PHP to pure HTML/JavaScript

## Migration from PHP/MySQL

The system has been completely migrated from PHP/MySQL to Firebase-only:

- ✅ Removed all PHP dependencies
- ✅ Converted admin panel to HTML/JS
- ✅ All data now stored in Firebase Realtime Database
- ✅ No server setup required
- ✅ Can be hosted on Firebase Hosting

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make changes and test thoroughly
4. Submit a pull request

## License

This project is licensed under the MIT License.
