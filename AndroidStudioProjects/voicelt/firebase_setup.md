# Firebase Setup Instructions

## 1. Create Firebase Project

1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Click "Create a project" or "Add project"
3. Enter project name (e.g., "campus-voting-system")
4. Choose whether to enable Google Analytics (optional)
5. Click "Create project"

## 2. Enable Authentication

1. In your Firebase project, go to "Authentication" in the left sidebar
2. Click "Get started"
3. Go to "Sign-in method" tab
4. Find "Phone" in the provider list and click on it
5. Toggle "Enable" and click "Save"

## 3. Set up Realtime Database

1. In your Firebase project, go to "Realtime Database" in the left sidebar
2. Click "Create database"
3. Choose "Start in test mode" (for development) or set up security rules
4. Select a location for your database
5. Click "Done"

## 4. Set Security Rules

Go to "Realtime Database" → "Rules" and replace the default rules with:

```json
{
  "rules": {
    ".read": true,
    ".write": true
  }
}
```

For production, use more restrictive rules:

```json
{
  "rules": {
    "users": {
      ".read": "auth != null",
      ".write": "auth != null"
    },
    "nominees": {
      ".read": true,
      ".write": false
    },
    "votes": {
      ".read": "auth != null",
      ".write": "auth != null"
    },
    "admin": {
      ".read": false,
      ".write": false
    }
  }
}
```

## 5. Get Project Configuration

1. Click the gear icon → "Project settings"
2. Scroll down to "Your apps" section
3. Click "Add app" → Flutter icon
4. Enter app details (Android package name: com.example.campus_voting_app)
5. Download `google-services.json` and place it in `android/app/`
6. For iOS, download `GoogleService-Info.plist` and place it in `ios/Runner/`

## 6. Update Firebase Options

After getting your Firebase config, update `lib/firebase_options.dart` with your actual values:

- **apiKey**: Your Firebase API key
- **appId**: Your app ID
- **messagingSenderId**: Your sender ID
- **projectId**: Your project ID
- **databaseURL**: Your Realtime Database URL
- **storageBucket**: Your storage bucket
- **measurementId**: Your measurement ID (for web)

## 7. Web Admin Setup (Firebase-only)

The web admin panel now uses pure HTML/JavaScript with Firebase SDK. No PHP/MySQL required!

### Local Testing:
- Open `web-admin/index.html` in your browser
- Login with admin/admin123
- Add nominees, view results

### Hosting on Firebase (Optional):
1. Go to "Hosting" in Firebase Console
2. Click "Get started"
3. Install Firebase CLI: `npm install -g firebase-tools`
4. Login: `firebase login`
5. Initialize: `firebase init hosting`
6. Deploy: `firebase deploy`
7. Access at: `https://your-project-id.web.app`

## Database Structure

Your Firebase Realtime Database will have this structure:

```
{
  "admin": {
    "username": "admin",
    "password": "admin123"
  },
  "users": {
    "{sap_id}": {
      "name": "John Doe",
      "sap_id": "ST12345",
      "phone": "+91XXXXXXXXXX",
      "password": "hashed_password",
      "has_voted": true
    }
  },
  "nominees": {
    "{position}": {
      "{nominee_id}": {
        "name": "Alice Johnson",
        "sap_id": "ST12345",
        "department": "CSE",
        "manifesto": "Promote creativity..."
      }
    }
  },
  "votes": {
    "{position}": {
      "{nominee_id}": {
        "count": 5,
        "voters": ["ST12345", "ST67890"]
      }
    }
  }
}
```

## Security Notes

- Admin credentials are stored in plain text for demo (hash in production)
- User passwords should be hashed before storing
- Database rules allow public read for nominees, authenticated write for votes
- In production, implement proper authentication and authorization
