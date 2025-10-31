import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:table_calendar/table_calendar.dart';
import '../providers/data_provider.dart';
import '../models/task.dart';

class CalendarScreen extends StatefulWidget {
  const CalendarScreen({super.key});

  @override
  State<CalendarScreen> createState() => _CalendarScreenState();
}

class _CalendarScreenState extends State<CalendarScreen> {
  DateTime _focusedDay = DateTime.now();
  DateTime? _selectedDay;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Column(
        children: [
          // Calendar widget
          Container(
            margin: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Theme.of(context).colorScheme.surface,
              borderRadius: BorderRadius.circular(12),
              boxShadow: [
                BoxShadow(
                  color: Theme.of(context).shadowColor.withOpacity(0.1),
                  blurRadius: 8,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Consumer<DataProvider>(
              builder: (context, dataProvider, child) {
                final tasks = dataProvider.tasks;

                return TableCalendar<Task>(
                  firstDay: DateTime.utc(2020, 1, 1),
                  lastDay: DateTime.utc(2030, 12, 31),
                  focusedDay: _focusedDay,
                  selectedDayPredicate: (day) => isSameDay(_selectedDay, day),
                  onDaySelected: (selectedDay, focusedDay) {
                    setState(() {
                      _selectedDay = selectedDay;
                      _focusedDay = focusedDay;
                    });
                  },
                  onPageChanged: (focusedDay) {
                    _focusedDay = focusedDay;
                  },
                  eventLoader: (day) {
                    return tasks.where((task) =>
                      task.date.year == day.year &&
                      task.date.month == day.month &&
                      task.date.day == day.day
                    ).toList();
                  },
                  calendarStyle: CalendarStyle(
                    todayDecoration: BoxDecoration(
                      color: Theme.of(context).colorScheme.primary.withOpacity(0.3),
                      shape: BoxShape.circle,
                    ),
                    selectedDecoration: BoxDecoration(
                      color: Theme.of(context).colorScheme.primary,
                      shape: BoxShape.circle,
                    ),
                    markerDecoration: BoxDecoration(
                      color: Theme.of(context).colorScheme.secondary,
                      shape: BoxShape.circle,
                    ),
                  ),
                  headerStyle: HeaderStyle(
                    formatButtonVisible: false,
                    titleCentered: true,
                    titleTextStyle: TextStyle(
                      fontFamily: 'Poppins',
                      fontWeight: FontWeight.w600,
                      fontSize: 18,
                    ),
                  ),
                  daysOfWeekStyle: DaysOfWeekStyle(
                    weekdayStyle: TextStyle(
                      fontFamily: 'Inter',
                      fontWeight: FontWeight.w600,
                      color: Theme.of(context).colorScheme.onSurface,
                    ),
                    weekendStyle: TextStyle(
                      fontFamily: 'Inter',
                      fontWeight: FontWeight.w600,
                      color: Theme.of(context).colorScheme.onSurface.withOpacity(0.7),
                    ),
                  ),
                  calendarBuilders: CalendarBuilders(
                    markerBuilder: (context, day, events) {
                      if (events.isNotEmpty) {
                        return Positioned(
                          right: 1,
                          bottom: 1,
                          child: Container(
                            width: 16,
                            height: 16,
                            decoration: BoxDecoration(
                              color: Theme.of(context).colorScheme.secondary,
                              shape: BoxShape.circle,
                            ),
                            child: Center(
                              child: Text(
                                '${events.length}',
                                style: TextStyle(
                                  color: Theme.of(context).colorScheme.onSecondary,
                                  fontSize: 10,
                                  fontWeight: FontWeight.bold,
                                  fontFamily: 'Inter',
                                ),
                              ),
                            ),
                          ),
                        );
                      }
                      return null;
                    },
                  ),
                );
              },
            ),
          ),

          // Tasks for selected day
          Expanded(
            child: Consumer<DataProvider>(
              builder: (context, dataProvider, child) {
                final selectedDate = _selectedDay ?? _focusedDay;
                final tasks = dataProvider.getTasksForDate(selectedDate);

                if (tasks.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.event_note,
                          size: 64,
                          color: Theme.of(context).colorScheme.onSurface.withOpacity(0.3),
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'No tasks on this day',
                          style: TextStyle(
                            fontSize: 18,
                            color: Theme.of(context).colorScheme.onSurface.withOpacity(0.6),
                            fontFamily: 'Inter',
                          ),
                        ),
                      ],
                    ),
                  );
                }

                final pendingTasks = tasks.where((task) => !task.completed).toList();
                final completedTasks = tasks.where((task) => task.completed).toList();

                return ListView(
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  children: [
                    if (pendingTasks.isNotEmpty) ...[
                      Padding(
                        padding: const EdgeInsets.symmetric(vertical: 8),
                        child: Text(
                          'Pending Tasks (${pendingTasks.length})',
                          style: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w600,
                            fontFamily: 'Poppins',
                          ),
                        ),
                      ),
                      ...pendingTasks.map((task) => _buildTaskItem(context, task)),
                    ],

                    if (completedTasks.isNotEmpty) ...[
                      const SizedBox(height: 24),
                      Padding(
                        padding: const EdgeInsets.symmetric(vertical: 8),
                        child: Text(
                          'Completed Tasks (${completedTasks.length})',
                          style: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w600,
                            fontFamily: 'Poppins',
                          ),
                        ),
                      ),
                      ...completedTasks.map((task) => _buildTaskItem(context, task)),
                    ],
                  ],
                );
              },
            ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () => _showAddTaskDialog(context),
        child: const Icon(Icons.add),
      ),
    );
  }

  Widget _buildTaskItem(BuildContext context, Task task) {
    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      elevation: 1,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(8),
      ),
      child: ListTile(
        leading: Container(
          width: 12,
          height: 12,
          decoration: BoxDecoration(
            color: Color(task.color),
            shape: BoxShape.circle,
          ),
        ),
        title: Text(
          task.title,
          style: TextStyle(
            fontFamily: 'Inter',
            fontWeight: FontWeight.w500,
            decoration: task.completed ? TextDecoration.lineThrough : null,
            color: task.completed
                ? Theme.of(context).colorScheme.onSurface.withOpacity(0.6)
                : Theme.of(context).colorScheme.onSurface,
          ),
        ),
        subtitle: task.time != null
            ? Text(
                '${task.time!.hour.toString().padLeft(2, '0')}:${task.time!.minute.toString().padLeft(2, '0')}',
                style: TextStyle(
                  fontFamily: 'Inter',
                  fontSize: 12,
                  color: Theme.of(context).colorScheme.onSurface.withOpacity(0.6),
                ),
              )
            : null,
        trailing: task.hasReminder
            ? Icon(
                Icons.notifications,
                size: 16,
                color: Theme.of(context).colorScheme.primary,
              )
            : null,
        onTap: () => _showTaskDetails(context, task),
      ),
    );
  }

  void _showTaskDetails(BuildContext context, Task task) {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
      ),
      builder: (context) => Container(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  width: 16,
                  height: 16,
                  decoration: BoxDecoration(
                    color: Color(task.color),
                    shape: BoxShape.circle,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(
                    task.title,
                    style: const TextStyle(
                      fontFamily: 'Poppins',
                      fontWeight: FontWeight.w600,
                      fontSize: 18,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            if (task.description != null) ...[
              Text(
                'Description',
                style: TextStyle(
                  fontFamily: 'Poppins',
                  fontWeight: FontWeight.w500,
                  color: Theme.of(context).colorScheme.primary,
                ),
              ),
              const SizedBox(height: 8),
              Text(
                task.description!,
                style: const TextStyle(
                  fontFamily: 'Inter',
                  fontSize: 16,
                ),
              ),
              const SizedBox(height: 16),
            ],
            Row(
              children: [
                Icon(
                  Icons.category,
                  size: 16,
                  color: Theme.of(context).colorScheme.onSurface.withOpacity(0.6),
                ),
                const SizedBox(width: 8),
                Text(
                  task.category,
                  style: TextStyle(
                    fontFamily: 'Inter',
                    color: Theme.of(context).colorScheme.onSurface.withOpacity(0.8),
                  ),
                ),
                const SizedBox(width: 16),
                if (task.time != null) ...[
                  Icon(
                    Icons.access_time,
                    size: 16,
                    color: Theme.of(context).colorScheme.onSurface.withOpacity(0.6),
                  ),
                  const SizedBox(width: 8),
                  Text(
                    '${task.time!.hour.toString().padLeft(2, '0')}:${task.time!.minute.toString().padLeft(2, '0')}',
                    style: TextStyle(
                      fontFamily: 'Inter',
                      color: Theme.of(context).colorScheme.onSurface.withOpacity(0.8),
                    ),
                  ),
                ],
                if (task.hasReminder) ...[
                  const SizedBox(width: 16),
                  Icon(
                    Icons.notifications,
                    size: 16,
                    color: Theme.of(context).colorScheme.primary,
                  ),
                  const SizedBox(width: 8),
                  Text(
                    'Reminder set',
                    style: TextStyle(
                      fontFamily: 'Inter',
                      color: Theme.of(context).colorScheme.primary,
                    ),
                  ),
                ],
              ],
            ),
            const SizedBox(height: 24),
            Row(
              children: [
                Expanded(
                  child: OutlinedButton(
                    onPressed: () => Navigator.of(context).pop(),
                    child: const Text('Close', style: TextStyle(fontFamily: 'Inter')),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: ElevatedButton(
                    onPressed: () {
                      Navigator.of(context).pop();
                      // Navigate to dashboard with selected date
                      // This would require navigation state management
                    },
                    child: const Text('Edit', style: TextStyle(fontFamily: 'Inter')),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  void _showAddTaskDialog(BuildContext context) {
    final selectedDate = _selectedDay ?? _focusedDay;
    final titleController = TextEditingController();
    final descriptionController = TextEditingController();
    String selectedCategory = 'Personal';
    Color selectedColor = Colors.blue;
    bool hasReminder = false;
    TimeOfDay? selectedTime;

    showDialog(
      context: context,
      builder: (context) => StatefulBuilder(
        builder: (context, setState) => AlertDialog(
          title: Text(
            'Add Task for ${selectedDate.day}/${selectedDate.month}/${selectedDate.year}',
            style: const TextStyle(fontFamily: 'Poppins', fontWeight: FontWeight.w600),
          ),
          content: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                TextField(
                  controller: titleController,
                  decoration: const InputDecoration(
                    labelText: 'Task Title',
                    border: OutlineInputBorder(),
                  ),
                  style: const TextStyle(fontFamily: 'Inter'),
                ),
                const SizedBox(height: 16),
                TextField(
                  controller: descriptionController,
                  decoration: const InputDecoration(
                    labelText: 'Description (Optional)',
                    border: OutlineInputBorder(),
                  ),
                  maxLines: 3,
                  style: const TextStyle(fontFamily: 'Inter'),
                ),
                const SizedBox(height: 16),
                DropdownButtonFormField<String>(
                  value: selectedCategory,
                  decoration: const InputDecoration(
                    labelText: 'Category',
                    border: OutlineInputBorder(),
                  ),
                  items: ['Work', 'Study', 'Health', 'Personal']
                      .map((category) => DropdownMenuItem(
                            value: category,
                            child: Text(category, style: const TextStyle(fontFamily: 'Inter')),
                          ))
                      .toList(),
                  onChanged: (value) {
                    setState(() {
                      selectedCategory = value!;
                    });
                  },
                ),
                const SizedBox(height: 16),
                Row(
                  children: [
                    const Text('Time: ', style: TextStyle(fontFamily: 'Inter')),
                    TextButton(
                      onPressed: () async {
                        final time = await showTimePicker(
                          context: context,
                          initialTime: TimeOfDay.now(),
                        );
                        if (time != null) {
                          setState(() {
                            selectedTime = time;
                          });
                        }
                      },
                      child: Text(
                        selectedTime?.format(context) ?? 'Select Time',
                        style: const TextStyle(fontFamily: 'Inter'),
                      ),
                    ),
                  ],
                ),
                Row(
                  children: [
                    const Text('Reminder: ', style: TextStyle(fontFamily: 'Inter')),
                    Switch(
                      value: hasReminder,
                      onChanged: (value) {
                        setState(() {
                          hasReminder = value;
                        });
                      },
                    ),
                  ],
                ),
              ],
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(context).pop(),
              child: const Text('Cancel', style: TextStyle(fontFamily: 'Inter')),
            ),
            ElevatedButton(
              onPressed: () {
                if (titleController.text.isNotEmpty) {
                  final task = Task(
                    id: DateTime.now().millisecondsSinceEpoch.toString(),
                    title: titleController.text,
                    description: descriptionController.text.isNotEmpty ? descriptionController.text : null,
                    date: selectedDate,
                    time: selectedTime != null
                        ? DateTime(selectedDate.year, selectedDate.month, selectedDate.day,
                            selectedTime!.hour, selectedTime!.minute)
                        : null,
                    category: selectedCategory,
                    color: selectedColor.value,
                    hasReminder: hasReminder,
                  );

                  context.read<DataProvider>().addTask(task);
                  Navigator.of(context).pop();
                }
              },
              child: const Text('Add Task', style: TextStyle(fontFamily: 'Inter')),
            ),
          ],
        ),
      ),
    );
  }
}
