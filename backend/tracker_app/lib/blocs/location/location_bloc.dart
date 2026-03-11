import 'package:flutter_bloc/flutter_bloc.dart';
import '../../services/location_service.dart';
import '../../models/location_data.dart';

// Events
abstract class LocationEvent {}

class LocationTrackingStarted extends LocationEvent {
  final String deviceId;
  final String deviceName;
  final String authToken;

  LocationTrackingStarted({
    required this.deviceId,
    required this.deviceName,
    required this.authToken,
  });
}

class LocationTrackingStopped extends LocationEvent {}

class LocationUpdateReceived extends LocationEvent {
  final LocationData locationData;

  LocationUpdateReceived({required this.locationData});
}

// States
abstract class LocationState {}

class LocationInitial extends LocationState {}

class LocationPermissionRequired extends LocationState {}

class LocationTracking extends LocationState {
  final LocationData? currentLocation;

  LocationTracking({this.currentLocation});
}

class LocationError extends LocationState {
  final String message;

  LocationError({required this.message});
}

// BLoC
class LocationBloc extends Bloc<LocationEvent, LocationState> {
  final LocationService _locationService;

  LocationBloc(this._locationService) : super(LocationInitial()) {
    on<LocationTrackingStarted>(_onLocationTrackingStarted);
    on<LocationTrackingStopped>(_onLocationTrackingStopped);
    on<LocationUpdateReceived>(_onLocationUpdateReceived);
  }

  Future<void> _onLocationTrackingStarted(
    LocationTrackingStarted event,
    Emitter<LocationState> emit,
  ) async {
    try {
      final hasPermission = await _locationService.requestPermissions();
      if (!hasPermission) {
        emit(LocationPermissionRequired());
        return;
      }

      await _locationService.startTracking(
        deviceId: event.deviceId,
        deviceName: event.deviceName,
        authToken: event.authToken,
      );

      emit(LocationTracking());

      // Listen to location updates
      _locationService.locationStream.listen((locationData) {
        add(LocationUpdateReceived(locationData: locationData));
      });
    } catch (e) {
      emit(LocationError(message: e.toString()));
    }
  }

  Future<void> _onLocationTrackingStopped(
    LocationTrackingStopped event,
    Emitter<LocationState> emit,
  ) async {
    try {
      await _locationService.stopTracking();
      emit(LocationInitial());
    } catch (e) {
      emit(LocationError(message: e.toString()));
    }
  }

  Future<void> _onLocationUpdateReceived(
    LocationUpdateReceived event,
    Emitter<LocationState> emit,
  ) async {
    emit(LocationTracking(currentLocation: event.locationData));
  }
}
