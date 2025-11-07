import 'package:campus_voting_app/services/firebase_service.dart';
import 'package:campus_voting_app/widgets/glassmorphic_widgets.dart';
import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:google_fonts/google_fonts.dart';
import 'otp_screen.dart';
import 'registration_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _sapIdController = TextEditingController();
  final _passwordController = TextEditingController();
  final _phoneController = TextEditingController();
  final FirebaseService _firebaseService = FirebaseService();

  String? _verificationId;
  bool _isLoading = false;

  void _login() async {
    if (_formKey.currentState!.validate()) {
      setState(() => _isLoading = true);

      String sapId = _sapIdController.text.trim();
      String password = _passwordController.text.trim();

      try {
        Map<String, dynamic>? user = await _firebaseService.getUser(sapId);
        if (user != null && user['password'] == password) {
          // Check if user has already voted
          bool hasVoted = await _firebaseService.hasUserVoted(sapId);
          
          if (hasVoted) {
            setState(() => _isLoading = false);
            // Show dialog that user has already voted
            showDialog(
              context: context,
              barrierDismissible: false,
              builder: (BuildContext context) {
                return AlertDialog(
                  backgroundColor: Colors.white.withOpacity(0.95),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(20),
                  ),
                  title: Row(
                    children: [
                      Icon(Icons.check_circle, color: Colors.green.shade600, size: 30),
                      const SizedBox(width: 12),
                      const Text(
                        'Already Voted',
                        style: TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 20,
                        ),
                      ),
                    ],
                  ),
                  content: Column(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'You have already voted!',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        'Your votes have been recorded successfully. Thank you for participating in the campus voting!',
                        style: TextStyle(
                          fontSize: 14,
                          color: Colors.grey.shade700,
                        ),
                      ),
                    ],
                  ),
                  actions: [
                    TextButton(
                      onPressed: () {
                        Navigator.of(context).pop();
                      },
                      style: TextButton.styleFrom(
                        backgroundColor: Colors.green.shade600,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(10),
                        ),
                      ),
                      child: const Text('OK'),
                    ),
                  ],
                );
              },
            );
            return;
          }
          
          String phone = user['phone'];
          String name = user['name'];
          _phoneController.text = phone;

          await _firebaseService.signInWithPhone(
            phone,
            onCodeSent: (String verificationId) {
              setState(() => _isLoading = false);
              _verificationId = verificationId;
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (context) => OTPScreen(
                    sapId: sapId,
                    name: name,
                    phone: phone,
                    verificationId: verificationId,
                  ),
                ),
              );
            },
            onError: (String error) {
              setState(() => _isLoading = false);
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(content: Text('Phone verification failed: $error')),
              );
            },
          );
        } else {
          setState(() => _isLoading = false);
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Invalid SAP ID or password')),
          );
        }
      } catch (e) {
        setState(() => _isLoading = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Login failed: $e')),
        );
      }
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
                            color: Colors.purple.withOpacity(0.5),
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
                                  colors: [Colors.purple.shade400, Colors.blue.shade400],
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
                      'Welcome Back',
                      style: GoogleFonts.poppins(
                        fontSize: 36,
                        fontWeight: FontWeight.bold,
                        color: Colors.white,
                        shadows: [
                          Shadow(
                            color: Colors.purple.withOpacity(0.5),
                            blurRadius: 20,
                          ),
                        ],
                      ),
                    ).animate().fadeIn(duration: 600.ms).slideY(begin: -0.3, end: 0),
                    
                    const SizedBox(height: 8),
                    Text(
                      'Login to cast your vote',
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
                            controller: _passwordController,
                            label: 'Password',
                            prefixIcon: Icons.lock,
                            obscureText: true,
                            validator: (value) {
                              if (value == null || value.isEmpty) {
                                return 'Please enter your password';
                              }
                              return null;
                            },
                          ),
                        ],
                      ),
                    ).animate().fadeIn(delay: 400.ms, duration: 600.ms).slideY(begin: 0.2, end: 0),
                    
                    const SizedBox(height: 32),
                    
                    // Login Button
                    GlowingButton(
                      text: 'Login & Send OTP',
                      icon: Icons.login,
                      onPressed: _login,
                      isLoading: _isLoading,
                      glowColor: Colors.purple.shade600,
                    ).animate().fadeIn(delay: 600.ms, duration: 600.ms).slideY(begin: 0.2, end: 0),
                    
                    const SizedBox(height: 24),
                    
                    // Register Link
                    GestureDetector(
                      onTap: () {
                        Navigator.pushReplacement(
                          context,
                          MaterialPageRoute(builder: (context) => const RegistrationScreen()),
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
                              'New here? ',
                              style: GoogleFonts.poppins(
                                color: Colors.white.withOpacity(0.8),
                                fontSize: 14,
                              ),
                            ),
                            Text(
                              'Register now',
                              style: GoogleFonts.poppins(
                                color: Colors.purple.shade300,
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
    _passwordController.dispose();
    _phoneController.dispose();
    super.dispose();
  }
}
