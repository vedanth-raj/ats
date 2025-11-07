import 'package:campus_voting_app/models/nominee.dart';
import 'package:campus_voting_app/services/firebase_service.dart';
import 'package:campus_voting_app/widgets/glassmorphic_widgets.dart';
import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:google_fonts/google_fonts.dart';
import 'thank_you_screen.dart';

class VotingScreen extends StatefulWidget {
  final String sapId;

  const VotingScreen({super.key, required this.sapId});

  @override
  State<VotingScreen> createState() => _VotingScreenState();
}

class _VotingScreenState extends State<VotingScreen> {
  final FirebaseService _firebaseService = FirebaseService();
  Map<String, List<Nominee>> _nomineesByPosition = {};
  bool _isLoading = true;
  Map<String, String?> _selectedNominees = {}; // Track selected nominee per position
  bool _hasVoted = false;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _isLoading = true);

    try {
      _nomineesByPosition = await _firebaseService.getNominees();
      _hasVoted = await _firebaseService.hasUserVoted(widget.sapId);

      // Initialize selected nominees map for all positions
      for (var position in _nomineesByPosition.keys) {
        _selectedNominees[position] = null;
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error loading data: $e')),
      );
    }

    setState(() => _isLoading = false);
  }

  void _castVote() async {
    // Check if all positions have a selected nominee
    bool allSelected = _selectedNominees.values.every((nomineeId) => nomineeId != null);
    if (!allSelected) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please select a nominee for all positions')),
      );
      return;
    }

    setState(() => _isLoading = true);

    try {
      // Cast votes for all selected nominees
      for (var entry in _selectedNominees.entries) {
        if (entry.value != null) {
          await _firebaseService.castVote(entry.key, entry.value!, widget.sapId);
        }
      }

      await _firebaseService.setUserVoted(widget.sapId, '', ''); // Name and phone already set

      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (context) => const ThankYouScreen()),
      );
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error casting vote: $e')),
      );
    }

    setState(() => _isLoading = false);
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return Scaffold(
        body: AnimatedBackground(
          child: Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Container(
                  width: 80,
                  height: 80,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    gradient: LinearGradient(
                      colors: [Colors.blue.shade400, Colors.purple.shade400],
                    ),
                  ),
                  child: const Padding(
                    padding: EdgeInsets.all(20),
                    child: CircularProgressIndicator(
                      color: Colors.white,
                      strokeWidth: 3,
                    ),
                  ),
                ).animate(onPlay: (controller) => controller.repeat()).rotate(duration: 2000.ms),
                const SizedBox(height: 24),
                Text(
                  'Loading nominees...',
                  style: GoogleFonts.poppins(
                    fontSize: 18,
                    color: Colors.white,
                    fontWeight: FontWeight.w500,
                  ),
                ).animate(onPlay: (controller) => controller.repeat()).fadeIn(duration: 800.ms).then().fadeOut(duration: 800.ms),
              ],
            ),
          ),
        ),
      );
    }

    if (_hasVoted) {
      return Scaffold(
        body: AnimatedBackground(
          child: Center(
            child: Padding(
              padding: const EdgeInsets.all(24.0),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  // Success Icon
                  Container(
                    width: 120,
                    height: 120,
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
                      size: 60,
                      color: Colors.white,
                    ),
                  ).animate().scale(duration: 600.ms, curve: Curves.elasticOut),
                  
                  const SizedBox(height: 32),
                  
                  GlassContainer(
                    width: double.infinity,
                    height: null,
                    padding: const EdgeInsets.all(32),
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(
                          'Already Voted!',
                          style: GoogleFonts.poppins(
                            fontSize: 28,
                            fontWeight: FontWeight.bold,
                            color: Colors.white,
                          ),
                          textAlign: TextAlign.center,
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'You have already cast your vote!',
                          style: GoogleFonts.poppins(
                            fontSize: 16,
                            color: Colors.white.withOpacity(0.9),
                            fontWeight: FontWeight.w500,
                          ),
                          textAlign: TextAlign.center,
                        ),
                        const SizedBox(height: 12),
                        Text(
                          'Your votes have been recorded successfully. Thank you for participating!',
                          style: GoogleFonts.poppins(
                            fontSize: 14,
                            color: Colors.white.withOpacity(0.7),
                          ),
                          textAlign: TextAlign.center,
                        ),
                      ],
                    ),
                  ).animate().fadeIn(delay: 300.ms, duration: 600.ms).slideY(begin: 0.2, end: 0),
                  
                  const SizedBox(height: 32),
                  
                  GlowingButton(
                    text: 'Back to Home',
                    icon: Icons.home,
                    onPressed: () {
                      Navigator.of(context).popUntil((route) => route.isFirst);
                    },
                    glowColor: Colors.green.shade600,
                  ).animate().fadeIn(delay: 600.ms, duration: 600.ms),
                ],
              ),
            ),
          ),
        ),
      );
    }

    return Scaffold(
      body: AnimatedBackground(
        child: SafeArea(
          child: Column(
            children: [
              // Header
              Padding(
                padding: const EdgeInsets.all(20.0),
                child: Column(
                  children: [
                    Row(
                      children: [
                        Container(
                          width: 50,
                          height: 50,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            color: Colors.white,
                            boxShadow: [
                              BoxShadow(
                                color: Colors.orange.withOpacity(0.5),
                                blurRadius: 15,
                                spreadRadius: 2,
                              ),
                            ],
                          ),
                          padding: const EdgeInsets.all(4),
                          child: ClipOval(
                            child: Image.asset(
                              'assets/images/college_logo.png',
                              fit: BoxFit.contain,
                              errorBuilder: (context, error, stackTrace) {
                                return Container(
                                  decoration: BoxDecoration(
                                    shape: BoxShape.circle,
                                    gradient: LinearGradient(
                                      colors: [Colors.orange.shade400, Colors.red.shade400],
                                    ),
                                  ),
                                  child: const Icon(Icons.school, color: Colors.white, size: 28),
                                );
                              },
                            ),
                          ),
                        ).animate().scale(duration: 600.ms, curve: Curves.elasticOut),
                        const SizedBox(width: 16),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Cast Your Vote',
                                style: GoogleFonts.poppins(
                                  fontSize: 24,
                                  fontWeight: FontWeight.bold,
                                  color: Colors.white,
                                ),
                              ).animate().fadeIn(duration: 600.ms).slideX(begin: -0.2, end: 0),
                              Text(
                                'Select one nominee per position',
                                style: GoogleFonts.poppins(
                                  fontSize: 12,
                                  color: Colors.white.withOpacity(0.8),
                                ),
                              ).animate().fadeIn(delay: 200.ms, duration: 600.ms),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              
              // Nominees List
              Expanded(
                child: _nomineesByPosition.isEmpty
                    ? Center(
                        child: GlassContainer(
                          width: 250,
                          height: null,
                          padding: const EdgeInsets.all(24),
                          child: Text(
                            'No nominees available',
                            style: GoogleFonts.poppins(
                              fontSize: 16,
                              color: Colors.white.withOpacity(0.9),
                            ),
                            textAlign: TextAlign.center,
                          ),
                        ),
                      )
                    : ListView.builder(
                        padding: const EdgeInsets.symmetric(horizontal: 20),
                        itemCount: _nomineesByPosition.keys.length,
                        itemBuilder: (context, positionIndex) {
                          final position = _nomineesByPosition.keys.elementAt(positionIndex);
                          final nominees = _nomineesByPosition[position]!;

                          return Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              // Position Title
                              Padding(
                                padding: const EdgeInsets.symmetric(vertical: 12.0),
                                child: Text(
                                  position,
                                  style: GoogleFonts.poppins(
                                    fontSize: 20,
                                    fontWeight: FontWeight.bold,
                                    color: Colors.white,
                                    shadows: [
                                      Shadow(
                                        color: Colors.black.withOpacity(0.3),
                                        blurRadius: 10,
                                      ),
                                    ],
                                  ),
                                ),
                              ).animate().fadeIn(duration: 400.ms).slideX(begin: -0.2, end: 0),
                              
                              // Nominees
                              ...nominees.asMap().entries.map((entry) {
                                final index = entry.key;
                                final nominee = entry.value;
                                final isSelected = _selectedNominees[position] == nominee.id;
                                
                                return GestureDetector(
                                  onTap: () {
                                    setState(() {
                                      _selectedNominees[position] = nominee.id;
                                    });
                                  },
                                  child: Container(
                                    margin: const EdgeInsets.only(bottom: 12),
                                    decoration: BoxDecoration(
                                      borderRadius: BorderRadius.circular(20),
                                      boxShadow: isSelected
                                          ? [
                                              BoxShadow(
                                                color: Colors.orange.withOpacity(0.6),
                                                blurRadius: 20,
                                                spreadRadius: 2,
                                              ),
                                            ]
                                          : [],
                                    ),
                                    child: GlassContainer(
                                      width: double.infinity,
                                      height: null,
                                      padding: const EdgeInsets.all(16),
                                      opacity: isSelected ? 0.2 : 0.1,
                                      border: Border.all(
                                        color: isSelected
                                            ? Colors.orange.withOpacity(0.8)
                                            : Colors.white.withOpacity(0.2),
                                        width: isSelected ? 2 : 1.5,
                                      ),
                                      child: Row(
                                        children: [
                                          // Radio indicator
                                          Container(
                                            width: 24,
                                            height: 24,
                                            decoration: BoxDecoration(
                                              shape: BoxShape.circle,
                                              border: Border.all(
                                                color: isSelected
                                                    ? Colors.orange
                                                    : Colors.white.withOpacity(0.5),
                                                width: 2,
                                              ),
                                              color: isSelected
                                                  ? Colors.orange
                                                  : Colors.transparent,
                                            ),
                                            child: isSelected
                                                ? const Icon(
                                                    Icons.check,
                                                    size: 16,
                                                    color: Colors.white,
                                                  )
                                                : null,
                                          ),
                                          const SizedBox(width: 16),
                                          
                                          // Nominee info
                                          Expanded(
                                            child: Column(
                                              crossAxisAlignment: CrossAxisAlignment.start,
                                              children: [
                                                Text(
                                                  nominee.name,
                                                  style: GoogleFonts.poppins(
                                                    fontSize: 16,
                                                    fontWeight: FontWeight.bold,
                                                    color: Colors.white,
                                                  ),
                                                ),
                                                const SizedBox(height: 4),
                                                Text(
                                                  'SAP ID: ${nominee.sapId}',
                                                  style: GoogleFonts.poppins(
                                                    fontSize: 12,
                                                    color: Colors.white.withOpacity(0.7),
                                                  ),
                                                ),
                                                Text(
                                                  'Dept: ${nominee.department}',
                                                  style: GoogleFonts.poppins(
                                                    fontSize: 12,
                                                    color: Colors.white.withOpacity(0.7),
                                                  ),
                                                ),
                                                const SizedBox(height: 8),
                                                Text(
                                                  nominee.manifesto,
                                                  style: GoogleFonts.poppins(
                                                    fontSize: 13,
                                                    color: Colors.white.withOpacity(0.8),
                                                    fontStyle: FontStyle.italic,
                                                  ),
                                                  maxLines: 2,
                                                  overflow: TextOverflow.ellipsis,
                                                ),
                                              ],
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                  ).animate().fadeIn(delay: (index * 100).ms, duration: 400.ms).slideX(begin: 0.2, end: 0),
                                );
                              }),
                              const SizedBox(height: 8),
                            ],
                          );
                        },
                      ),
              ),
              
              // Cast Vote Button
              Padding(
                padding: const EdgeInsets.all(20.0),
                child: GlowingButton(
                  text: 'Cast Vote',
                  icon: Icons.how_to_vote,
                  onPressed: _castVote,
                  isLoading: false,
                  glowColor: Colors.orange.shade600,
                ),
              ).animate().fadeIn(delay: 600.ms, duration: 600.ms).slideY(begin: 0.3, end: 0),
            ],
          ),
        ),
      ),
    );
  }
}
