import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../blocs/auth/auth_bloc.dart';
import '../../blocs/device/device_bloc.dart';
import '../../blocs/location/location_bloc.dart';
import '../device/device_registration_screen.dart';
import 'dashboard_screen.dart';

class MainScreen extends StatefulWidget {
  const MainScreen({super.key});

  @override
  State<MainScreen> createState() => _MainScreenState();
}

class _MainScreenState extends State<MainScreen> {
  @override
  void initState() {
    super.initState();
    // Check if device is registered
    context.read<DeviceBloc>().add(DeviceCheckRequested());
  }

  @override
  Widget build(BuildContext context) {
    return BlocListener<DeviceBloc, DeviceState>(
      listener: (context, deviceState) {
        if (deviceState is DeviceRegistered) {
          // Start location tracking when device is registered
          final authState = context.read<AuthBloc>().state;
          if (authState is AuthAuthenticated) {
            context.read<LocationBloc>().add(
              LocationTrackingStarted(
                deviceId: deviceState.device.deviceId,
                deviceName: deviceState.device.name,
                authToken: authState.token,
              ),
            );
          }
        }
      },
      child: BlocBuilder<DeviceBloc, DeviceState>(
        builder: (context, deviceState) {
          if (deviceState is DeviceLoading) {
            return const Scaffold(
              body: Center(child: CircularProgressIndicator()),
            );
          } else if (deviceState is DeviceNotRegistered) {
            return const DeviceRegistrationScreen();
          } else if (deviceState is DeviceRegistered) {
            return const DashboardScreen();
          } else if (deviceState is DeviceError) {
            return Scaffold(
              body: Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(
                      Icons.error_outline,
                      size: 64,
                      color: Colors.red,
                    ),
                    const SizedBox(height: 16),
                    Text(
                      'Error: ${deviceState.message}',
                      textAlign: TextAlign.center,
                      style: const TextStyle(fontSize: 16),
                    ),
                    const SizedBox(height: 16),
                    ElevatedButton(
                      onPressed: () {
                        context.read<DeviceBloc>().add(DeviceCheckRequested());
                      },
                      child: const Text('Retry'),
                    ),
                  ],
                ),
              ),
            );
          } else {
            // Initial state - check device registration
            return const Scaffold(
              body: Center(child: CircularProgressIndicator()),
            );
          }
        },
      ),
    );
  }
}
