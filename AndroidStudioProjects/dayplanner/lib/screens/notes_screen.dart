import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:flutter_staggered_animations/flutter_staggered_animations.dart';
import '../providers/data_provider.dart';
import '../models/note.dart';

class NotesScreen extends StatefulWidget {
  const NotesScreen({super.key});

  @override
  State<NotesScreen> createState() => _NotesScreenState();
}

class _NotesScreenState extends State<NotesScreen> {
  final TextEditingController _searchController = TextEditingController();
  String _searchQuery = '';

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Column(
        children: [
          // Search bar
          Padding(
            padding: const EdgeInsets.all(16),
            child: TextField(
              controller: _searchController,
              decoration: InputDecoration(
                hintText: 'Search notes...',
                prefixIcon: const Icon(Icons.search),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                filled: true,
                fillColor: Theme.of(context).colorScheme.surface,
              ),
              style: const TextStyle(fontFamily: 'Inter'),
              onChanged: (value) {
                setState(() {
                  _searchQuery = value.toLowerCase();
                });
              },
            ),
          ),

          // Notes list
          Expanded(
            child: Consumer<DataProvider>(
              builder: (context, dataProvider, child) {
                final allNotes = dataProvider.notes;
                final filteredNotes = _searchQuery.isEmpty
                    ? allNotes
                    : allNotes.where((note) =>
                        note.title.toLowerCase().contains(_searchQuery) ||
                        note.content.toLowerCase().contains(_searchQuery) ||
                        note.tags.any((tag) => tag.toLowerCase().contains(_searchQuery))
                      ).toList();

                filteredNotes.sort((a, b) => b.date.compareTo(a.date));

                if (filteredNotes.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.note_alt,
                          size: 64,
                          color: Theme.of(context).colorScheme.onSurface.withOpacity(0.3),
                        ),
                        const SizedBox(height: 16),
                        Text(
                          _searchQuery.isEmpty ? 'No notes yet' : 'No notes found',
                          style: TextStyle(
                            fontSize: 18,
                            color: Theme.of(context).colorScheme.onSurface.withOpacity(0.6),
                            fontFamily: 'Inter',
                          ),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          'Tap + to create your first note',
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
                    padding: const EdgeInsets.symmetric(horizontal: 16),
                    itemCount: filteredNotes.length,
                    itemBuilder: (context, index) {
                      final note = filteredNotes[index];
                      return AnimationConfiguration.staggeredList(
                        position: index,
                        duration: const Duration(milliseconds: 375),
                        child: SlideAnimation(
                          horizontalOffset: 50.0,
                          child: FadeInAnimation(
                            child: Card(
                              margin: const EdgeInsets.only(bottom: 12),
                              elevation: 2,
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: ListTile(
                                title: Text(
                                  note.title,
                                  style: const TextStyle(
                                    fontFamily: 'Poppins',
                                    fontWeight: FontWeight.w600,
                                    fontSize: 16,
                                  ),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                                subtitle: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      note.content.replaceAll('\n', ' ').replaceAll(RegExp(r'#+\s*'), ''),
                                      style: TextStyle(
                                        fontFamily: 'Inter',
                                        fontSize: 14,
                                        color: Theme.of(context).colorScheme.onSurface.withOpacity(0.7),
                                      ),
                                      maxLines: 2,
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                    const SizedBox(height: 4),
                                    Row(
                                      children: [
                                        Text(
                                          '${note.date.day}/${note.date.month}/${note.date.year}',
                                          style: TextStyle(
                                            fontFamily: 'Inter',
                                            fontSize: 12,
                                            color: Theme.of(context).colorScheme.onSurface.withOpacity(0.5),
                                          ),
                                        ),
                                        if (note.tags.isNotEmpty) ...[
                                          const SizedBox(width: 8),
                                          Expanded(
                                            child: Wrap(
                                              spacing: 4,
                                              runSpacing: 2,
                                              children: note.tags.take(3).map((tag) => Container(
                                                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                                decoration: BoxDecoration(
                                                  color: Theme.of(context).colorScheme.primary.withOpacity(0.1),
                                                  borderRadius: BorderRadius.circular(8),
                                                ),
                                                child: Text(
                                                  '#$tag',
                                                  style: TextStyle(
                                                    fontFamily: 'Inter',
                                                    fontSize: 10,
                                                    color: Theme.of(context).colorScheme.primary,
                                                    fontWeight: FontWeight.w500,
                                                  ),
                                                ),
                                              )).toList(),
                                            ),
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
                                        _showEditNoteDialog(context, note);
                                        break;
                                      case 'delete':
                                        _showDeleteConfirmation(context, note);
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
                                onTap: () => _showEditNoteDialog(context, note),
                              ),
                            ),
                          ),
                        ),
                      );
                    },
                  ),
                );
              },
            ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () => _showEditNoteDialog(context, null),
        child: const Icon(Icons.add),
      ),
    );
  }

  void _showEditNoteDialog(BuildContext context, Note? note) {
    final titleController = TextEditingController(text: note?.title ?? '');
    final contentController = TextEditingController(text: note?.content ?? '');
    final tagsController = TextEditingController(text: note?.tags.join(', ') ?? '');

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(
          note == null ? 'New Note' : 'Edit Note',
          style: const TextStyle(fontFamily: 'Poppins', fontWeight: FontWeight.w600),
        ),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextField(
                controller: titleController,
                decoration: const InputDecoration(
                  labelText: 'Note Title',
                  border: OutlineInputBorder(),
                ),
                style: const TextStyle(fontFamily: 'Inter'),
              ),
              const SizedBox(height: 16),
              TextField(
                controller: contentController,
                decoration: const InputDecoration(
                  labelText: 'Content (Markdown supported)',
                  border: OutlineInputBorder(),
                  alignLabelWithHint: true,
                ),
                maxLines: 8,
                style: const TextStyle(fontFamily: 'Inter'),
              ),
              const SizedBox(height: 16),
              TextField(
                controller: tagsController,
                decoration: const InputDecoration(
                  labelText: 'Tags (comma separated)',
                  border: OutlineInputBorder(),
                  hintText: 'work, personal, ideas',
                ),
                style: const TextStyle(fontFamily: 'Inter'),
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
                final tags = tagsController.text
                    .split(',')
                    .map((tag) => tag.trim())
                    .where((tag) => tag.isNotEmpty)
                    .toList();

                if (note == null) {
                  // Create new note
                  final newNote = Note(
                    id: DateTime.now().millisecondsSinceEpoch.toString(),
                    title: titleController.text,
                    content: contentController.text,
                    date: DateTime.now(),
                    tags: tags,
                  );
                  context.read<DataProvider>().addNote(newNote);
                } else {
                  // Update existing note
                  final updatedNote = Note(
                    id: note.id,
                    title: titleController.text,
                    content: contentController.text,
                    date: note.date,
                    tags: tags,
                  );
                  context.read<DataProvider>().updateNote(updatedNote);
                }
                Navigator.of(context).pop();
              }
            },
            child: Text(
              note == null ? 'Create' : 'Update',
              style: const TextStyle(fontFamily: 'Inter'),
            ),
          ),
        ],
      ),
    );
  }

  void _showDeleteConfirmation(BuildContext context, Note note) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text(
          'Delete Note',
          style: TextStyle(fontFamily: 'Poppins', fontWeight: FontWeight.w600),
        ),
        content: const Text(
          'Are you sure you want to delete this note?',
          style: TextStyle(fontFamily: 'Inter'),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Cancel', style: TextStyle(fontFamily: 'Inter')),
          ),
          ElevatedButton(
            onPressed: () {
              context.read<DataProvider>().deleteNote(note.id);
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
