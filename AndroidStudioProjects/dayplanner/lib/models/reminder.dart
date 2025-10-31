import 'package:hive/hive.dart';

part 'reminder.g.dart';

@HiveType(typeId: 3)
class Reminder extends HiveObject {
  @HiveField(0)
  String id;

  @HiveField(1)
  String taskId;

  @HiveField(2)
  DateTime dateTime;

  @HiveField(3)
  String repeat; // 'none', 'daily', 'weekly', 'custom'

  @HiveField(4)
  int? customDays; // For custom repeat

  Reminder({
    required this.id,
    required this.taskId,
    required this.dateTime,
    this.repeat = 'none',
    this.customDays,
  });

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'taskId': taskId,
      'dateTime': dateTime.toIso8601String(),
      'repeat': repeat,
      'customDays': customDays,
    };
  }

  factory Reminder.fromJson(Map<String, dynamic> json) {
    return Reminder(
      id: json['id'],
      taskId: json['taskId'],
      dateTime: DateTime.parse(json['dateTime']),
      repeat: json['repeat'] ?? 'none',
      customDays: json['customDays'],
    );
  }

  DateTime? getNextReminder() {
    if (repeat == 'none') return null;

    DateTime next = dateTime;
    final now = DateTime.now();

    while (next.isBefore(now)) {
      switch (repeat) {
        case 'daily':
          next = next.add(const Duration(days: 1));
          break;
        case 'weekly':
          next = next.add(const Duration(days: 7));
          break;
        case 'custom':
          if (customDays != null) {
            next = next.add(Duration(days: customDays!));
          }
          break;
      }
    }
    return next;
  }
}
