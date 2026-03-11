import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../blocs/auth/auth_bloc.dart';
import '../../blocs/location/location_bloc.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Family Tracker'),
        backgroundColor: Colors.blue,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.logout),
            onPressed: () {
              context.read<AuthBloc>().add(AuthLogoutRequested());
            },
          ),
        ],
      ),
      body: BlocBuilder<LocationBloc, LocationState>(
        builder: (context, locationState) {
          return Padding(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // Status Card
                Card(
                  child: Padding(
                    padding: const EdgeInsets.all(16.0),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Tracking Status',
                          style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 8),
                        _buildStatusRow(locationState),
                      ],
                    ),
                  ),
                ),

                const SizedBox(height: 16),

                // Current Location Card
                if (locationState is LocationTracking &&
                    locationState.currentLocation != null)
                  Card(
                    child: Padding(
                      padding: const EdgeInsets.all(16.0),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Current Location',
                            style: TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(height: 8),
                          _buildLocationInfo(locationState.currentLocation!),
                        ],
                      ),
                    ),
                  ),

                const SizedBox(height: 16),

                // Security Features Card
                Card(
                  child: Padding(
                    padding: const EdgeInsets.all(16.0),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Security Features',
                          style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 8),
                        _buildSecurityFeatures(),
                      ],
                    ),
                  ),
                ),

                const Spacer(),

                // Emergency Notice
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: Colors.red.shade50,
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: Colors.red.shade200),
                  ),
                  child: const Column(
                    children: [
                      Icon(Icons.warning, color: Colors.red, size: 24),
                      SizedBox(height: 8),
                      Text(
                        'Important Notice',
                        style: TextStyle(
                          fontWeight: FontWeight.bold,
                          color: Colors.red,
                        ),
                      ),
                      SizedBox(height: 4),
                      Text(
                        'This app provides continuous family safety monitoring. Location tracking cannot be disabled and runs in the background at all times.',
                        textAlign: TextAlign.center,
                        style: TextStyle(color: Colors.red),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildStatusRow(LocationState locationState) {
    IconData icon;
    String status;
    Color color;

    if (locationState is LocationTracking) {
      icon = Icons.gps_fixed;
      status = 'Active - Tracking Location';
      color = Colors.green;
    } else if (locationState is LocationPermissionRequired) {
      icon = Icons.gps_off;
      status = 'Permission Required';
      color = Colors.orange;
    } else if (locationState is LocationError) {
      icon = Icons.error;
      status = 'Error: ${locationState.message}';
      color = Colors.red;
    } else {
      icon = Icons.gps_not_fixed;
      status = 'Initializing...';
      color = Colors.grey;
    }

    return Row(
      children: [
        Icon(icon, color: color),
        const SizedBox(width: 8),
        Expanded(
          child: Text(status, style: TextStyle(color: color)),
        ),
      ],
    );
  }

  Widget _buildLocationInfo(dynamic currentLocation) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text('Latitude: ${currentLocation.latitude.toStringAsFixed(6)}'),
        Text('Longitude: ${currentLocation.longitude.toStringAsFixed(6)}'),
        Text('Accuracy: ${currentLocation.accuracy.toStringAsFixed(1)}m'),
        Text('Battery: ${currentLocation.batteryLevel}%'),
        Text('Updated: ${_formatTime(currentLocation.timestamp)}'),
      ],
    );
  }

  Widget _buildSecurityFeatures() {
    return Column(
      children: [
        _buildFeatureRow(
          Icons.location_on,
          'Continuous Location Tracking',
          'Always active',
          Colors.green,
        ),
        const SizedBox(height: 8),
        _buildFeatureRow(
          Icons.battery_full,
          'Battery Monitoring',
          'Real-time status',
          Colors.blue,
        ),
        const SizedBox(height: 8),
        _buildFeatureRow(
          Icons.security,
          'Anti-Uninstall Protection',
          'Verification required',
          Colors.orange,
        ),
        const SizedBox(height: 8),
        _buildFeatureRow(
          Icons.camera_alt,
          'Remote Camera Access',
          'On-demand capture',
          Colors.purple,
        ),
        const SizedBox(height: 8),
        _buildFeatureRow(
          Icons.mic,
          'Remote Audio Recording',
          'Voice monitoring',
          Colors.teal,
        ),
      ],
    );
  }

  Widget _buildFeatureRow(
    IconData icon,
    String title,
    String subtitle,
    Color color,
  ) {
    return Row(
      children: [
        Icon(icon, color: color, size: 20),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(title, style: const TextStyle(fontWeight: FontWeight.w500)),
              Text(
                subtitle,
                style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
              ),
            ],
          ),
        ),
        Icon(Icons.check_circle, color: color, size: 16),
      ],
    );
  }

  String _formatTime(DateTime dateTime) {
    return '${dateTime.hour.toString().padLeft(2, '0')}:'
        '${dateTime.minute.toString().padLeft(2, '0')}:'
        '${dateTime.second.toString().padLeft(2, '0')}';
  }
}
