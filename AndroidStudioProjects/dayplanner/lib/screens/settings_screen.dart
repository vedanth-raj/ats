import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:share_plus/share_plus.dart';
import 'package:file_picker/file_picker.dart';
import 'dart:typed_data';
import '../providers/theme_provider.dart';
import '../providers/data_provider.dart';
import '../services/database_service.dart';

class SettingsScreen extends StatefulWidget {
  const SettingsScreen({super.key});

  @override
  State<SettingsScreen> createState() => _SettingsScreenState();
}

class _SettingsScreenState extends State<SettingsScreen> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          _buildSectionTitle('Appearance'),
          _buildThemeSettings(),
          const SizedBox(height: 24),
          _buildSectionTitle('Data Management'),
          _buildDataManagement(),
          const SizedBox(height: 24),
          _buildSectionTitle('About'),
          _buildAboutSection(),
        ],
      ),
    );
  }

  Widget _buildSectionTitle(String title) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Text(
        title,
        style: TextStyle(
          fontFamily: 'Poppins',
          fontWeight: FontWeight.w600,
          fontSize: 18,
          color: Theme.of(context).colorScheme.primary,
        ),
      ),
    );
  }

  Widget _buildThemeSettings() {
    return Consumer<ThemeProvider>(
      builder: (context, themeProvider, child) {
        return Card(
          elevation: 2,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
          child: Column(
            children: [
              ListTile(
                title: const Text(
                  'Theme Mode',
                  style: TextStyle(fontFamily: 'Inter', fontWeight: FontWeight.w500),
                ),
                subtitle: Text(
                  _getThemeModeText(themeProvider.themeMode),
                  style: TextStyle(
                    fontFamily: 'Inter',
                    color: Theme.of(context).colorScheme.onSurface.withOpacity(0.6),
                  ),
                ),
                trailing: DropdownButton<ThemeMode>(
                  value: themeProvider.themeMode,
                  items: [
                    DropdownMenuItem(
                      value: ThemeMode.system,
                      child: Text('System', style: const TextStyle(fontFamily: 'Inter')),
                    ),
                    DropdownMenuItem(
                      value: ThemeMode.light,
                      child: Text('Light', style: const TextStyle(fontFamily: 'Inter')),
                    ),
                    DropdownMenuItem(
                      value: ThemeMode.dark,
                      child: Text('Dark', style: const TextStyle(fontFamily: 'Inter')),
                    ),
                  ],
                  onChanged: (value) {
                    if (value != null) {
                      themeProvider.setThemeMode(value);
                    }
                  },
                ),
              ),
              const Divider(height: 1),
              SwitchListTile(
                title: const Text(
                  'AMOLED Dark Mode',
                  style: TextStyle(fontFamily: 'Inter', fontWeight: FontWeight.w500),
                ),
                subtitle: const Text(
                  'Pure black background for OLED displays',
                  style: TextStyle(fontFamily: 'Inter'),
                ),
                value: themeProvider.isAmoled,
                onChanged: (value) {
                  themeProvider.setAmoled(value);
                },
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildDataManagement() {
    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(
        children: [
          ListTile(
            leading: const Icon(Icons.download),
            title: const Text(
              'Export Data',
              style: TextStyle(fontFamily: 'Inter', fontWeight: FontWeight.w500),
            ),
            subtitle: const Text(
              'Save all your data as JSON file',
              style: TextStyle(fontFamily: 'Inter'),
            ),
            onTap: _exportData,
          ),
          const Divider(height: 1),
          ListTile(
            leading: const Icon(Icons.upload),
            title: const Text(
              'Import Data',
              style: TextStyle(fontFamily: 'Inter', fontWeight: FontWeight.w500),
            ),
            subtitle: const Text(
              'Restore data from JSON file',
              style: TextStyle(fontFamily: 'Inter'),
            ),
            onTap: _importData,
          ),
          const Divider(height: 1),
          ListTile(
            leading: const Icon(Icons.delete_forever, color: Colors.red),
            title: const Text(
              'Clear All Data',
              style: TextStyle(fontFamily: 'Inter', fontWeight: FontWeight.w500, color: Colors.red),
            ),
            subtitle: const Text(
              'Permanently delete all data',
              style: TextStyle(fontFamily: 'Inter'),
            ),
            onTap: _showClearDataConfirmation,
          ),
        ],
      ),
    );
  }

  Widget _buildAboutSection() {
    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
      ),
      child: const Padding(
        padding: EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Day Planner',
              style: TextStyle(
                fontFamily: 'Poppins',
                fontWeight: FontWeight.w600,
                fontSize: 20,
              ),
            ),
            SizedBox(height: 8),
            Text(
              'Version 1.0.0',
              style: TextStyle(
                fontFamily: 'Inter',
                color: Colors.grey,
              ),
            ),
            SizedBox(height: 16),
            Text(
              'A comprehensive day planning app with tasks, notes, habits, and calendar features. All data is stored locally for complete privacy.',
              style: TextStyle(
                fontFamily: 'Inter',
                height: 1.5,
              ),
            ),
          ],
        ),
      ),
    );
  }

  String _getThemeModeText(ThemeMode mode) {
    switch (mode) {
      case ThemeMode.system:
        return 'Follow system setting';
      case ThemeMode.light:
        return 'Always light';
      case ThemeMode.dark:
        return 'Always dark';
    }
  }

  Future<void> _exportData() async {
    try {
      final dataProvider = context.read<DataProvider>();
      final data = await dataProvider.exportData();

      final jsonString = data.toString();
      final fileName = 'day_planner_backup_${DateTime.now().millisecondsSinceEpoch}.json';

      await Share.shareXFiles(
        [XFile.fromData(Uint8List.fromList(jsonString.codeUnits), name: fileName, mimeType: 'application/json')],
        text: 'Day Planner Data Backup',
      );

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Data exported successfully', style: TextStyle(fontFamily: 'Inter')),
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Failed to export data: $e', style: const TextStyle(fontFamily: 'Inter')),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  Future<void> _importData() async {
    try {
      final result = await FilePicker.platform.pickFiles(
        type: FileType.custom,
        allowedExtensions: ['json'],
      );

      if (result != null && result.files.isNotEmpty) {
        final file = result.files.first;
        final jsonString = String.fromCharCodes(file.bytes!);
        final data = jsonString as Map<String, dynamic>;

        await context.read<DataProvider>().importData(data);

        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Data imported successfully', style: TextStyle(fontFamily: 'Inter')),
            ),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Failed to import data: $e', style: const TextStyle(fontFamily: 'Inter')),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  void _showClearDataConfirmation() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text(
          'Clear All Data',
          style: TextStyle(fontFamily: 'Poppins', fontWeight: FontWeight.w600),
        ),
        content: const Text(
          'This will permanently delete all your tasks, notes, habits, and settings. This action cannot be undone.',
          style: TextStyle(fontFamily: 'Inter'),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Cancel', style: TextStyle(fontFamily: 'Inter')),
          ),
          ElevatedButton(
            onPressed: () async {
              try {
                final databaseService = DatabaseService();
                await databaseService.tasksBox.clear();
                await databaseService.notesBox.clear();
                await databaseService.habitsBox.clear();
                await databaseService.remindersBox.clear();
                await databaseService.settingsBox.clear();

                context.read<DataProvider>().loadData();

                Navigator.of(context).pop();

                if (mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      content: Text('All data cleared', style: TextStyle(fontFamily: 'Inter')),
                    ),
                  );
                }
              } catch (e) {
                Navigator.of(context).pop();
                if (mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text('Failed to clear data: $e', style: const TextStyle(fontFamily: 'Inter')),
                      backgroundColor: Colors.red,
                    ),
                  );
                }
              }
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.red,
              foregroundColor: Colors.white,
            ),
            child: const Text('Clear All', style: TextStyle(fontFamily: 'Inter')),
          ),
        ],
      ),
    );
  }
}
