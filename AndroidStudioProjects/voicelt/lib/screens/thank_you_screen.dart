import 'package:campus_voting_app/widgets/glassmorphic_widgets.dart';
import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:google_fonts/google_fonts.dart';
import 'dart:math' as math;

class ThankYouScreen extends StatelessWidget {
  const ThankYouScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: AnimatedBackground(
        child: SafeArea(
          child: Center(
            child: Padding(
              padding: const EdgeInsets.all(24.0),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  // Success Icon with animation
                  Container(
                    width: 150,
                    height: 150,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      gradient: LinearGradient(
                        colors: [Colors.green.shade400, Colors.teal.shade400],
                      ),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.green.withOpacity(0.6),
                          blurRadius: 40,
                          spreadRadius: 10,
                        ),
                      ],
                    ),
                    child: const Icon(
                      Icons.check_circle,
                      size: 80,
                      color: Colors.white,
                    ),
                  )
                      .animate()
                      .scale(duration: 600.ms, curve: Curves.elasticOut)
                      .then()
                      .shimmer(duration: 1500.ms, color: Colors.white.withOpacity(0.5)),
                  
                  const SizedBox(height: 40),
                  
                  // Thank you text
                  Text(
                    'Thank You!',
                    style: GoogleFonts.poppins(
                      fontSize: 48,
                      fontWeight: FontWeight.bold,
                      color: Colors.white,
                      shadows: [
                        Shadow(
                          color: Colors.green.withOpacity(0.5),
                          blurRadius: 20,
                        ),
                      ],
                    ),
                  ).animate().fadeIn(delay: 300.ms, duration: 600.ms).slideY(begin: -0.3, end: 0),
                  
                  const SizedBox(height: 16),
                  
                  // Success message
                  GlassContainer(
                    width: double.infinity,
                    height: null,
                    padding: const EdgeInsets.all(24),
                    child: Column(
                      children: [
                        Icon(
                          Icons.how_to_vote,
                          size: 40,
                          color: Colors.white.withOpacity(0.9),
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'Your vote has been recorded successfully!',
                          style: GoogleFonts.poppins(
                            fontSize: 18,
                            color: Colors.white.withOpacity(0.9),
                            fontWeight: FontWeight.w500,
                          ),
                          textAlign: TextAlign.center,
                        ),
                        const SizedBox(height: 12),
                        Text(
                          'Your voice matters in shaping the future of our campus.',
                          style: GoogleFonts.poppins(
                            fontSize: 14,
                            color: Colors.white.withOpacity(0.7),
                          ),
                          textAlign: TextAlign.center,
                        ),
                      ],
                    ),
                  ).animate().fadeIn(delay: 600.ms, duration: 600.ms).slideY(begin: 0.2, end: 0),
                  
                  const SizedBox(height: 40),
                  
                  // Done button
                  GlowingButton(
                    text: 'Done',
                    icon: Icons.home,
                    onPressed: () {
                      Navigator.of(context).popUntil((route) => route.isFirst);
                    },
                    glowColor: Colors.green.shade600,
                  ).animate().fadeIn(delay: 900.ms, duration: 600.ms).slideY(begin: 0.2, end: 0),
                  
                  const SizedBox(height: 24),
                  
                  // Confetti effect indicator
                  Text(
                    '🎉 🎊 ✨',
                    style: const TextStyle(fontSize: 32),
                  )
                      .animate(onPlay: (controller) => controller.repeat())
                      .fadeIn(duration: 600.ms)
                      .then()
                      .shake(duration: 800.ms, hz: 2),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
