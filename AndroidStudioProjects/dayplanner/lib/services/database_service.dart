import 'package:hive_flutter/hive_flutter.dart';
import '../models/task.dart';
import '../models/note.dart';
import '../models/habit.dart';
import '../models/reminder.dart';

class DatabaseService {
  static const String tasksBoxName = 'tasks';
  static const String notesBoxName = 'notes';
  static const String habitsBoxName = 'habits';
  static const String remindersBoxName = 'reminders';
  static const String settingsBoxName = 'settings';

  static Future<void> init() async {
    await Hive.initFlutter();

    // Register adapters
    Hive.registerAdapter(TaskAdapter());
    Hive.registerAdapter(NoteAdapter());
    Hive.registerAdapter(HabitAdapter());
    Hive.registerAdapter(ReminderAdapter());

    // Open boxes
    await Hive.openBox<Task>(tasksBoxName);
    await Hive.openBox<Note>(notesBoxName);
    await Hive.openBox<Habit>(habitsBoxName);
    await Hive.openBox<Reminder>(remindersBoxName);
    await Hive.openBox(settingsBoxName);
  }

  // Tasks CRUD
  Box<Task> get tasksBox => Hive.box<Task>(tasksBoxName);

  Future<void> addTask(Task task) async {
    await tasksBox.put(task.id, task);
  }

  Future<void> updateTask(Task task) async {
    await task.save();
  }

  Future<void> deleteTask(String id) async {
    await tasksBox.delete(id);
  }

  List<Task> getAllTasks() {
    return tasksBox.values.toList();
  }

  List<Task> getTasksForDate(DateTime date) {
    return tasksBox.values.where((task) =>
      task.date.year == date.year &&
      task.date.month == date.month &&
      task.date.day == date.day
    ).toList();
  }

  // Notes CRUD
  Box<Note> get notesBox => Hive.box<Note>(notesBoxName);

  Future<void> addNote(Note note) async {
    await notesBox.put(note.id, note);
  }

  Future<void> updateNote(Note note) async {
    await note.save();
  }

  Future<void> deleteNote(String id) async {
    await notesBox.delete(id);
  }

  List<Note> getAllNotes() {
    return notesBox.values.toList();
  }

  // Habits CRUD
  Box<Habit> get habitsBox => Hive.box<Habit>(habitsBoxName);

  Future<void> addHabit(Habit habit) async {
    await habitsBox.put(habit.id, habit);
  }

  Future<void> updateHabit(Habit habit) async {
    await habit.save();
  }

  Future<void> deleteHabit(String id) async {
    await habitsBox.delete(id);
  }

  List<Habit> getAllHabits() {
    return habitsBox.values.toList();
  }

  // Reminders CRUD
  Box<Reminder> get remindersBox => Hive.box<Reminder>(remindersBoxName);

  Future<void> addReminder(Reminder reminder) async {
    await remindersBox.put(reminder.id, reminder);
  }

  Future<void> updateReminder(Reminder reminder) async {
    await reminder.save();
  }

  Future<void> deleteReminder(String id) async {
    await remindersBox.delete(id);
  }

  List<Reminder> getAllReminders() {
    return remindersBox.values.toList();
  }

  List<Reminder> getRemindersForTask(String taskId) {
    return remindersBox.values.where((reminder) => reminder.taskId == taskId).toList();
  }

  // Settings
  Box get settingsBox => Hive.box(settingsBoxName);

  Future<void> saveSetting(String key, dynamic value) async {
    await settingsBox.put(key, value);
  }

  dynamic getSetting(String key, {dynamic defaultValue}) {
    return settingsBox.get(key, defaultValue: defaultValue);
  }

  // Export/Import functionality
  Future<Map<String, dynamic>> exportData() async {
    return {
      'tasks': getAllTasks().map((t) => t.toJson()).toList(),
      'notes': getAllNotes().map((n) => n.toJson()).toList(),
      'habits': getAllHabits().map((h) => h.toJson()).toList(),
      'reminders': getAllReminders().map((r) => r.toJson()).toList(),
      'settings': Map<String, dynamic>.from(settingsBox.toMap()),
    };
  }

  Future<void> importData(Map<String, dynamic> data) async {
    // Clear existing data
    await tasksBox.clear();
    await notesBox.clear();
    await habitsBox.clear();
    await remindersBox.clear();
    await settingsBox.clear();

    // Import tasks
    if (data['tasks'] != null) {
      for (var taskJson in data['tasks']) {
        final task = Task.fromJson(taskJson);
        await addTask(task);
      }
    }

    // Import notes
    if (data['notes'] != null) {
      for (var noteJson in data['notes']) {
        final note = Note.fromJson(noteJson);
        await addNote(note);
      }
    }

    // Import habits
    if (data['habits'] != null) {
      for (var habitJson in data['habits']) {
        final habit = Habit.fromJson(habitJson);
        await addHabit(habit);
      }
    }

    // Import reminders
    if (data['reminders'] != null) {
      for (var reminderJson in data['reminders']) {
        final reminder = Reminder.fromJson(reminderJson);
        await addReminder(reminder);
      }
    }

    // Import settings
    if (data['settings'] != null) {
      for (var entry in data['settings'].entries) {
        await saveSetting(entry.key, entry.value);
      }
    }
  }
}
