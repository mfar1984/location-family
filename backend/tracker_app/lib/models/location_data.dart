class LocationData {
  final String deviceId;
  final String name;
  final double latitude;
  final double longitude;
  final double accuracy;
  final int batteryLevel;
  final int signalStrength;
  final bool microphoneStatus;
  final bool cameraStatus;
  final bool recordingStatus;
  final DateTime timestamp;
  final bool isStale;

  LocationData({
    required this.deviceId,
    required this.name,
    required this.latitude,
    required this.longitude,
    required this.accuracy,
    required this.batteryLevel,
    required this.signalStrength,
    required this.microphoneStatus,
    required this.cameraStatus,
    required this.recordingStatus,
    required this.timestamp,
    this.isStale = false,
  });

  factory LocationData.fromJson(Map<String, dynamic> json) {
    return LocationData(
      deviceId: json['deviceId'],
      name: json['name'],
      latitude: json['latitude'].toDouble(),
      longitude: json['longitude'].toDouble(),
      accuracy: json['accuracy']?.toDouble() ?? 0.0,
      batteryLevel: json['batteryLevel'],
      signalStrength: json['signalStrength'],
      microphoneStatus: json['microphoneStatus'] ?? false,
      cameraStatus: json['cameraStatus'] ?? false,
      recordingStatus: json['recordingStatus'] ?? false,
      timestamp: DateTime.now(),
      isStale: json['isStale'] ?? false,
    );
  }

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
      'timestamp': (timestamp.millisecondsSinceEpoch / 1000).round(),
    };
  }
}
