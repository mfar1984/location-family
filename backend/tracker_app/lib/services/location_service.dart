import 'dart:async';
import 'dart:convert';
import 'package:geolocator/geolocator.dart';
import 'package:battery_plus/battery_plus.dart';
import 'package:http/http.dart' as http;
import 'package:permission_handler/permission_handler.dart';
import '../models/location_data.dart';
import 'dart:developer' as developer;

class LocationService {
  static const String baseUrl = 'http://localhost:8000/api';

  final Battery _battery = Battery();
  StreamSubscription<Position>? _positionStream;
  Timer? _pingTimer;
  StreamController<LocationData>? _locationController;

  String? _deviceId;
  String? _deviceName;
  String? _authToken;

  Stream<LocationData> get locationStream =>
      _locationController?.stream ?? const Stream.empty();

  Future<void> initialize() async {
    // Initialize location service
    _locationController = StreamController<LocationData>.broadcast();
  }

  Future<bool> requestPermissions() async {
    // Request location permission
    final locationStatus = await Permission.location.request();
    if (locationStatus != PermissionStatus.granted) {
      return false;
    }

    // Request location always permission for background tracking
    final locationAlwaysStatus = await Permission.locationAlways.request();
    if (locationAlwaysStatus != PermissionStatus.granted) {
      // Try to get when in use permission at least
      final locationWhenInUse = await Permission.locationWhenInUse.request();
      return locationWhenInUse == PermissionStatus.granted;
    }

    return true;
  }

  Future<void> startTracking({
    required String deviceId,
    required String deviceName,
    required String authToken,
  }) async {
    _deviceId = deviceId;
    _deviceName = deviceName;
    _authToken = authToken;

    // Check if location services are enabled
    bool serviceEnabled = await Geolocator.isLocationServiceEnabled();
    if (!serviceEnabled) {
      throw Exception(
        'Location services are disabled. Please enable location services to continue.',
      );
    }

    // Start continuous location tracking
    const LocationSettings locationSettings = LocationSettings(
      accuracy: LocationAccuracy.high,
      distanceFilter: 10, // Update every 10 meters
    );

    _positionStream =
        Geolocator.getPositionStream(locationSettings: locationSettings).listen(
          (Position position) {
            _sendLocationPing(position);
          },
        );

    // Also send pings every 2 minutes regardless of movement
    _pingTimer = Timer.periodic(const Duration(minutes: 2), (timer) async {
      try {
        final position = await Geolocator.getCurrentPosition();
        _sendLocationPing(position);
      } catch (e) {
        developer.log('Error getting current position: $e');
      }
    });
  }

  Future<void> stopTracking() async {
    await _positionStream?.cancel();
    _pingTimer?.cancel();
    _positionStream = null;
    _pingTimer = null;
  }

  Future<void> _sendLocationPing(Position position) async {
    if (_deviceId == null || _deviceName == null || _authToken == null) return;

    try {
      // Get battery level
      final batteryLevel = await _battery.batteryLevel;

      // Create location data
      final locationData = LocationData(
        deviceId: _deviceId!,
        name: _deviceName!,
        latitude: position.latitude,
        longitude: position.longitude,
        accuracy: position.accuracy,
        batteryLevel: batteryLevel,
        signalStrength: -50, // Mock signal strength for now
        microphoneStatus: false, // Will be updated when microphone is accessed
        cameraStatus: false, // Will be updated when camera is accessed
        recordingStatus: false, // Will be updated when recording
        timestamp: DateTime.now(),
      );

      // Send to backend
      final response = await http.post(
        Uri.parse('$baseUrl/pings'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode(locationData.toJson()),
      );

      if (response.statusCode == 201) {
        developer.log('Location ping sent successfully');
        // Emit to stream
        _locationController?.add(locationData);
      } else {
        developer.log('Failed to send location ping: ${response.statusCode}');
      }
    } catch (e) {
      developer.log('Error sending location ping: $e');
      // TODO: Queue for retry when network is available
    }
  }

  Future<LocationData?> getCurrentLocation() async {
    try {
      final position = await Geolocator.getCurrentPosition();
      final batteryLevel = await _battery.batteryLevel;

      return LocationData(
        deviceId: _deviceId ?? '',
        name: _deviceName ?? '',
        latitude: position.latitude,
        longitude: position.longitude,
        accuracy: position.accuracy,
        batteryLevel: batteryLevel,
        signalStrength: -50,
        microphoneStatus: false,
        cameraStatus: false,
        recordingStatus: false,
        timestamp: DateTime.now(),
      );
    } catch (e) {
      developer.log('Error getting current location: $e');
      return null;
    }
  }

  void dispose() {
    _positionStream?.cancel();
    _pingTimer?.cancel();
    _locationController?.close();
  }
}
