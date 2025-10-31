import 'package:hive/hive.dart';

part 'habit.g.dart';

@HiveType(typeId: 2)
class Habit extends HiveObject {
  @HiveField(0)
  String id;

  @HiveField(1)
  String title;

  @HiveField(2)
  int streak;

  @HiveField(3)
  int target; // Target days per week/month

  @HiveField(4)
  List<DateTime> completedDates;

  Habit({
    required this.id,
    required this.title,
    this.streak = 0,
    this.target = 7, // Default weekly
    this.completedDates = const [],
  });

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'title': title,
      'streak': streak,
      'target': target,
      'completedDates': completedDates.map((d) => d.toIso8601String()).toList(),
    };
  }

  factory Habit.fromJson(Map<String, dynamic> json) {
    return Habit(
      id: json['id'],
      title: json['title'],
      streak: json['streak'] ?? 0,
      target: json['target'] ?? 7,
      completedDates: (json['completedDates'] as List<dynamic>?)
              ?.map((d) => DateTime.parse(d))
              .toList() ??
          [],
    );
  }

  bool isCompletedToday() {
    final today = DateTime.now();
    return completedDates.any((date) =>
        date.year == today.year &&
        date.month == today.month &&
        date.day == today.day);
  }

  void markCompleted() {
    final today = DateTime.now();
    if (!isCompletedToday()) {
      completedDates.add(today);
      streak++;
    }
  }

  void markIncomplete() {
    final today = DateTime.now();
    completedDates.removeWhere((date) =>
        date.year == today.year &&
        date.month == today.month &&
        date.day == today.day);
    if (streak > 0) streak--;
  }
}
