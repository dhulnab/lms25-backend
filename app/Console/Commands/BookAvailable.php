<?php

namespace App\Console\Commands;

use App\Models\Book_for_borrow_copy;
use App\Models\User;
use App\Notifications\BookAvailable as NotificationsBookAvailable;
use Illuminate\Console\Command;

class BookAvailable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:book-available';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle($copy_id)
    {
        $copy = Book_for_borrow_copy::find($copy_id);
        $requests = $copy->borrow_requests;
        dd($requests);
        $user = User::find(1);
        $user->notify(new NotificationsBookAvailable('Your book is now available')); // Send notification
    }
}
