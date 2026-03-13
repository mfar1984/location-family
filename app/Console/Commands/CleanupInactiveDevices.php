<?php

namespace App\Console\Commands;

use App\Models\Device;
use App\Models\LocationPing;
use Illuminate\Console\Command;

class CleanupInactiveDevices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'devices:cleanup-inactive 
                            {--days=7 : Number of days of inactivity before marking device as inactive}
                            {--delete : Permanently delete inactive devices instead of marking them inactive}
                            {--dry-run : Show what would be cleaned up without actually doing it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark devices as inactive if they haven\'t sent location pings for a specified number of days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $shouldDelete = $this->option('delete');
        $dryRun = $this->option('dry-run');
        
        $cutoffDate = now()->subDays($days);
        
        $this->info("Looking for devices inactive since: {$cutoffDate->toDateTimeString()}");
        $this->newLine();
        
        // Get all active devices
        $activeDevices = Device::where('is_active', true)->get();
        
        $inactiveDevices = [];
        
        foreach ($activeDevices as $device) {
            // Get the latest ping for this device
            $latestPing = LocationPing::where('device_id', $device->id)
                ->orderBy('received_at', 'desc')
                ->first();
            
            // If no pings at all, or last ping is older than cutoff date
            if (!$latestPing || $latestPing->received_at < $cutoffDate) {
                $lastSeen = $latestPing ? $latestPing->received_at->toDateTimeString() : 'Never';
                $inactiveDevices[] = [
                    'device' => $device,
                    'last_seen' => $lastSeen,
                ];
            }
        }
        
        if (empty($inactiveDevices)) {
            $this->info('✓ No inactive devices found.');
            return 0;
        }
        
        // Display inactive devices
        $count = count($inactiveDevices);
        $this->warn("Found {$count} inactive device(s):");
        $this->newLine();
        
        $tableData = [];
        foreach ($inactiveDevices as $item) {
            $device = $item['device'];
            $tableData[] = [
                $device->device_id,
                $device->name,
                $device->user->email ?? 'N/A',
                $item['last_seen'],
            ];
        }
        
        $this->table(
            ['Device ID', 'Name', 'User Email', 'Last Seen'],
            $tableData
        );
        
        if ($dryRun) {
            $this->info('DRY RUN - No changes made.');
            return 0;
        }
        
        // Confirm action
        if (!$this->confirm('Do you want to proceed?', true)) {
            $this->info('Operation cancelled.');
            return 0;
        }
        
        // Process inactive devices
        $processedCount = 0;
        
        foreach ($inactiveDevices as $item) {
            $device = $item['device'];
            
            if ($shouldDelete) {
                // Permanently delete device and its pings
                LocationPing::where('device_id', $device->id)->delete();
                $device->delete();
                $this->line("✓ Deleted device: {$device->name} ({$device->device_id})");
            } else {
                // Mark as inactive
                $device->is_active = false;
                $device->save();
                $this->line("✓ Marked inactive: {$device->name} ({$device->device_id})");
            }
            
            $processedCount++;
        }
        
        $this->newLine();
        $action = $shouldDelete ? 'deleted' : 'marked as inactive';
        $this->info("✓ Successfully {$action} {$processedCount} device(s).");
        
        return 0;
    }
}
