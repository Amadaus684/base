<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    
    public function run(): void
    {
        // Reset cached permission
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // post
            'post.view',
            'post.create',
            'post.update',
            'post.delete',
            'post.publish',      // publish & unpublish
            'post.featured',     // jadikan artikel unggulan

            // category

            'category.view',
            'category.create',
            'category.update',
            'category.delete',

            // tag

            'tag.view',
            'tag.create',
            'tag.update',
            'tag.delete',

            // page

            'page.view',
            'page.create',
            'page.update',
            'page.delete',
            'page.publish',

            // profil

            'profil.view',
            'profil.update',

            // user
            'user.view',
            'user.create',
            'user.update',
            'user.delete',

            // role
            'role.view',
            'role.create',
            'role.update',
            'role.delete',

            // permission
            'permission.view',
            'permission.create',
            'permission.update',
            'permission.delete',

            // seeting
            'seeting.view',
            'seeting.update',

            // Activity Logs
            'activity_log.view',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate(
                $permission,
                'web'
            );
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Owner
        $owner = Role::findOrCreate('Owner', 'web');
        $owner->syncPermissions(Permission::all());

        // Admin
        $admin = Role::findOrCreate('Admin', 'web');

        // Author
        $author = Role::findOrCreate('Author', 'web');

        // Member
        $member = Role::findOrCreate('Member', 'web');

        /*
         * Owner
         *
         * We will handle full access through Gate::before()
         * rather than assigning every permission individually.
         */

        // Admin permission
        $admin->syncPermissions([
            
            'user.view',
            'user.create',
            'user.update',

            'activity_log.view',
        ]);

        // Author permission
        $author->syncPermissions([
            
        ]);

        // Viewer permission
        $member->syncPermissions([
            
        ]);

        // Clear cache again after modifications
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
