<?php

namespace App\Console\Commands;

use App\Notifications\SystemNotification;
use Illuminate\Console\Command;
use App\Models\User;

class GenerateTestNotifications extends Command
{
    protected $signature = 'notifications:test {user? : User ID}';
    protected $description = 'Generate sample notifications for testing';

    public function handle(): int
    {
        $userId = $this->argument('user');
        $users = $userId
            ? User::where('id', $userId)->get()
            : User::whereIn('id', function ($q) {
                $q->select('id')->from('model_has_roles')
                    ->whereIn('role_id', function ($q) {
                        $q->select('id')->from('roles')
                            ->whereIn('name', ['super_admin', 'admin', 'guru']);
                    });
            })->get();

        if ($users->isEmpty()) {
            $this->error('No users found.');
            return Command::FAILURE;
        }

        $notifications = [
            new SystemNotification(
                title: 'Selamat Datang',
                message: 'Sistem notifikasi SIAMA siap digunakan. Pantau aktivitas ujian di halaman Monitoring.',
                url: route('admin.ujian.sesi.index', false),
                icon: 'info',
            ),
            new SystemNotification(
                title: 'Sesi Ujian Akan Berakhir',
                message: 'Sesi Ujian Matematika Wajib akan berakhir dalam 15 menit. Pastikan semua peserta sudah menyelesaikan ujian.',
                url: route('admin.ujian.sesi.index', false),
                icon: 'alert',
            ),
            new SystemNotification(
                title: 'Peserta Selesai Ujian',
                message: '5 peserta telah menyelesaikan ujian Bahasa Indonesia. Klik untuk melihat hasil.',
                url: route('admin.ujian.laporan.index', false),
                icon: 'success',
            ),
        ];

        foreach ($users as $user) {
            foreach ($notifications as $notif) {
                $user->notify($notif);
            }
            $this->info("Sent 3 notifications to user: {$user->name}");
        }

        $this->info('Done!');
        return Command::SUCCESS;
    }
}
