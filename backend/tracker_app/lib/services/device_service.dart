import 'dart:convert';
import 'dart:math';
import 'package:http/http.dart' as http;
import 'package:device_info_plus/device_info_plus.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../models/device.dart';

class DeviceService {
  static const String baseUrl = 'http://localhost:8000/api';

  final FlutterSecureStorage _secureStorage = const FlutterSecureStorage();
  final DeviceInfoPlugin _deviceInfo = DeviceInfoPlugin();

  Future<void> initialize() async {
    // Initialize device service
  }

  Future<String> generateDeviceId() async {
    // Get device info for unique ID generation
    final deviceInfo = await _deviceInfo.androidInfo;
    final deviceModel = deviceInfo.model;
    final timestamp = DateTime.now().millisecondsSinceEpoch;
    final random = Random().nextInt(999999);

    return 'device-$deviceModel-$timestamp-$random'.toLowerCase().replaceAll(
      ' ',
      '-',
    );
  }

  Future<DeviceRegistrationResult> registerDevice({
    required String name,
    required String avatarType,
    required String avatarValue,
  }) async {
    try {
      // Get auth token from secure storage
      final authToken = await _secureStorage.read(key: 'auth_token');
      if (authToken == null) {
        return DeviceRegistrationResult(
          success: false,
          errorMessage: 'Authentication token not found',
        );
      }

      // Generate unique device ID
      final deviceId = await generateDeviceId();

      final response = await http.post(
        Uri.parse('$baseUrl/devices/register'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $authToken',
        },
        body: jsonEncode({
          'device_id': deviceId,
          'name': name,
          'avatar_type': avatarType,
          'avatar_value': avatarValue,
        }),
      );

      if (response.statusCode == 201) {
        final data = jsonDecode(response.body);
        final device = Device.fromJson(data['device']);

        // Store device info locally
        await _secureStorage.write(key: 'device_id', value: deviceId);
        await _secureStorage.write(key: 'device_name', value: name);
        await _secureStorage.write(
          key: 'device_data',
          value: jsonEncode(device.toJson()),
        );

        return DeviceRegistrationResult(success: true, device: device);
      } else {
        final data = jsonDecode(response.body);
        return DeviceRegistrationResult(
          success: false,
          errorMessage: data['message'] ?? 'Device registration failed',
        );
      }
    } catch (e) {
      return DeviceRegistrationResult(
        success: false,
        errorMessage: 'Network error: ${e.toString()}',
      );
    }
  }

  Future<List<AvatarIcon>> getAvatarIcons() async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/devices/avatar-icons'),
        headers: {'Content-Type': 'application/json'},
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        final List<dynamic> iconsJson = data['icons'];
        return iconsJson.map((json) => AvatarIcon.fromJson(json)).toList();
      } else {
        throw Exception('Failed to load avatar icons');
      }
    } catch (e) {
      throw Exception('Error loading avatar icons: ${e.toString()}');
    }
  }

  Future<String?> getStoredDeviceId() async {
    return await _secureStorage.read(key: 'device_id');
  }

  Future<String?> getStoredDeviceName() async {
    return await _secureStorage.read(key: 'device_name');
  }

  Future<Device?> getStoredDevice() async {
    final deviceData = await _secureStorage.read(key: 'device_data');
    if (deviceData != null) {
      return Device.fromJson(jsonDecode(deviceData));
    }
    return null;
  }

  Future<bool> isDeviceRegistered() async {
    final deviceId = await getStoredDeviceId();
    return deviceId != null;
  }

  Future<void> clearDeviceData() async {
    await _secureStorage.delete(key: 'device_id');
    await _secureStorage.delete(key: 'device_name');
    await _secureStorage.delete(key: 'device_data');
  }
}

class DeviceRegistrationResult {
  final bool success;
  final Device? device;
  final String? errorMessage;

  DeviceRegistrationResult({
    required this.success,
    this.device,
    this.errorMessage,
  });
}

class AvatarIcon {
  final String id;
  final String name;
  final String emoji;
  final String color;

  AvatarIcon({
    required this.id,
    required this.name,
    required this.emoji,
    required this.color,
  });

  factory AvatarIcon.fromJson(Map<String, dynamic> json) {
    return AvatarIcon(
      id: json['id'],
      name: json['name'],
      emoji: json['emoji'],
      color: json['color'],
    );
  }
}
