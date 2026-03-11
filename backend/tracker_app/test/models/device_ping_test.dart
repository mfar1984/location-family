import 'package:flutter_test/flutter_test.dart';
import 'package:umrah_family_tracker/models/device_ping.dart';

void main() {
  group('DevicePing', () {
    test('toJson should serialize all fields correctly', () {
      final ping = DevicePing(
        deviceId: 'test-device-123',
        name: 'Test User',
        latitude: 21.4225,
        longitude: 39.8262,
        accuracy: 15.5,
        batteryLevel: 85,
        signalStrength: -70,
        microphoneStatus: false,
        cameraStatus: false,
        recordingStatus: false,
        timestamp: 1704067200000,
      );

      final json = ping.toJson();

      expect(json['deviceId'], 'test-device-123');
      expect(json['name'], 'Test User');
      expect(json['latitude'], 21.4225);
      expect(json['longitude'], 39.8262);
      expect(json['accuracy'], 15.5);
      expect(json['batteryLevel'], 85);
      expect(json['signalStrength'], -70);
      expect(json['microphoneStatus'], false);
      expect(json['cameraStatus'], false);
      expect(json['recordingStatus'], false);
      expect(json['timestamp'], 1704067200000);
    });

    test('toJson should handle null optional fields', () {
      final ping = DevicePing(
        deviceId: 'test-device-123',
        name: 'Test User',
        latitude: 21.4225,
        longitude: 39.8262,
        accuracy: 15.5,
        batteryLevel: 85,
        signalStrength: -70,
        timestamp: 1704067200000,
      );

      final json = ping.toJson();

      expect(json['microphoneStatus'], null);
      expect(json['cameraStatus'], null);
      expect(json['recordingStatus'], null);
    });

    test('fromJson should deserialize all fields correctly', () {
      final json = {
        'deviceId': 'test-device-123',
        'name': 'Test User',
        'latitude': 21.4225,
        'longitude': 39.8262,
        'accuracy': 15.5,
        'batteryLevel': 85,
        'signalStrength': -70,
        'microphoneStatus': false,
        'cameraStatus': false,
        'recordingStatus': false,
        'timestamp': 1704067200000,
      };

      final ping = DevicePing.fromJson(json);

      expect(ping.deviceId, 'test-device-123');
      expect(ping.name, 'Test User');
      expect(ping.latitude, 21.4225);
      expect(ping.longitude, 39.8262);
      expect(ping.accuracy, 15.5);
      expect(ping.batteryLevel, 85);
      expect(ping.signalStrength, -70);
      expect(ping.microphoneStatus, false);
      expect(ping.cameraStatus, false);
      expect(ping.recordingStatus, false);
      expect(ping.timestamp, 1704067200000);
    });

    test('fromJson should handle null optional fields', () {
      final json = {
        'deviceId': 'test-device-123',
        'name': 'Test User',
        'latitude': 21.4225,
        'longitude': 39.8262,
        'accuracy': 15.5,
        'batteryLevel': 85,
        'signalStrength': -70,
        'microphoneStatus': null,
        'cameraStatus': null,
        'recordingStatus': null,
        'timestamp': 1704067200000,
      };

      final ping = DevicePing.fromJson(json);

      expect(ping.microphoneStatus, null);
      expect(ping.cameraStatus, null);
      expect(ping.recordingStatus, null);
    });

    test('toJson and fromJson should be reversible', () {
      final original = DevicePing(
        deviceId: 'test-device-123',
        name: 'Test User',
        latitude: 21.4225,
        longitude: 39.8262,
        accuracy: 15.5,
        batteryLevel: 85,
        signalStrength: -70,
        microphoneStatus: true,
        cameraStatus: false,
        recordingStatus: null,
        timestamp: 1704067200000,
      );

      final json = original.toJson();
      final deserialized = DevicePing.fromJson(json);

      expect(deserialized.deviceId, original.deviceId);
      expect(deserialized.name, original.name);
      expect(deserialized.latitude, original.latitude);
      expect(deserialized.longitude, original.longitude);
      expect(deserialized.accuracy, original.accuracy);
      expect(deserialized.batteryLevel, original.batteryLevel);
      expect(deserialized.signalStrength, original.signalStrength);
      expect(deserialized.microphoneStatus, original.microphoneStatus);
      expect(deserialized.cameraStatus, original.cameraStatus);
      expect(deserialized.recordingStatus, original.recordingStatus);
      expect(deserialized.timestamp, original.timestamp);
    });

    test('fromJson should handle numeric types correctly', () {
      // Test that integers can be parsed as doubles for coordinates
      final json = {
        'deviceId': 'test-device-123',
        'name': 'Test User',
        'latitude': 21,
        'longitude': 39,
        'accuracy': 15,
        'batteryLevel': 85,
        'signalStrength': -70,
        'timestamp': 1704067200000,
      };

      final ping = DevicePing.fromJson(json);

      expect(ping.latitude, 21.0);
      expect(ping.longitude, 39.0);
      expect(ping.accuracy, 15.0);
    });

    test('should handle boundary coordinate values', () {
      final ping = DevicePing(
        deviceId: 'test-device-123',
        name: 'Test User',
        latitude: -90.0,
        longitude: -180.0,
        accuracy: 0.0,
        batteryLevel: 0,
        signalStrength: -120,
        timestamp: 0,
      );

      final json = ping.toJson();
      final deserialized = DevicePing.fromJson(json);

      expect(deserialized.latitude, -90.0);
      expect(deserialized.longitude, -180.0);
      expect(deserialized.accuracy, 0.0);
      expect(deserialized.batteryLevel, 0);
    });

    test('should handle maximum coordinate values', () {
      final ping = DevicePing(
        deviceId: 'test-device-123',
        name: 'Test User',
        latitude: 90.0,
        longitude: 180.0,
        accuracy: 1000.0,
        batteryLevel: 100,
        signalStrength: 0,
        timestamp: 9999999999999,
      );

      final json = ping.toJson();
      final deserialized = DevicePing.fromJson(json);

      expect(deserialized.latitude, 90.0);
      expect(deserialized.longitude, 180.0);
      expect(deserialized.batteryLevel, 100);
    });
  });
}
