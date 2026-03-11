import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../blocs/device/device_bloc.dart';
import '../../services/device_service.dart';

class DeviceRegistrationScreen extends StatefulWidget {
  const DeviceRegistrationScreen({super.key});

  @override
  State<DeviceRegistrationScreen> createState() =>
      _DeviceRegistrationScreenState();
}

class _DeviceRegistrationScreenState extends State<DeviceRegistrationScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();

  String _selectedAvatarType = 'icon';
  String _selectedAvatarValue = 'person';
  List<AvatarIcon> _avatarIcons = [];
  bool _loadingIcons = true;

  @override
  void initState() {
    super.initState();
    _loadAvatarIcons();
  }

  @override
  void dispose() {
    _nameController.dispose();
    super.dispose();
  }

  Future<void> _loadAvatarIcons() async {
    try {
      final deviceService = DeviceService();
      final icons = await deviceService.getAvatarIcons();
      setState(() {
        _avatarIcons = icons;
        _loadingIcons = false;
        if (icons.isNotEmpty) {
          _selectedAvatarValue = icons.first.id;
        }
      });
    } catch (e) {
      setState(() {
        _loadingIcons = false;
      });
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Failed to load avatar icons: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  void _registerDevice() {
    if (_formKey.currentState!.validate()) {
      context.read<DeviceBloc>().add(
        DeviceRegistrationRequested(
          name: _nameController.text.trim(),
          avatarType: _selectedAvatarType,
          avatarValue: _selectedAvatarValue,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: SafeArea(
        child: BlocListener<DeviceBloc, DeviceState>(
          listener: (context, state) {
            if (state is DeviceError) {
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text(state.message),
                  backgroundColor: Colors.red,
                ),
              );
            }
          },
          child: Padding(
            padding: const EdgeInsets.all(24.0),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // Header
                const Icon(Icons.phone_android, size: 80, color: Colors.blue),
                const SizedBox(height: 16),
                const Text(
                  'Device Registration',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    fontSize: 28,
                    fontWeight: FontWeight.bold,
                    color: Colors.blue,
                  ),
                ),
                const SizedBox(height: 8),
                const Text(
                  'Set up your device for family tracking',
                  textAlign: TextAlign.center,
                  style: TextStyle(fontSize: 16, color: Colors.grey),
                ),
                const SizedBox(height: 48),

                // Registration Form
                Form(
                  key: _formKey,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Device Name Field
                      const Text(
                        'Device Name',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 8),
                      TextFormField(
                        controller: _nameController,
                        decoration: const InputDecoration(
                          hintText: 'e.g., Mother, Father, Child',
                          prefixIcon: Icon(Icons.person),
                          border: OutlineInputBorder(),
                        ),
                        validator: (value) {
                          if (value == null || value.trim().isEmpty) {
                            return 'Please enter a device name';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: 24),

                      // Avatar Selection
                      const Text(
                        'Choose Avatar',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 8),

                      if (_loadingIcons)
                        const Center(child: CircularProgressIndicator())
                      else if (_avatarIcons.isEmpty)
                        const Text(
                          'No avatar icons available',
                          style: TextStyle(color: Colors.grey),
                        )
                      else
                        Container(
                          height: 120,
                          padding: const EdgeInsets.all(8),
                          decoration: BoxDecoration(
                            border: Border.all(color: Colors.grey.shade300),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: GridView.builder(
                            gridDelegate:
                                const SliverGridDelegateWithFixedCrossAxisCount(
                                  crossAxisCount: 6,
                                  mainAxisSpacing: 8,
                                  crossAxisSpacing: 8,
                                ),
                            itemCount: _avatarIcons.length,
                            itemBuilder: (context, index) {
                              final icon = _avatarIcons[index];
                              final isSelected =
                                  _selectedAvatarValue == icon.id;

                              return GestureDetector(
                                onTap: () {
                                  setState(() {
                                    _selectedAvatarValue = icon.id;
                                  });
                                },
                                child: Container(
                                  decoration: BoxDecoration(
                                    color: isSelected
                                        ? Colors.blue.shade100
                                        : Colors.transparent,
                                    border: Border.all(
                                      color: isSelected
                                          ? Colors.blue
                                          : Colors.grey.shade300,
                                      width: 2,
                                    ),
                                    borderRadius: BorderRadius.circular(8),
                                  ),
                                  child: Center(
                                    child: Text(
                                      icon.emoji,
                                      style: const TextStyle(fontSize: 24),
                                    ),
                                  ),
                                ),
                              );
                            },
                          ),
                        ),

                      const SizedBox(height: 32),

                      // Register Button
                      BlocBuilder<DeviceBloc, DeviceState>(
                        builder: (context, state) {
                          return SizedBox(
                            width: double.infinity,
                            height: 50,
                            child: ElevatedButton(
                              onPressed: state is DeviceLoading
                                  ? null
                                  : _registerDevice,
                              style: ElevatedButton.styleFrom(
                                backgroundColor: Colors.blue,
                                foregroundColor: Colors.white,
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(8),
                                ),
                              ),
                              child: state is DeviceLoading
                                  ? const CircularProgressIndicator(
                                      color: Colors.white,
                                    )
                                  : const Text(
                                      'Register Device',
                                      style: TextStyle(
                                        fontSize: 16,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                            ),
                          );
                        },
                      ),
                    ],
                  ),
                ),

                const SizedBox(height: 32),

                // Info Notice
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: Colors.orange.shade50,
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: Colors.orange.shade200),
                  ),
                  child: const Column(
                    children: [
                      Icon(Icons.security, color: Colors.orange, size: 24),
                      SizedBox(height: 8),
                      Text(
                        'Security Notice',
                        style: TextStyle(
                          fontWeight: FontWeight.bold,
                          color: Colors.orange,
                        ),
                      ),
                      SizedBox(height: 4),
                      Text(
                        'This app will continuously track your location and device status for family safety. Location tracking will continue in the background.',
                        textAlign: TextAlign.center,
                        style: TextStyle(color: Colors.orange),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
