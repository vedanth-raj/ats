import 'package:flutter/material.dart';
import '../services/database_service.dart';

class ThemeProvider extends ChangeNotifier {
  ThemeMode _themeMode = ThemeMode.system;
  Color _accentColor = Colors.blue;
  bool _isAmoled = false;

  ThemeMode get themeMode => _themeMode;
  Color get accentColor => _accentColor;
  bool get isAmoled => _isAmoled;

  ThemeProvider() {
    _loadSettings();
  }

  Future<void> _loadSettings() async {
    final databaseService = DatabaseService();
    final themeModeString = databaseService.getSetting('themeMode', defaultValue: 'system');
    final accentColorValue = databaseService.getSetting('accentColor', defaultValue: Colors.blue.value);
    final isAmoledValue = databaseService.getSetting('isAmoled', defaultValue: false);

    _themeMode = _stringToThemeMode(themeModeString);
    _accentColor = Color(accentColorValue);
    _isAmoled = isAmoledValue;

    notifyListeners();
  }

  Future<void> setThemeMode(ThemeMode mode) async {
    _themeMode = mode;
    final databaseService = DatabaseService();
    await databaseService.saveSetting('themeMode', _themeModeToString(mode));
    notifyListeners();
  }

  Future<void> setAccentColor(Color color) async {
    _accentColor = color;
    final databaseService = DatabaseService();
    await databaseService.saveSetting('accentColor', color.value);
    notifyListeners();
  }

  Future<void> setAmoled(bool value) async {
    _isAmoled = value;
    final databaseService = DatabaseService();
    await databaseService.saveSetting('isAmoled', value);
    notifyListeners();
  }

  ThemeData getLightTheme() {
    return ThemeData(
      useMaterial3: true,
      fontFamily: 'Inter',
      colorScheme: ColorScheme.fromSeed(
        seedColor: _accentColor,
        brightness: Brightness.light,
      ),
      scaffoldBackgroundColor: _isAmoled ? Colors.black : null,
      cardTheme: CardThemeData(
        elevation: 2,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(12),
        ),
      ),
      appBarTheme: AppBarTheme(
        elevation: 0,
        backgroundColor: _isAmoled ? Colors.black : null,
        foregroundColor: _isAmoled ? Colors.white : null,
      ),
      floatingActionButtonTheme: FloatingActionButtonThemeData(
        backgroundColor: _accentColor,
        foregroundColor: Colors.white,
      ),
    );
  }

  ThemeData getDarkTheme() {
    return ThemeData(
      useMaterial3: true,
      fontFamily: 'Inter',
      colorScheme: ColorScheme.fromSeed(
        seedColor: _accentColor,
        brightness: Brightness.dark,
      ),
      scaffoldBackgroundColor: _isAmoled ? Colors.black : null,
      cardTheme: CardThemeData(
        elevation: 2,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(12),
        ),
      ),
      appBarTheme: AppBarTheme(
        elevation: 0,
        backgroundColor: _isAmoled ? Colors.black : null,
      ),
      floatingActionButtonTheme: FloatingActionButtonThemeData(
        backgroundColor: _accentColor,
        foregroundColor: Colors.white,
      ),
    );
  }

  ThemeMode _stringToThemeMode(String mode) {
    switch (mode) {
      case 'light':
        return ThemeMode.light;
      case 'dark':
        return ThemeMode.dark;
      case 'system':
      default:
        return ThemeMode.system;
    }
  }

  String _themeModeToString(ThemeMode mode) {
    switch (mode) {
      case ThemeMode.light:
        return 'light';
      case ThemeMode.dark:
        return 'dark';
      case ThemeMode.system:
      default:
        return 'system';
    }
  }
}
