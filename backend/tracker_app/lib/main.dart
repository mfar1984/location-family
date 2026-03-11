import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:get_it/get_it.dart';
import 'screens/auth/login_screen.dart';
import 'screens/main/main_screen.dart';
import 'services/auth_service.dart';
import 'services/location_service.dart';
import 'services/device_service.dart';
import 'services/security_service.dart';
import 'blocs/auth/auth_bloc.dart';
import 'blocs/location/location_bloc.dart';
import 'blocs/device/device_bloc.dart';

final GetIt getIt = GetIt.instance;

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  
  // Initialize services
  await _setupServices();
  
  // Initialize security features
  await getIt<SecurityService>().initializeSecurity();
  
  runApp(const TrackerApp());
}

Future<void> _setupServices() async {
  // Register services
  getIt.registerLazySingleton<AuthService>(() => AuthService());
  getIt.registerLazySingleton<LocationService>(() => LocationService());
  getIt.registerLazySingleton<DeviceService>(() => DeviceService());
  getIt.registerLazySingleton<SecurityService>(() => SecurityService());
  
  // Initialize services
  await getIt<AuthService>().initialize();
  await getIt<LocationService>().initialize();
  await getIt<DeviceService>().initialize();
}

class TrackerApp extends StatelessWidget {
  const TrackerApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiBlocProvider(
      providers: [
        BlocProvider<AuthBloc>(
          create: (context) => AuthBloc(getIt<AuthService>())..add(AuthCheckRequested()),
        ),
        BlocProvider<LocationBloc>(
          create: (context) => LocationBloc(getIt<LocationService>()),
        ),
        BlocProvider<DeviceBloc>(
          create: (context) => DeviceBloc(getIt<DeviceService>()),
        ),
      ],
      child: MaterialApp(
        title: 'Family Tracker',
        debugShowCheckedModeBanner: false,
        theme: ThemeData(
          primarySwatch: Colors.blue,
          visualDensity: VisualDensity.adaptivePlatformDensity,
        ),
        home: BlocBuilder<AuthBloc, AuthState>(
          builder: (context, state) {
            if (state is AuthLoading) {
              return const Scaffold(
                body: Center(
                  child: CircularProgressIndicator(),
                ),
              );
            } else if (state is AuthAuthenticated) {
              return const MainScreen();
            } else {
              return const LoginScreen();
            }
          },
        ),
      ),
    );
  }
}
