class Nominee {
  final String id;
  final String name;
  final String sapId;
  final String department;
  final String manifesto;

  Nominee({
    required this.id,
    required this.name,
    required this.sapId,
    required this.department,
    required this.manifesto,
  });

  factory Nominee.fromJson(String id, Map<dynamic, dynamic> json) {
    return Nominee(
      id: id,
      name: json['name'] ?? '',
      sapId: json['sap_id'] ?? '',
      department: json['department'] ?? '',
      manifesto: json['manifesto'] ?? '',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'name': name,
      'sap_id': sapId,
      'department': department,
      'manifesto': manifesto,
    };
  }
}
