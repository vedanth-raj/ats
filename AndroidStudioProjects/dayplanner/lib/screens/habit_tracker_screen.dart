import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:flutter_staggered_animations/flutter_staggered_animations.dart';
import 'package:percent_indicator/percent_indicator.dart';
import '../providers/data_provider.dart';
import '../models/habit.dart';

class HabitTrackerScreen extends StatefulWidget {
  const HabitTrackerScreen({super.key});

  @override
  State<HabitTrackerScreen> createState() => _HabitTrackerScreenState();
}

class _HabitTrackerScreenState extends State<HabitTrackerScreen> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Consumer<DataProvider>(
        builder: (context, dataProvider, child) {
          final habits = dataProvider.habits;

          if (habits.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    Icons.track_changes,
                    size: 64,
                    color: Theme.of(context).colorScheme.onSurface.withOpacity(0.3),
                  ),
                  const SizedBox(height: 16),
                  Text(
                    'No habits yet',
                    style: TextStyle(
                      fontSize: 18,
                      color: Theme.of(context).colorScheme.onSurface.withOpacity(0.6),
                      fontFamily: 'Inter',
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Tap + to create your first habit',
                    style: TextStyle(
                      fontSize: 14,
                      color: Theme.of(context).colorScheme.onSurface.withOpacity(0.4),
                      fontFamily: 'Inter',
                    ),
                  ),
                ],
              ),
            );
          }

          return AnimationLimiter(
            child: ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: habits.length,
              itemBuilder: (context, index) {
                final habit = habits[index];
                return AnimationConfiguration.staggeredList(
                  position: index,
                  duration: const Duration(milliseconds: 375),
                  child: SlideAnimation(
                    horizontalOffset: 50.0,
                    child: FadeInAnimation(
                      child: _buildHabitCard(context, habit),
                    ),
                  ),
                );
              },
            ),
          );
        },
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () => _showAddHabitDialog(context),
        child: const Icon(Icons.add),
      ),
    );
  }

  Widget _buildHabitCard(BuildContext context, Habit habit) {
    final today = DateTime.now();
    final isCompletedToday = habit.isCompletedToday();
    final progress = habit.completedDates.length / habit.target;
    final streakText = habit.streak == 1 ? '1 day' : '${habit.streak} days';

    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      elevation: 2,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
      ),
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(
                  child: Text(
                    habit.title,
                    style: const TextStyle(
                      fontFamily: 'Poppins',
                      fontWeight: FontWeight.w600,
                      fontSize: 18,
                    ),
                  ),
                ),
                PopupMenuButton<String>(
                  onSelected: (value) {
                    switch (value) {
                      case 'edit':
                        _showEditHabitDialog(context, habit);
                        break;
                      case 'delete':
                        _showDeleteConfirmation(context, habit);
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
              ],
            ),
            const SizedBox(height: 16),
            Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Current Streak',
                        style: TextStyle(
                          fontFamily: 'Inter',
                          fontSize: 12,
                          color: Theme.of(context).colorScheme.onSurface.withOpacity(0.6),
                        ),
                      ),
                      Text(
                        streakText,
                        style: const TextStyle(
                          fontFamily: 'Poppins',
                          fontWeight: FontWeight.w600,
                          fontSize: 16,
                        ),
                      ),
                    ],
                  ),
                ),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'This Week',
                        style: TextStyle(
                          fontFamily: 'Inter',
                          fontSize: 12,
                          color: Theme.of(context).colorScheme.onSurface.withOpacity(0.6),
                        ),
                      ),
                      Text(
                        '${habit.completedDates.length}/${habit.target}',
                        style: const TextStyle(
                          fontFamily: 'Poppins',
                          fontWeight: FontWeight.w600,
                          fontSize: 16,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 20),
            LinearPercentIndicator(
              percent: progress.clamp(0.0, 1.0),
              lineHeight: 8,
              backgroundColor: Theme.of(context).colorScheme.surfaceVariant,
              progressColor: Theme.of(context).colorScheme.primary,
              barRadius: const Radius.circular(4),
            ),
            const SizedBox(height: 20),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: () {
                  if (isCompletedToday) {
                    context.read<DataProvider>().markHabitIncomplete(habit.id);
                  } else {
                    context.read<DataProvider>().markHabitCompleted(habit.id);
                  }
                },
                icon: Icon(isCompletedToday ? Icons.undo : Icons.check),
                label: Text(
                  isCompletedToday ? 'Mark Incomplete' : 'Mark Complete',
                  style: const TextStyle(fontFamily: 'Inter'),
                ),
                style: ElevatedButton.styleFrom(
                  backgroundColor: isCompletedToday
                      ? Theme.of(context).colorScheme.surfaceVariant
                      : Theme.of(context).colorScheme.primary,
                  foregroundColor: isCompletedToday
                      ? Theme.of(context).colorScheme.onSurface
                      : Theme.of(context).colorScheme.onPrimary,
                  padding: const EdgeInsets.symmetric(vertical: 12),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(8),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _showAddHabitDialog(BuildContext context) {
    final titleController = TextEditingController();
    int target = 7; // Default weekly

    showDialog(
      context: context,
      builder: (context) => StatefulBuilder(
        builder: (context, setState) => AlertDialog(
          title: const Text(
            'Add New Habit',
            style: TextStyle(fontFamily: 'Poppins', fontWeight: FontWeight.w600),
          ),
          content: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                TextField(
                  controller: titleController,
                  decoration: const InputDecoration(
                    labelText: 'Habit Title',
                    border: OutlineInputBorder(),
                  ),
                  style: const TextStyle(fontFamily: 'Inter'),
                ),
                const SizedBox(height: 16),
                DropdownButtonFormField<int>(
                  value: target,
                  decoration: const InputDecoration(
                    labelText: 'Target (per week)',
                    border: OutlineInputBorder(),
                  ),
                  items: [3, 5, 7, 10, 14]
                      .map((value) => DropdownMenuItem(
                            value: value,
                            child: Text('$value days', style: const TextStyle(fontFamily: 'Inter')),
                          ))
                      .toList(),
                  onChanged: (value) {
                    setState(() {
                      target = value!;
                    });
                  },
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
                  final habit = Habit(
                    id: DateTime.now().millisecondsSinceEpoch.toString(),
                    title: titleController.text,
                    target: target,
                  );

                  context.read<DataProvider>().addHabit(habit);
                  Navigator.of(context).pop();
                }
              },
              child: const Text('Add Habit', style: TextStyle(fontFamily: 'Inter')),
            ),
          ],
        ),
      ),
    );
  }

  void _showEditHabitDialog(BuildContext context, Habit habit) {
    final titleController = TextEditingController(text: habit.title);
    int target = habit.target;

    showDialog(
      context: context,
      builder: (context) => StatefulBuilder(
        builder: (context, setState) => AlertDialog(
          title: const Text(
            'Edit Habit',
            style: TextStyle(fontFamily: 'Poppins', fontWeight: FontWeight.w600),
          ),
          content: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                TextField(
                  controller: titleController,
                  decoration: const InputDecoration(
                    labelText: 'Habit Title',
                    border: OutlineInputBorder(),
                  ),
                  style: const TextStyle(fontFamily: 'Inter'),
                ),
                const SizedBox(height: 16),
                DropdownButtonFormField<int>(
                  value: target,
                  decoration: const InputDecoration(
                    labelText: 'Target (per week)',
                    border: OutlineInputBorder(),
                  ),
                  items: [3, 5, 7, 10, 14]
                      .map((value) => DropdownMenuItem(
                            value: value,
                            child: Text('$value days', style: const TextStyle(fontFamily: 'Inter')),
                          ))
                      .toList(),
                  onChanged: (value) {
                    setState(() {
                      target = value!;
                    });
                  },
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
                  final updatedHabit = Habit(
                    id: habit.id,
                    title: titleController.text,
                    streak: habit.streak,
                    target: target,
                    completedDates: habit.completedDates,
                  );

                  context.read<DataProvider>().updateHabit(updatedHabit);
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

  void _showDeleteConfirmation(BuildContext context, Habit habit) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text(
          'Delete Habit',
          style: TextStyle(fontFamily: 'Poppins', fontWeight: FontWeight.w600),
        ),
        content: const Text(
          'Are you sure you want to delete this habit? This action cannot be undone.',
          style: TextStyle(fontFamily: 'Inter'),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Cancel', style: TextStyle(fontFamily: 'Inter')),
          ),
          ElevatedButton(
            onPressed: () {
              context.read<DataProvider>().deleteHabit(habit.id);
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
