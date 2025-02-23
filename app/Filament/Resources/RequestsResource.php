<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RequestsResource\Pages;
use App\Models\Book;
use App\Models\Book_borrowing;
use App\Models\Book_for_borrow_copy;
use App\Models\Borrow_request;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Faker\Provider\ar_EG\Text;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

use function Laravel\Prompts\alert;

class RequestsResource extends Resource
{
    public $unavailableDates = [];
    public $selectedBook = null;
    public $copyInfo = null;
    protected static ?string $model = Borrow_request::class;
    protected static ?string $modelLabel = 'Request for Borrowing';
    protected static ?string $navigationLabel = 'Requests for Borrowing';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'heroicon-o-envelope-open';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->label('User')
                    ->options(
                        User::all()->mapWithKeys(fn($user) => [$user->id => "{$user->name} ({$user->email})"])
                    )
                    ->required(),

                Select::make('book_id')
                    ->label('Book')
                    ->options(Book::all()->pluck('title', 'id'))
                    ->reactive()
                    ->required()
                    ->afterStateUpdated(fn(callable $set, $state, $livewire) => $livewire->handleBookSelection($state, $set)),

                Hidden::make('copy_id')
                    ->label('Copy ID')
                    ->required()
                    ->reactive(),

                DatePicker::make('borrow_start_date')
                    ->label('Borrow Start Date')
                    ->reactive()
                    ->required()
                    ->native(false)
                    ->minDate(now()->toDateString())
                    ->disabledDates(fn(callable $get, $livewire) => $livewire->getUnavailableDates())
                    ->afterStateUpdated(function (callable $set, callable $get) {
                        // Set request_date and request_expiry_date when borrow_start_date changes
                        $set('request_date', now()->toDateTimeString());
                        $set('request_expiry_date', Carbon::parse($get('borrow_start_date'))->subSecond()->toDateTimeString());
                    })
                    ->afterStateUpdated(function ($state, callable $set) {
                        // Reset borrow_end_date and update minimum borrow end date based on borrow_start_date
                        $set('borrow_end_date', null);
                        $set('min_borrow_end_date', $state ? Carbon::parse($state)->addDays(3)->toDateTimeString() : null);
                    }),

                DatePicker::make('borrow_end_date')
                    ->label('Borrow End Date')
                    ->reactive()
                    ->required()
                    ->native(false)
                    ->minDate(fn(callable $get) => $get('min_borrow_end_date'))
                    ->disabledDates(fn(callable $get, $livewire) => $livewire->getUnavailableDates()),



                Hidden::make('status')->required()
                    ->default('approved'),

                Hidden::make('request_date')->required()
                    ->default(now()->toDateTimeString()),

                Hidden::make('request_expiry_date')
                    ->reactive()
                    ->required()
                    ->default(fn(callable $get) => Carbon::parse($get('borrow_start_date'))->subSecond()->toDateTimeString())
                    ->afterStateUpdated(function (callable $set, callable $get) {
                        $req_expiry = $get('borrow_start_date');
                        $set('request_expiry_date', Carbon::parse($req_expiry)->subSecond()->toDateTimeString());
                        return $req_expiry;
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.email')
                    ->label('User')
                    ->searchable(),
                TextColumn::make('book_for_borrow_copy.book.title')
                    ->label('Book')
                    ->searchable(),
                TextColumn::make('book_for_borrow_copy.serial_number')
                    ->label('Copy Serial Number')
                    ->searchable(),
                TextColumn::make('borrow_start_date')
                    ->label('Borrow Start Date')
                    ->searchable()
                    ->date('Y-m-d')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('borrow_end_date')
                    ->label('Borrow End Date')
                    ->date('Y-m-d')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('request_date')
                    ->label('Request Date')
                    ->searchable(),
                TextColumn::make('request_expiry_date')
                    ->label('Request Expiry Date')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->searchable()
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('transferToBorrowing')
                    ->label('Transfer to Borrowing')
                    ->color('customPurple')
                    ->requiresConfirmation()
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->action(function (Borrow_request $record) {
                        $copy = Book_for_borrow_copy::find($record->copy_id);
                        if (!$copy || $copy->status->value === 'borrowed') {
                            return Notification::make()
                                ->title('Error')
                                ->body('This copy is already borrowed.')
                                ->danger()
                                ->send();
                        }
                        if ($record->request_expiry_date < now()->toDateTimeString()) {
                            return Notification::make()
                                ->title('Error')
                                ->body('This request has expired.')
                                ->danger()
                                ->send();
                        }
                        $requests = Borrow_request::where('copy_id', $record->copy_id)
                            ->where('status', 'approved')
                            ->where('request_expiry_date', '>=', now()->toDateTimeString())->get();
                        $req = Borrow_request::find($record->id);

                        foreach ($requests as $request) {
                            Log::info($request->getAttributes()['created_at'] > $req->getAttributes()['created_at'] ? "old" : "there is another request with higher priority for this copy.");
                            if ($request->created_at < $req->created_at) {
                                return Notification::make()
                                    ->title('Error')
                                    ->body('There is another request with higher priority for this copy.')
                                    ->danger()
                                    ->send();
                            }
                        }
                        $borrowingRecord = Book_borrowing::create([
                            'user_id' => $record->user_id,
                            'book_copy_id' => $record->copy_id,
                            'borrow_start' => $record->borrow_start_date,
                            'borrow_end' => $record->borrow_end_date
                        ]);
                        $record->delete();
                        $copy->update(['status' => 'borrowed']);
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
    public static function canEdit(Model $record): bool
    {
        return false;
    }
    /**
     * Get the unavailable dates for the selected copy.
     */
    public function getUnavailableDates()
    {
        $unavailableDates = $this->unavailableDates;

        if (!empty($unavailableDates)) {
            $from = Carbon::parse($unavailableDates['from']);
            $until = Carbon::parse($unavailableDates['until']);

            // Generate an array of disabled dates between 'from' and 'until'
            $disabledDates = [];
            while ($from->lte($until)) {
                $disabledDates[] = $from->toDateString();
                $from->addDay();
            }
            return $disabledDates;
        }
        dd($unavailableDates);

        return [];
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRequests::route('/'),
            'create' => Pages\CreateRequests::route('/create'),
            'view' => Pages\ViewRequests::route('/{record}'),
            'edit' => Pages\EditRequests::route('/{record}/edit'),
        ];
    }
}
