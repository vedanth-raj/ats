import 'package:hive/hive.dart';

part 'task.g.dart';

@HiveType(typeId: 0)
class Task extends HiveObject {
  @HiveField(0)
  String id;

  @HiveField(1)
  String title;

  @HiveField(2)
  String? description;

  @HiveField(3)
  DateTime date;

  @HiveField(4)
  DateTime? time;

  @HiveField(5)
  String category; // Work, Study, Health, Personal

  @HiveField(6)
  int color; // Color value

  @HiveField(7)
  bool completed;

  @HiveField(8)
  bool hasReminder;

  Task({
    required this.id,
    required this.title,
    this.description,
    required this.date,
    this.time,
    required this.category,
    required this.color,
    this.completed = false,
    this.hasReminder = false,
  });

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'title': title,
      'description': description,
      'date': date.toIso8601String(),
      'time': time?.toIso8601String(),
      'category': category,
      'color': color,
      'completed': completed,
      'hasReminder': hasReminder,
    };
  }

  factory Task.fromJson(Map<String, dynamic> json) {
    return Task(
      id: json['id'],
      title: json['title'],
      description: json['description'],
      date: DateTime.parse(json['date']),
      time: json['time'] != null ? DateTime.parse(json['time']) : null,
      category: json['category'],
      color: json['color'],
      completed: json['completed'] ?? false,
      hasReminder: json['hasReminder'] ?? false,
    );
  }
}
