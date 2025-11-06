import 'package:firebase_auth/firebase_auth.dart';
import 'package:firebase_database/firebase_database.dart';
import '../models/nominee.dart';

class FirebaseService {
  final FirebaseAuth _auth = FirebaseAuth.instance;
  final DatabaseReference _dbRef = FirebaseDatabase.instance.ref();

  // Authentication methods
  Future<ConfirmationResult> signInWithPhone(String phoneNumber) async {
    return await _auth.signInWithPhoneNumber(phoneNumber);
  }

  Future<UserCredential> verifyOTP(ConfirmationResult confirmationResult, String smsCode) async {
    return await confirmationResult.confirm(smsCode);
  }

  User? get currentUser => _auth.currentUser;

  Future<void> signOut() async {
    await _auth.signOut();
  }

  // Database methods
  Future<Map<String, List<Nominee>>> getNominees() async {
    Map<String, List<Nominee>> nomineesByPosition = {};

    try {
      DatabaseEvent event = await _dbRef.child('nominees').once();
      if (event.snapshot.value != null) {
        Map<dynamic, dynamic> positions = event.snapshot.value as Map<dynamic, dynamic>;

        positions.forEach((position, nomineesData) {
          List<Nominee> nominees = [];
          if (nomineesData is Map<dynamic, dynamic>) {
            nomineesData.forEach((id, nomineeData) {
              if (nomineeData is Map<dynamic, dynamic>) {
                nominees.add(Nominee.fromJson(id, nomineeData));
              }
            });
          }
          nomineesByPosition[position] = nominees;
        });
      }
    } catch (e) {
      print('Error fetching nominees: $e');
    }

    return nomineesByPosition;
  }

  Future<bool> hasUserVoted(String sapId) async {
    try {
      DatabaseEvent event = await _dbRef.child('users/$sapId/has_voted').once();
      return event.snapshot.value == true;
    } catch (e) {
      print('Error checking vote status: $e');
      return false;
    }
  }

  Future<void> registerUser(String sapId, String name, String phone, String password) async {
    await _dbRef.child('users/$sapId').set({
      'name': name,
      'sap_id': sapId,
      'phone': phone,
      'password': password, // In production, hash the password
      'has_voted': false,
    });
  }

  Future<Map<String, dynamic>?> getUser(String sapId) async {
    try {
      DatabaseEvent event = await _dbRef.child('users/$sapId').once();
      if (event.snapshot.value != null) {
        return Map<String, dynamic>.from(event.snapshot.value as Map);
      }
    } catch (e) {
      print('Error fetching user: $e');
    }
    return null;
  }

  Future<void> setUserLoggedIn(String sapId, String name, String phone) async {
    await _dbRef.child('users/$sapId').update({
      'name': name,
      'sap_id': sapId,
      'phone': phone,
      'logged_in': true,
    });
  }

  Future<void> setUserVoted(String sapId, String name, String phone) async {
    await _dbRef.child('users/$sapId').update({
      'name': name,
      'sap_id': sapId,
      'phone': phone,
      'has_voted': true,
    });
  }

  Future<void> castVote(String position, String nomineeId, String voterSapId) async {
    DatabaseReference voteRef = _dbRef.child('votes/$position/$nomineeId/count');
    DatabaseReference votersRef = _dbRef.child('votes/$position/$nomineeId/voters');

    try {
      DatabaseEvent event = await voteRef.once();
      int currentCount = event.snapshot.value as int? ?? 0;
      await voteRef.set(currentCount + 1);

      // Add voter SAP ID to the list
      DatabaseEvent votersEvent = await votersRef.once();
      List<dynamic> voters = List.from(votersEvent.snapshot.value as List<dynamic>? ?? []);
      voters.add(voterSapId);
      await votersRef.set(voters);
    } catch (e) {
      print('Error casting vote: $e');
      // If count doesn't exist, set it to 1 and initialize voters list
      await voteRef.set(1);
      await votersRef.set([voterSapId]);
    }
  }

  Future<Map<String, Map<String, int>>> getVoteCounts() async {
    Map<String, Map<String, int>> voteCounts = {};

    try {
      DatabaseEvent event = await _dbRef.child('votes').once();
      if (event.snapshot.value != null) {
        Map<dynamic, dynamic> positions = event.snapshot.value as Map<dynamic, dynamic>;

        positions.forEach((position, nomineesData) {
          Map<String, int> nomineeCounts = {};
          if (nomineesData is Map<dynamic, dynamic>) {
            nomineesData.forEach((nomineeId, voteData) {
              if (voteData is Map<dynamic, dynamic>) {
                nomineeCounts[nomineeId] = voteData['count'] as int? ?? 0;
              }
            });
          }
          voteCounts[position] = nomineeCounts;
        });
      }
    } catch (e) {
      print('Error fetching vote counts: $e');
    }

    return voteCounts;
  }


}
