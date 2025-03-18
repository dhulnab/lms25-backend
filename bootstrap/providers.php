<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    Ichtrojan\Otp\OtpServiceProvider::class,
    Illuminate\Filesystem\FilesystemServiceProvider::class,
    NotificationChannels\Fcm\FcmServiceProvider::class,
];
