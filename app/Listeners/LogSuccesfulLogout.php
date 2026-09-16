<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Request;

class LogSuccessfulLogout
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Logout $event): void
    {
        // if (!$event->user) {
        //     return;
        // }

        if ($event->user->hasRole('Owner')) {
            return;
        }

        ActivityLog::create([
            'user_id' => $event->user->id,
            'action' => 'logout',
            'description' => 'User berhasil logout',
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
