/// DevicePing model representing location and device status data
/// transmitted from the tracker app to the backend.
///
/// Validates Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 4.6, 4.7
class DevicePing {
  final String deviceId;
  final String name;
  final double latitude;
  final double longitude;
  final double accuracy;
  final int batteryLevel;
  final int signalStrength;
  final bool? microphoneStatus;
  final bool? cameraStatus;
  final bool? recordingStatus;
  final int timestamp;

  DevicePing({
    required this.deviceId,
    required this.name,
    required this.latitude,
    required this.longitude,
    required this.accuracy,
    required this.batteryLevel,
    required this.signalStrength,
    this.microphoneStatus,
    this.cameraStatus,
    this.recordingStatus,
    required this.timestamp,
  });

  /// Convert DevicePing to JSON for API transmission
  Map<String, dynamic> toJson() {
    return {
      'deviceId': deviceId,
      'name': name,
      'latitude': latitude,
      'longitude': longitude,
      'accuracy': accuracy,
      'batteryLevel': batteryLevel,
      'signalStrength': signalStrength,
      'microphoneStatus': microphoneStatus,
      'cameraStatus': cameraStatus,
      'recordingStatus': recordingStatus,
      'timestamp': timestamp,
    };
  }

  /// Create DevicePing from JSON response
  factory DevicePing.fromJson(Map<String, dynamic> json) {
    return DevicePing(
      deviceId: json['deviceId'] as String,
      name: json['name'] as String,
      latitude: (json['latitude'] as num).toDouble(),
      longitude: (json['longitude'] as num).toDouble(),
      accuracy: (json['accuracy'] as num).toDouble(),
      batteryLevel: json['batteryLevel'] as int,
      signalStrength: json['signalStrength'] as int,
      microphoneStatus: json['microphoneStatus'] as bool?,
      cameraStatus: json['cameraStatus'] as bool?,
      recordingStatus: json['recordingStatus'] as bool?,
      timestamp: json['timestamp'] as int,
    );
  }
}
