import 'package:flutter_bloc/flutter_bloc.dart';
import '../../services/device_service.dart';
import '../../models/device.dart';

// Events
abstract class DeviceEvent {}

class DeviceCheckRequested extends DeviceEvent {}

class DeviceRegistrationRequested extends DeviceEvent {
  final String name;
  final String avatarType;
  final String avatarValue;

  DeviceRegistrationRequested({
    required this.name,
    required this.avatarType,
    required this.avatarValue,
  });
}

// States
abstract class DeviceState {}

class DeviceInitial extends DeviceState {}

class DeviceLoading extends DeviceState {}

class DeviceNotRegistered extends DeviceState {}

class DeviceRegistered extends DeviceState {
  final Device device;

  DeviceRegistered({required this.device});
}

class DeviceError extends DeviceState {
  final String message;

  DeviceError({required this.message});
}

// BLoC
class DeviceBloc extends Bloc<DeviceEvent, DeviceState> {
  final DeviceService _deviceService;

  DeviceBloc(this._deviceService) : super(DeviceInitial()) {
    on<DeviceCheckRequested>(_onDeviceCheckRequested);
    on<DeviceRegistrationRequested>(_onDeviceRegistrationRequested);
  }

  Future<void> _onDeviceCheckRequested(
    DeviceCheckRequested event,
    Emitter<DeviceState> emit,
  ) async {
    emit(DeviceLoading());

    try {
      final device = await _deviceService.getStoredDevice();
      if (device != null) {
        emit(DeviceRegistered(device: device));
      } else {
        emit(DeviceNotRegistered());
      }
    } catch (e) {
      emit(DeviceError(message: e.toString()));
    }
  }

  Future<void> _onDeviceRegistrationRequested(
    DeviceRegistrationRequested event,
    Emitter<DeviceState> emit,
  ) async {
    emit(DeviceLoading());

    try {
      final result = await _deviceService.registerDevice(
        name: event.name,
        avatarType: event.avatarType,
        avatarValue: event.avatarValue,
      );

      if (result.success && result.device != null) {
        emit(DeviceRegistered(device: result.device!));
      } else {
        emit(
          DeviceError(message: result.errorMessage ?? 'Registration failed'),
        );
      }
    } catch (e) {
      emit(DeviceError(message: e.toString()));
    }
  }
}
