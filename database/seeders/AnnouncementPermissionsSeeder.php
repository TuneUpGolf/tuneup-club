<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Spatie\Permission\Models\Role;

class AnnouncementPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Create announcements permissions
        $announcementPermissions = [
            'manage-announcements',
            'create-announcements', 
            'edit-announcements',
            'delete-announcements'
        ];

        foreach ($announcementPermissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName]);
        }

        // Instructor gets all announcement permissions
        $instructorRole = Role::where('name', 'influencer')->first();
        if ($instructorRole) {
            $instructorRole->givePermissionTo($announcementPermissions);
        }

        // Student only gets 'manage-announcements' (view/read only)
        $studentRole = Role::where('name', 'Follower')->first();
        if ($studentRole) {
            $studentRole->givePermissionTo(['manage-announcements']);
        }

        // Clear permission cache
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
