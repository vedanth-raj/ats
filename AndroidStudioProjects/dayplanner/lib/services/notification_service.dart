import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:timezone/timezone.dart' as tz;
import '../models/reminder.dart';
import '../models/task.dart';
import 'database_service.dart';

class NotificationService {
  static final FlutterLocalNotificationsPlugin _notificationsPlugin =
      FlutterLocalNotificationsPlugin();

  static Future<void> init() async {
    const AndroidInitializationSettings androidSettings =
        AndroidInitializationSettings('@mipmap/ic_launcher');

    const DarwinInitializationSettings iosSettings =
        DarwinInitializationSettings(
      requestAlertPermission: true,
      requestBadgePermission: true,
      requestSoundPermission: true,
    );

    const InitializationSettings settings = InitializationSettings(
      android: androidSettings,
      iOS: iosSettings,
    );

    await _notificationsPlugin.initialize(
      settings,
      onDidReceiveNotificationResponse: (NotificationResponse response) {
        // Handle notification tap
        print('Notification tapped: ${response.payload}');
      },
    );

    // Request permissions for iOS
    await _notificationsPlugin
        .resolvePlatformSpecificImplementation<
            IOSFlutterLocalNotificationsPlugin>()
        ?.requestPermissions(
          alert: true,
          badge: true,
          sound: true,
        );
  }

  static Future<void> scheduleReminder(Reminder reminder, Task task) async {
    final nextReminder = reminder.getNextReminder();
    if (nextReminder == null) return;

    final tzDateTime = tz.TZDateTime.from(nextReminder, tz.local);

    const AndroidNotificationDetails androidDetails =
        AndroidNotificationDetails(
      'reminder_channel',
      'Task Reminders',
      channelDescription: 'Notifications for task reminders',
      importance: Importance.high,
      priority: Priority.high,
      sound: RawResourceAndroidNotificationSound('notification'),
    );

    const DarwinNotificationDetails iosDetails = DarwinNotificationDetails(
      sound: 'notification.wav',
    );

    const NotificationDetails details = NotificationDetails(
      android: androidDetails,
      iOS: iosDetails,
    );

    await _notificationsPlugin.zonedSchedule(
      reminder.id.hashCode,
      'Task Reminder',
      task.title,
      tzDateTime,
      details,
      androidAllowWhileIdle: true,
      uiLocalNotificationDateInterpretation:
          UILocalNotificationDateInterpretation.absoluteTime,
      matchDateTimeComponents: _getDateTimeComponents(reminder.repeat),
      payload: task.id,
    );
  }

  static Future<void> cancelReminder(String reminderId) async {
    await _notificationsPlugin.cancel(reminderId.hashCode);
  }

  static Future<void> cancelAllReminders() async {
    await _notificationsPlugin.cancelAll();
  }

  static Future<void> rescheduleAllReminders() async {
    await cancelAllReminders();

    final databaseService = DatabaseService();
    final reminders = databaseService.getAllReminders();
    final tasks = databaseService.getAllTasks();

    for (final reminder in reminders) {
      final task = tasks.firstWhere(
        (t) => t.id == reminder.taskId,
        orElse: () => Task(
          id: '',
          title: '',
          date: DateTime.now(),
          category: '',
          color: 0,
        ),
      );

      if (task.id.isNotEmpty) {
        await scheduleReminder(reminder, task);
      }
    }
  }

  static DateTimeComponents? _getDateTimeComponents(String repeat) {
    switch (repeat) {
      case 'daily':
        return DateTimeComponents.time;
      case 'weekly':
        return DateTimeComponents.dayOfWeekAndTime;
      default:
        return null;
    }
  }

  static Future<void> showInstantNotification(String title, String body) async {
    const AndroidNotificationDetails androidDetails =
        AndroidNotificationDetails(
      'instant_channel',
      'Instant Notifications',
      channelDescription: 'Instant notifications',
      importance: Importance.high,
      priority: Priority.high,
    );

    const DarwinNotificationDetails iosDetails = DarwinNotificationDetails();

    const NotificationDetails details = NotificationDetails(
      android: androidDetails,
      iOS: iosDetails,
    );

    await _notificationsPlugin.show(
      DateTime.now().millisecondsSinceEpoch ~/ 1000,
      title,
      body,
      details,
    );
  }
}
