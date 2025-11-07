import 'package:campus_voting_app/services/firebase_service.dart';
import 'package:campus_voting_app/widgets/glassmorphic_widgets.dart';
import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:google_fonts/google_fonts.dart';
import 'login_screen.dart';

class RegistrationScreen extends StatefulWidget {
  const RegistrationScreen({super.key});

  @override
  State<RegistrationScreen> createState() => _RegistrationScreenState();
}

class _RegistrationScreenState extends State<RegistrationScreen> {
  final _formKey = GlobalKey<FormState>();
  final _sapIdController = TextEditingController();
  final _nameController = TextEditingController();
  final _phoneController = TextEditingController();
  final _passwordController = TextEditingController();
  final FirebaseService _firebaseService = FirebaseService();
  bool _isLoading = false;

  void _register() async {
    if (_formKey.currentState!.validate()) {
      setState(() => _isLoading = true);

      String sapId = _sapIdController.text.trim();
      String name = _nameController.text.trim();
      String phone = _phoneController.text.trim();
      String password = _passwordController.text.trim();

      try {
        // Check if user already exists
        Map<String, dynamic>? existingUser = await _firebaseService.getUser(sapId);
        if (existingUser != null) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('SAP ID already registered')),
          );
          setState(() => _isLoading = false);
          return;
        }

        // Register user
        await _firebaseService.registerUser(sapId, name, phone, password);

        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Registration successful! Please login.')),
        );

        // Navigate to login screen
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(builder: (context) => const LoginScreen()),
        );
      } catch (e) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Registration failed: $e')),
        );
      }

      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: AnimatedBackground(
        child: SafeArea(
          child: Center(
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(24.0),
              child: Form(
                key: _formKey,
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    // College Logo
                    Container(
                      width: 120,
                      height: 120,
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        color: Colors.white,
                        boxShadow: [
                          BoxShadow(
                            color: Colors.blue.withOpacity(0.5),
                            blurRadius: 30,
                            spreadRadius: 5,
                          ),
                        ],
                      ),
                      padding: const EdgeInsets.all(8),
                      child: ClipOval(
                        child: Image.asset(
                          'assets/images/college_logo.png',
                          fit: BoxFit.contain,
                          errorBuilder: (context, error, stackTrace) {
                            return Container(
                              decoration: BoxDecoration(
                                shape: BoxShape.circle,
                                gradient: LinearGradient(
                                  colors: [Colors.blue.shade400, Colors.purple.shade400],
                                ),
                              ),
                              child: const Icon(Icons.school, size: 50, color: Colors.white),
                            );
                          },
                        ),
                      ),
                    ).animate().scale(duration: 600.ms, curve: Curves.elasticOut),
                    const SizedBox(height: 24),
                    
                    // Title
                    Text(
                      'Student Registration',
                      style: GoogleFonts.poppins(
                        fontSize: 32,
                        fontWeight: FontWeight.bold,
                        color: Colors.white,
                        shadows: [
                          Shadow(
                            color: Colors.blue.withOpacity(0.5),
                            blurRadius: 20,
                          ),
                        ],
                      ),
                    ).animate().fadeIn(duration: 600.ms).slideY(begin: -0.3, end: 0),
                    
                    const SizedBox(height: 8),
                    Text(
                      'Join the democratic process',
                      style: GoogleFonts.poppins(
                        fontSize: 14,
                        color: Colors.white.withOpacity(0.8),
                      ),
                    ).animate().fadeIn(delay: 200.ms, duration: 600.ms),
                    
                    const SizedBox(height: 40),
                    
                    // Glass Container with Form
                    GlassContainer(
                      width: double.infinity,
                      height: null,
                      padding: const EdgeInsets.all(24),
                      child: Column(
                        children: [
                          GlassTextField(
                            controller: _sapIdController,
                            label: 'SAP ID',
                            prefixIcon: Icons.badge,
                            validator: (value) {
                              if (value == null || value.isEmpty) {
                                return 'Please enter your SAP ID';
                              }
                              return null;
                            },
                          ),
                          const SizedBox(height: 16),
                          
                          GlassTextField(
                            controller: _nameController,
                            label: 'Full Name',
                            prefixIcon: Icons.person,
                            validator: (value) {
                              if (value == null || value.isEmpty) {
                                return 'Please enter your full name';
                              }
                              return null;
                            },
                          ),
                          const SizedBox(height: 16),
                          
                          GlassTextField(
                            controller: _phoneController,
                            label: 'Phone Number',
                            hint: '+91XXXXXXXXXX',
                            prefixIcon: Icons.phone,
                            keyboardType: TextInputType.phone,
                            validator: (value) {
                              if (value == null || value.isEmpty) {
                                return 'Please enter your phone number';
                              }
                              if (!RegExp(r'^\+[0-9]{10,15}$').hasMatch(value)) {
                                return 'Enter valid number with country code';
                              }
                              return null;
                            },
                          ),
                          const SizedBox(height: 16),
                          
                          GlassTextField(
                            controller: _passwordController,
                            label: 'Password',
                            prefixIcon: Icons.lock,
                            obscureText: true,
                            validator: (value) {
                              if (value == null || value.isEmpty) {
                                return 'Please enter a password';
                              }
                              if (value.length < 6) {
                                return 'Password must be at least 6 characters';
                              }
                              return null;
                            },
                          ),
                        ],
                      ),
                    ).animate().fadeIn(delay: 400.ms, duration: 600.ms).slideY(begin: 0.2, end: 0),
                    
                    const SizedBox(height: 32),
                    
                    // Register Button
                    GlowingButton(
                      text: 'Register',
                      icon: Icons.app_registration,
                      onPressed: _register,
                      isLoading: _isLoading,
                      glowColor: Colors.blue.shade600,
                    ).animate().fadeIn(delay: 600.ms, duration: 600.ms).slideY(begin: 0.2, end: 0),
                    
                    const SizedBox(height: 24),
                    
                    // Login Link
                    GestureDetector(
                      onTap: () {
                        Navigator.pushReplacement(
                          context,
                          MaterialPageRoute(builder: (context) => const LoginScreen()),
                        );
                      },
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                        decoration: BoxDecoration(
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: Colors.white.withOpacity(0.3)),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Text(
                              'Already registered? ',
                              style: GoogleFonts.poppins(
                                color: Colors.white.withOpacity(0.8),
                                fontSize: 14,
                              ),
                            ),
                            Text(
                              'Login here',
                              style: GoogleFonts.poppins(
                                color: Colors.blue.shade300,
                                fontSize: 14,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ).animate().fadeIn(delay: 800.ms, duration: 600.ms),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }

  @override
  void dispose() {
    _sapIdController.dispose();
    _nameController.dispose();
    _phoneController.dispose();
    _passwordController.dispose();
    super.dispose();
  }
}
