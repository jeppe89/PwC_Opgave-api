<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Populate permissions
        $permissions = [
            'event-create',
            'event-edit',
            'event-delete',
            'event-subscribe',
            'event-subscribe-multiple',
            'event-get-subscribers',

            'user-list',
            'user-detail',
            'user-create',
            'user-edit',
            'user-delete',
            'user-get-subscribed'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }


        // Populate roles
        $adminRole = Role::create(['name' => 'Admin']);
        $adminRole->givePermissionTo($permissions);

        $customerRole = Role::create(['name' => 'Customer']);
        $customerRole->givePermissionTo([
            'user-detail', 'user-edit', 'event-subscribe'
        ]);

        // Create Admin user
        $adminUser = App\User::create([
           'name' => 'Admin',
           'email' => 'admin',
           'password' => Hash::make('admin@pwc@'),
            'phone' => '12 34 56 78'
        ]);
        $adminUser->assignRole('Admin');

        // Create Customer user
        $customerUser = App\User::create([
            'name' => 'Jeppe Thougaard Nielsen',
            'email' => 'j@j.dk',
            'password' => Hash::make('123'),
            'phone' => '12 34 56 78'
        ]);
        $customerUser->assignRole('Customer');


        // Populate events
        App\Event::create([
            'name' => 'Årsfest',
            'description' => 'Lorem ipsum',
            'date' => '2018-08-10 10:00:00'
        ]);

        App\Event::create([
            'name' => 'Intro kursus til Ruby on Rails',
            'description' => 'Lorem ipsum',
            'date' => '2018-08-20 09:00:00'
        ]);

        App\Event::create([
            'name' => 'Learning on databases',
            'description' => 'Lorem ipsum',
            'date' => '2018-08-05 10:00:00'
        ]);
    }
}
