import 'package:flutter/material.dart';
import '../models/task.dart';
import '../models/note.dart';
import '../models/habit.dart';
import '../models/reminder.dart';
import '../services/database_service.dart';
import '../services/notification_service.dart';

class DataProvider extends ChangeNotifier {
  final DatabaseService _databaseService = DatabaseService();

  List<Task> _tasks = [];
  List<Note> _notes = [];
  List<Habit> _habits = [];
  List<Reminder> _reminders = [];

  List<Task> get tasks => _tasks;
  List<Note> get notes => _notes;
  List<Habit> get habits => _habits;
  List<Reminder> get reminders => _reminders;

  Future<void> loadData() async {
    _tasks = _databaseService.getAllTasks();
    _notes = _databaseService.getAllNotes();
    _habits = _databaseService.getAllHabits();
    _reminders = _databaseService.getAllReminders();
    notifyListeners();
  }

  // Task operations
  Future<void> addTask(Task task) async {
    await _databaseService.addTask(task);
    _tasks.add(task);
    notifyListeners();

    // Schedule reminder if needed
    if (task.hasReminder) {
      final reminders = _databaseService.getRemindersForTask(task.id);
      for (final reminder in reminders) {
        await NotificationService.scheduleReminder(reminder, task);
      }
    }
  }

  Future<void> updateTask(Task task) async {
    await _databaseService.updateTask(task);
    final index = _tasks.indexWhere((t) => t.id == task.id);
    if (index != -1) {
      _tasks[index] = task;
      notifyListeners();
    }

    // Update reminders
    if (task.hasReminder) {
      final reminders = _databaseService.getRemindersForTask(task.id);
      for (final reminder in reminders) {
        await NotificationService.cancelReminder(reminder.id);
        await NotificationService.scheduleReminder(reminder, task);
      }
    }
  }

  Future<void> deleteTask(String id) async {
    // Cancel reminders first
    final taskReminders = _databaseService.getRemindersForTask(id);
    for (final reminder in taskReminders) {
      await NotificationService.cancelReminder(reminder.id);
      await _databaseService.deleteReminder(reminder.id);
    }

    await _databaseService.deleteTask(id);
    _tasks.removeWhere((t) => t.id == id);
    _reminders.removeWhere((r) => r.taskId == id);
    notifyListeners();
  }

  List<Task> getTasksForDate(DateTime date) {
    return _tasks.where((task) =>
      task.date.year == date.year &&
      task.date.month == date.month &&
      task.date.day == date.day
    ).toList();
  }

  // Note operations
  Future<void> addNote(Note note) async {
    await _databaseService.addNote(note);
    _notes.add(note);
    notifyListeners();
  }

  Future<void> updateNote(Note note) async {
    await _databaseService.updateNote(note);
    final index = _notes.indexWhere((n) => n.id == note.id);
    if (index != -1) {
      _notes[index] = note;
      notifyListeners();
    }
  }

  Future<void> deleteNote(String id) async {
    await _databaseService.deleteNote(id);
    _notes.removeWhere((n) => n.id == id);
    notifyListeners();
  }

  // Habit operations
  Future<void> addHabit(Habit habit) async {
    await _databaseService.addHabit(habit);
    _habits.add(habit);
    notifyListeners();
  }

  Future<void> updateHabit(Habit habit) async {
    await _databaseService.updateHabit(habit);
    final index = _habits.indexWhere((h) => h.id == habit.id);
    if (index != -1) {
      _habits[index] = habit;
      notifyListeners();
    }
  }

  Future<void> deleteHabit(String id) async {
    await _databaseService.deleteHabit(id);
    _habits.removeWhere((h) => h.id == id);
    notifyListeners();
  }

  Future<void> markHabitCompleted(String habitId) async {
    final habit = _habits.firstWhere((h) => h.id == habitId);
    habit.markCompleted();
    await updateHabit(habit);
  }

  Future<void> markHabitIncomplete(String habitId) async {
    final habit = _habits.firstWhere((h) => h.id == habitId);
    habit.markIncomplete();
    await updateHabit(habit);
  }

  // Reminder operations
  Future<void> addReminder(Reminder reminder) async {
    await _databaseService.addReminder(reminder);
    _reminders.add(reminder);
    notifyListeners();

    // Schedule notification
    final task = _tasks.firstWhere((t) => t.id == reminder.taskId);
    await NotificationService.scheduleReminder(reminder, task);
  }

  Future<void> updateReminder(Reminder reminder) async {
    await _databaseService.updateReminder(reminder);
    final index = _reminders.indexWhere((r) => r.id == reminder.id);
    if (index != -1) {
      _reminders[index] = reminder;
      notifyListeners();
    }

    // Reschedule notification
    final task = _tasks.firstWhere((t) => t.id == reminder.taskId);
    await NotificationService.cancelReminder(reminder.id);
    await NotificationService.scheduleReminder(reminder, task);
  }

  Future<void> deleteReminder(String id) async {
    await NotificationService.cancelReminder(id);
    await _databaseService.deleteReminder(id);
    _reminders.removeWhere((r) => r.id == id);
    notifyListeners();
  }

  // Export/Import
  Future<Map<String, dynamic>> exportData() async {
    return await _databaseService.exportData();
  }

  Future<void> importData(Map<String, dynamic> data) async {
    await _databaseService.importData(data);
    await loadData();
  }
}
