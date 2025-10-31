# Day Planner App Development TODO

## 1. Setup Dependencies and Fonts
- [x] Update pubspec.yaml with required dependencies: hive, hive_flutter, flutter_local_notifications, provider, intl, share_plus, file_picker, flutter_markdown (for notes), table_calendar (for calendar), percent_indicator (for habit progress), flutter_colorpicker (for themes), flutter_staggered_animations (for animations).
- [x] Add fonts: Poppins and Inter.
- [x] Run flutter pub get.

## 2. Data Models and Storage
- [x] Create models/task.dart: Task model with Hive adapter (id, title, description, date, time, category, color, completed, reminder).
- [x] Create models/note.dart: Note model with Hive adapter (id, title, content, date, tags).
- [x] Create models/habit.dart: Habit model with Hive adapter (id, title, streak, target, completedDates).
- [x] Create models/reminder.dart: Reminder model with Hive adapter (id, taskId, dateTime, repeat).
- [x] Create services/database_service.dart: Initialize Hive, open boxes for tasks, notes, habits, settings.
- [x] Create services/notification_service.dart: Setup flutter_local_notifications, schedule reminders.

## 3. State Management
- [x] Create providers/theme_provider.dart: Manage light/dark/AMOLED themes, accent colors.
- [x] Create providers/data_provider.dart: Manage tasks, notes, habits data with CRUD operations.

## 4. Screens and UI
- [x] Create screens/dashboard_screen.dart: Timeline view, drag-drop tasks, color-coded categories.
- [x] Create screens/notes_screen.dart: List notes, add/edit with markdown support.
- [x] Create screens/calendar_screen.dart: Monthly/weekly calendar, tap to view/add tasks.
- [x] Create screens/habit_tracker_screen.dart: List habits, progress rings, streaks.
- [x] Create screens/settings_screen.dart: Theme selection, accent colors, backup/export.
- [x] Create widgets/task_tile.dart: Reusable task widget with animations.
- [ ] Create widgets/habit_progress_ring.dart: Custom progress ring for habits.
- [x] Update lib/main.dart: Set up app structure, initialize Hive, providers, routes.

## 5. Features Implementation
- [ ] Implement drag-and-drop for tasks in dashboard.
- [ ] Add local push notifications for reminders.
- [ ] Implement repeating reminders (daily/weekly/custom).
- [ ] Add daily reflection page in notes.
- [ ] Implement export/import as JSON/CSV.
- [ ] Add smooth animations: Page transitions, micro-animations.

## 6. Testing and Optimization
- [ ] Test offline functionality: No internet required.
- [ ] Unit tests for models and services.
- [ ] Performance optimization for large data sets.
- [ ] Ensure data privacy: All local storage.

## 7. Bonus Features (Optional)
- [ ] Add home screen widget for daily overview.
- [ ] Voice-to-text for quick task entry (if possible offline).
- [ ] Mood tracking in notes.
- [ ] Offline motivational quotes.
- [ ] Focus Mode with timer.
