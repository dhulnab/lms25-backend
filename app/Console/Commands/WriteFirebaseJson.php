<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class WriteFirebaseJson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'write:firebase-json';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create and write to the firebase.json file with sensitive information.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $firebaseData = [
            "type" => "service_account",
            "project_id" => config('services.firebase.project_id'),
            "private_key_id" => config('services.firebase.private_key_id'),
            "private_key" => config('services.firebase.private_key'),
            "client_email" => config('services.firebase.client_email'),
            "client_id" => config('services.firebase.client_id'),
            "auth_uri" => "https://accounts.google.com/o/oauth2/auth",
            "token_uri" => "https://oauth2.googleapis.com/token",
            "auth_provider_x509_cert_url" => "https://www.googleapis.com/oauth2/v1/certs",
            "client_x509_cert_url" => config('services.firebase.client_cert_url'),
            "universe_domain" => "googleapis.com"
        ];

        // Convert to JSON
        $jsonContent = json_encode($firebaseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        // Path to save the JSON file in the Laravel root directory
        $filePath = base_path('firebase.json');

        // Write to the file
        if (file_put_contents($filePath, $jsonContent)) {
            $this->info('firebase.json file has been created in the root directory successfully.');
        } else {
            $this->error('Failed to write the firebase.json file.');
        }
    }
}
