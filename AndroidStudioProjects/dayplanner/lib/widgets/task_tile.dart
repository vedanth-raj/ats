import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/data_provider.dart';
import '../models/task.dart';

class TaskTile extends StatelessWidget {
  final Task task;

  const TaskTile({super.key, required this.task});

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      elevation: 2,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
      ),
      child: ListTile(
        leading: Checkbox(
          value: task.completed,
          onChanged: (value) {
            final updatedTask = Task(
              id: task.id,
              title: task.title,
              description: task.description,
              date: task.date,
              time: task.time,
              category: task.category,
              color: task.color,
              completed: value ?? false,
              hasReminder: task.hasReminder,
            );
            context.read<DataProvider>().updateTask(updatedTask);
          },
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(4),
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
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (task.description != null)
              Text(
                task.description!,
                style: TextStyle(
                  fontFamily: 'Inter',
                  fontSize: 12,
                  color: Theme.of(context).colorScheme.onSurface.withOpacity(0.7),
                ),
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
            const SizedBox(height: 4),
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                  decoration: BoxDecoration(
                    color: Color(task.color).withOpacity(0.1),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    task.category,
                    style: TextStyle(
                      fontFamily: 'Inter',
                      fontSize: 10,
                      color: Color(task.color),
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                ),
                if (task.time != null) ...[
                  const SizedBox(width: 8),
                  Icon(
                    Icons.access_time,
                    size: 14,
                    color: Theme.of(context).colorScheme.onSurface.withOpacity(0.6),
                  ),
                  const SizedBox(width: 4),
                  Text(
                    '${task.time!.hour.toString().padLeft(2, '0')}:${task.time!.minute.toString().padLeft(2, '0')}',
                    style: TextStyle(
                      fontFamily: 'Inter',
                      fontSize: 12,
                      color: Theme.of(context).colorScheme.onSurface.withOpacity(0.6),
                    ),
                  ),
                ],
                if (task.hasReminder) ...[
                  const SizedBox(width: 8),
                  Icon(
                    Icons.notifications,
                    size: 14,
                    color: Theme.of(context).colorScheme.primary,
                  ),
                ],
              ],
            ),
          ],
        ),
        trailing: PopupMenuButton<String>(
          onSelected: (value) {
            switch (value) {
              case 'edit':
                _showEditTaskDialog(context);
                break;
              case 'delete':
                _showDeleteConfirmation(context);
                break;
            }
          },
          itemBuilder: (context) => [
            const PopupMenuItem(
              value: 'edit',
              child: Row(
                children: [
                  Icon(Icons.edit),
                  SizedBox(width: 8),
                  Text('Edit', style: TextStyle(fontFamily: 'Inter')),
                ],
              ),
            ),
            const PopupMenuItem(
              value: 'delete',
              child: Row(
                children: [
                  Icon(Icons.delete, color: Colors.red),
                  SizedBox(width: 8),
                  Text('Delete', style: TextStyle(fontFamily: 'Inter')),
                ],
              ),
            ),
          ],
        ),
        onTap: () => _showEditTaskDialog(context),
      ),
    );
  }

  void _showEditTaskDialog(BuildContext context) {
    final titleController = TextEditingController(text: task.title);
    final descriptionController = TextEditingController(text: task.description ?? '');
    String selectedCategory = task.category;
    Color selectedColor = Color(task.color);
    bool hasReminder = task.hasReminder;
    TimeOfDay? selectedTime = task.time != null
        ? TimeOfDay(hour: task.time!.hour, minute: task.time!.minute)
        : null;

    showDialog(
      context: context,
      builder: (context) => StatefulBuilder(
        builder: (context, setState) => AlertDialog(
          title: const Text(
            'Edit Task',
            style: TextStyle(fontFamily: 'Poppins', fontWeight: FontWeight.w600),
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
                          initialTime: selectedTime ?? TimeOfDay.now(),
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
                  final updatedTask = Task(
                    id: task.id,
                    title: titleController.text,
                    description: descriptionController.text.isNotEmpty ? descriptionController.text : null,
                    date: task.date,
                    time: selectedTime != null
                        ? DateTime(task.date.year, task.date.month, task.date.day,
                            selectedTime!.hour, selectedTime!.minute)
                        : null,
                    category: selectedCategory,
                    color: selectedColor.value,
                    completed: task.completed,
                    hasReminder: hasReminder,
                  );

                  context.read<DataProvider>().updateTask(updatedTask);
                  Navigator.of(context).pop();
                }
              },
              child: const Text('Update', style: TextStyle(fontFamily: 'Inter')),
            ),
          ],
        ),
      ),
    );
  }

  void _showDeleteConfirmation(BuildContext context) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text(
          'Delete Task',
          style: TextStyle(fontFamily: 'Poppins', fontWeight: FontWeight.w600),
        ),
        content: const Text(
          'Are you sure you want to delete this task?',
          style: TextStyle(fontFamily: 'Inter'),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Cancel', style: TextStyle(fontFamily: 'Inter')),
          ),
          ElevatedButton(
            onPressed: () {
              context.read<DataProvider>().deleteTask(task.id);
              Navigator.of(context).pop();
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.red,
              foregroundColor: Colors.white,
            ),
            child: const Text('Delete', style: TextStyle(fontFamily: 'Inter')),
          ),
        ],
      ),
    );
  }
}
