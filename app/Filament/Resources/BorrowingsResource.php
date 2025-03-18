<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BorrowingsResource\Pages;
use App\Filament\Resources\BorrowingsResource\RelationManagers;
use App\Models\Book as Books;
use App\Models\Book_borrowing;
use App\Models\Book_for_borrow_copy;
use App\Models\Penalty;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\FcmNotification;
use App\Notifications\NotifyByAll;
use App\Services\FirebaseNotificationService;
use Faker\Provider\ar_EG\Text;
use Filament\Forms\Components;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BorrowingsResource extends Resource
{
    protected static ?string $model = Book_borrowing::class;
    protected static ?string $modelLabel = 'Book Borrowings';
    protected static ?string $navigationLabel = 'Book Borrowings';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'heroicon-o-swatch';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('book_copy_id')
                    ->label('Copy Serial Number')
                    ->options(Book_for_borrow_copy::where('status', 'available')->pluck('serial_number', 'id'))
                    ->required(),
                Select::make('user_id')
                    ->label('User')
                    ->options(User::all()->pluck('name', 'id'))
                    ->required(),
                Components\DateTimePicker::make('borrow_start')
                    ->label('Borrow Start')
                    ->required(),
                Components\DateTimePicker::make('borrow_end')
                    ->label('Borrow End')
                    ->required(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('book_for_borrow_copy.book.title')
                    ->label('Book')
                    ->searchable(),
                TextColumn::make('book_for_borrow_copy.serial_number')
                    ->label('Copy Serial Number')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(),
                TextColumn::make('borrow_start')
                    ->label('Borrow Start')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('borrow_end')
                    ->label('Borrow End')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('return_date')
                    ->label('Return Date')
                    ->searchable(),
                TextColumn::make('returned')
                    ->label('Returned')
                    ->formatStateUsing(function ($state) {
                        return $state === true ? 'Returned' : 'Not Returned';
                    })
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('return')
                    ->label('Returned?')
                    ->color('customPurple')
                    ->icon('heroicon-s-check-circle')
                    ->requiresConfirmation()
                    ->visible(fn(Book_borrowing $record) => !$record->returned)
                    ->action(function (Book_borrowing $record) {
                        $copy = Book_for_borrow_copy::find($record->book_copy_id);
                        if (!$copy) {
                            return Notification::make()
                                ->title('Error')
                                ->body('This copy is already borrowed.')
                                ->danger()
                                ->send();
                        }
                        $returnRecord = $copy->book_borrowings->where('returned', false)->first();
                        if (!$returnRecord) {
                            return Notification::make()
                                ->title('Not Found')
                                ->body('This copy is not currently borrowed.')
                                ->danger()
                                ->send();
                        }
                        $penaltyPreDay = Setting::latest()->value('penalty_per_day');
                        DB::beginTransaction();
                        try {
                            if ($returnRecord->borrow_end < now()) {
                                $days = (int) now()->diffInDays($returnRecord->borrow_end, false);
                                $penaltyAmount = $penaltyPreDay * $days;
                                $penaltyAmount = abs($penaltyAmount);
                                // dd($days, $penaltyAmount);
                                try {
                                    $penalty = Penalty::create([
                                        'user_id' => $returnRecord->user_id,
                                        'borrow_id' => $returnRecord->id,
                                        'penalty_amount' => $penaltyAmount,
                                        'penalty_status' => 'unpaid',
                                        'assessed_at' => now()->toDateTimeString(),
                                    ]);
                                } catch (\Exception $e) {

                                    dd($e->getMessage());
                                }
                            }
                            $returnRecord->update(['returned' => true]);
                            $returnRecord->update(['return_date' => now()]);
                            $requests = $copy->borrow_requests->where('status', 'approved');
                            $status = $requests->count() > 0 ? 'requested' : 'available';
                            $notifiableUser = User::find($returnRecord->user_id);
                            if ($notifiableUser) {
                                $notifiableUser->notify(new NotifyByAll('Book Returned', 'The book you borrowed \'' . $copy->book->title . '\' has been returned.', $copy->book));
                                (new FirebaseNotificationService())->sendNotification($notifiableUser->fcm_token, 'Book Returned', 'The book you borrowed \'' . $copy->book->title . '\' has been returned.');
                            } else {
                                Log::warning('User has no Web Push subscriptions:', [$notifiableUser->id]);
                            }
                            $copy->update(['status' => $status]);
                            if ($status === 'requested') {
                                $notifiableUser = User::find($requests->first()->user_id);
                                if ($notifiableUser) {
                                    $notifiableUser->notify(new NotifyByAll('Book Available', 'The book you requested \'' . $copy->book->title . '\' is now available.', $copy->book));
                                } else {
                                    Log::warning('User has no Web Push subscriptions:', [$notifiableUser->id]);
                                }
                            }


                            DB::commit();
                        } catch (\Exception $e) {
                            DB::rollBack();
                            Log::error('Error returning book copy: ' . $e->getMessage());
                            return Notification::make()
                                ->title('Error')
                                ->body('An error occurred while returning the book copy.')
                                ->danger()
                                ->send();
                        }

                        return Notification::make()
                            ->title('Success')
                            ->body('Book copy returned successfully.')
                            ->success()
                            ->send();
                    })
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBorrowings::route('/'),
            'create' => Pages\CreateBorrowings::route('/create'),
            'view' => Pages\ViewBorrowings::route('/{record}'),
            'edit' => Pages\EditBorrowings::route('/{record}/edit'),
        ];
    }
}
