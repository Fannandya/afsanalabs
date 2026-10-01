<?php

namespace App\Services;

use App\Mail\AdminNotificationMail;
use App\Models\BusinessSetting;
use App\Models\Notification;
use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyService
{
    /** @var array<string,string> */
    public const TYPE_TO_FLAG = [
        'pesanan_baru' => 'notify_new_order',
        'permintaan_mockup' => 'notify_mockup_request',
        'pesan_kontak' => 'notify_contact_message',
        'konsultasi_baru' => 'notify_consultation',
    ];

    public static function send(string $type, string $message, ?int $relatedId = null): void
    {
        $admin = User::query()->orderBy('id')->first();
        if (! $admin) {
            return;
        }

        Notification::create([
            'user_id' => $admin->id,
            'type' => $type,
            'message' => $message,
            'related_id' => $relatedId,
            'is_read' => false,
        ]);

        $flag = self::TYPE_TO_FLAG[$type] ?? null;
        if ($flag) {
            $pref = NotificationPreference::where('user_id', $admin->id)->first();
            $enabled = $pref ? (bool) $pref->{$flag} : true;
            if (! $enabled) {
                return;
            }
        }

        try {
            if (! self::mailConfigured()) {
                Log::info('Mail tidak dikonfigurasi; notifikasi hanya in-app.', ['type' => $type]);

                return;
            }
            $business = BusinessSetting::find(1);
            $to = $business?->contact_email ?? $admin->email;
            Mail::to($to)->send(new AdminNotificationMail($type, $message));
        } catch (\Throwable $e) {
            Log::error('Gagal kirim email notifikasi.', ['type' => $type, 'error' => $e->getMessage()]);
        }
    }

    public static function mailConfigured(): bool
    {
        $mailer = (string) config('mail.default', 'log');
        if (in_array($mailer, ['log', 'array'], true)) {
            return (bool) config('mail.mailers.smtp.host');
        }

        return true;
    }
}
