import 'package:campus_voting_app/models/nominee.dart';
import 'package:campus_voting_app/services/firebase_service.dart';
import 'package:flutter/material.dart';
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
  bool _isDarkMode = false; // Dark mode toggle

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
      return const Scaffold(
        body: Center(child: CircularProgressIndicator()),
      );
    }

    if (_hasVoted) {
      return const Scaffold(
        body: Center(
          child: Text(
            'You have already voted!',
            style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
          ),
        ),
      );
    }

    return MaterialApp(
      theme: _isDarkMode ? ThemeData.dark() : ThemeData.light(),
      home: Scaffold(
        appBar: AppBar(
          title: const Text('Vote for Your Leaders'),
          actions: [
            IconButton(
              icon: Icon(_isDarkMode ? Icons.light_mode : Icons.dark_mode),
              onPressed: () {
                setState(() {
                  _isDarkMode = !_isDarkMode;
                });
              },
            ),
          ],
        ),
        body: _nomineesByPosition.isEmpty
            ? const Center(child: Text('No nominees available'))
            : Padding(
                padding: const EdgeInsets.all(16.0),
                child: Column(
                  children: [
                    // Show all positions with nominees
                    Expanded(
                      child: ListView.builder(
                        itemCount: _nomineesByPosition.keys.length,
                        itemBuilder: (context, positionIndex) {
                          final position = _nomineesByPosition.keys.elementAt(positionIndex);
                          final nominees = _nomineesByPosition[position]!;

                          return Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Padding(
                                padding: const EdgeInsets.symmetric(vertical: 8.0),
                                child: Text(
                                  position,
                                  style: const TextStyle(
                                    fontSize: 20,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                              ),
                              ...nominees.map((nominee) {
                                return Card(
                                  elevation: 4,
                                  margin: const EdgeInsets.symmetric(vertical: 4),
                                  child: InkWell(
                                    onTap: () {
                                      setState(() {
                                        _selectedNominees[position] = nominee.id;
                                      });
                                    },
                                    child: Container(
                                      decoration: BoxDecoration(
                                        borderRadius: BorderRadius.circular(8),
                                        boxShadow: _selectedNominees[position] == nominee.id
                                            ? [
                                                BoxShadow(
                                                  color: Colors.blue.withOpacity(0.5),
                                                  spreadRadius: 2,
                                                  blurRadius: 8,
                                                  offset: const Offset(0, 0),
                                                ),
                                              ]
                                            : null,
                                      ),
                                      child: RadioListTile<String>(
                                        title: Text(
                                          nominee.name,
                                          style: const TextStyle(fontWeight: FontWeight.bold),
                                        ),
                                        subtitle: Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            Text('SAP ID: ${nominee.sapId}'),
                                            Text('Department: ${nominee.department}'),
                                            const SizedBox(height: 8),
                                            Text('Manifesto: ${nominee.manifesto}'),
                                          ],
                                        ),
                                        value: nominee.id,
                                        groupValue: _selectedNominees[position],
                                        onChanged: (value) {
                                          setState(() {
                                            _selectedNominees[position] = value;
                                          });
                                        },
                                      ),
                                    ),
                                  ),
                                );
                              }),
                              const SizedBox(height: 16),
                            ],
                          );
                        },
                      ),
                    ),
                    const SizedBox(height: 16),
                    ElevatedButton(
                      onPressed: _castVote,
                      style: ElevatedButton.styleFrom(
                        minimumSize: const Size(double.infinity, 50),
                        backgroundColor: Colors.blue,
                        foregroundColor: Colors.white,
                        elevation: 8,
                        shadowColor: Colors.blue.withOpacity(0.5),
                      ),
                      child: const Text('Cast Vote'),
                    ),
                  ],
                ),
              ),
      ),
    );
  }
}
