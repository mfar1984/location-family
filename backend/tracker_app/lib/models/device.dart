class Device {
  final String deviceId;
  final String name;
  final String avatarType;
  final String avatarValue;
  final bool isActive;
  final DateTime? lastSeen;

  Device({
    required this.deviceId,
    required this.name,
    required this.avatarType,
    required this.avatarValue,
    required this.isActive,
    this.lastSeen,
  });

  factory Device.fromJson(Map<String, dynamic> json) {
    return Device(
      deviceId: json['device_id'],
      name: json['name'],
      avatarType: json['avatar_type'],
      avatarValue: json['avatar_value'],
      isActive: json['is_active'] ?? true,
      lastSeen: json['last_seen'] != null
          ? DateTime.parse(json['last_seen'])
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'device_id': deviceId,
      'name': name,
      'avatar_type': avatarType,
      'avatar_value': avatarValue,
      'is_active': isActive,
      'last_seen': lastSeen?.toIso8601String(),
    };
  }
}
