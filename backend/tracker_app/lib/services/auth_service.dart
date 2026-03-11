import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../models/user.dart';

class AuthService {
  static const String baseUrl = 'http://localhost:8000/api';
  static const String webUrl = 'http://localhost:8000';

  final FlutterSecureStorage _secureStorage = const FlutterSecureStorage();

  Future<void> initialize() async {
    // Initialize secure storage
  }

  Future<AuthResult> login(String username, String password) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/login'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({'username': username, 'password': password}),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        final token = data['token'];
        final user = User.fromJson(data['user']);

        // Store token securely
        await _secureStorage.write(key: 'auth_token', value: token);
        await _secureStorage.write(
          key: 'user_data',
          value: jsonEncode(user.toJson()),
        );

        return AuthResult(success: true, user: user, token: token);
      } else if (response.statusCode == 404) {
        return AuthResult(
          success: false,
          errorMessage:
              'Account not found. Please register at $webUrl first before using the mobile app.',
        );
      } else {
        final data = jsonDecode(response.body);
        return AuthResult(
          success: false,
          errorMessage: data['message'] ?? 'Login failed',
        );
      }
    } catch (e) {
      return AuthResult(
        success: false,
        errorMessage: 'Network error: ${e.toString()}',
      );
    }
  }

  Future<void> logout() async {
    await _secureStorage.delete(key: 'auth_token');
    await _secureStorage.delete(key: 'user_data');
  }

  Future<String?> getStoredToken() async {
    return await _secureStorage.read(key: 'auth_token');
  }

  Future<User?> getStoredUser() async {
    final userData = await _secureStorage.read(key: 'user_data');
    if (userData != null) {
      return User.fromJson(jsonDecode(userData));
    }
    return null;
  }

  Future<bool> isAuthenticated() async {
    final token = await getStoredToken();
    return token != null;
  }
}

class AuthResult {
  final bool success;
  final String? token;
  final User? user;
  final String? errorMessage;

  AuthResult({required this.success, this.token, this.user, this.errorMessage});
}
