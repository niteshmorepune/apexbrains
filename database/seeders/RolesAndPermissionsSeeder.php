<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $adminPerms = [
            'franchise.create', 'franchise.read', 'franchise.update', 'franchise.delete',
            'franchise.approve', 'franchise.suspend',
            'level.create', 'level.read', 'level.update', 'level.delete',
            'question.create', 'question.read', 'question.update', 'question.delete',
            'question.approve', 'question.audio',
            'competition.create', 'competition.read', 'competition.update', 'competition.delete',
            'audit.view', 'settings.edit', 'user.manage',
            'class_practice.admin',
        ];

        $franchisePerms = [
            'student.create', 'student.read', 'student.update', 'student.delete',
            'student.import',
            'batch.create', 'batch.read', 'batch.update', 'batch.delete',
            'fee.read', 'fee.record', 'fee.reminder',
            'exam.schedule', 'exam.view',
            'certificate.generate', 'certificate.view',
            'notification.send',
            'class_practice.manage',
            'competition.register_student',
        ];

        $internalStudentPerms = [
            'profile.view', 'practice.access', 'exam.take', 'exam.view',
            'certificate.download', 'result.view',
            'competition.participate', 'competition.practice',
            'class_practice.view',
        ];

        $externalStudentPerms = [
            'profile.view', 'competition.practice', 'competition.participate',
        ];

        $allPerms = array_unique(array_merge($adminPerms, $franchisePerms, $internalStudentPerms, $externalStudentPerms));
        foreach ($allPerms as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions($adminPerms);

        $franchiseAdmin = Role::firstOrCreate(['name' => 'franchise_admin', 'guard_name' => 'web']);
        $franchiseAdmin->syncPermissions($franchisePerms);

        $student = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
        $student->syncPermissions(array_unique(array_merge($internalStudentPerms, $externalStudentPerms)));
    }
}
