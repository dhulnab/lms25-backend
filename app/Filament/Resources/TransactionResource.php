<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $modelLabel = 'Transaction';
    protected static ?string $navigationLabel = 'Transactions';
    protected static ?string $navigationGroup = 'Finance';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('action')
                    ->label('Action')
                    ->options([
                        'borrow' => 'Borrow',
                        'purchase' => 'Purchase',
                        'penalty_payment' => 'Penalty Payment',
                        'balance_update' => 'Balance Update',
                    ])
                    ->required(),
                Forms\Components\Select::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->required(),
                Forms\Components\Select::make('book_id')
                    ->label('Book')
                    ->relationship('book', 'title')
                    ->required(),
                Forms\Components\Select::make('book_for_sell_copy_id')
                    ->label('Serial Number')
                    ->relationship('book_for_sell_copy', 'serial_number')
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->label('Amount')
                    ->numeric()
                    ->mask(RawJs::make('$money($input, ".", ",", 2)'))
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'done' => 'Done',
                        'fail' => 'Fail',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('penalty_id')
                    ->label('Penalty Record')
                    ->required(),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('action')
                    ->label('Action')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User Name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('User Email')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('book.title')
                    ->label('Book Title')
                    ->sortable(),
                Tables\Columns\TextColumn::make('penalty_id')
                    ->label('Penalty Records')
                    ->sortable()
                    ->color('primary')
                    ->formatStateUsing(fn($state) => $state ? 'Penalty Record' : 'No Penalty')
                    ->openUrlInNewTab()
                    ->url(fn($record) => $record->penalty_id ? PenaltyResource::getUrl('view', ['record' => $record->penalty_id]) : null),

                Tables\Columns\TextColumn::make('book_for_sell_copy.serial_number')
                    ->label('Serial Number')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->money('USD', locale: 'en_US')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->label('Status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([])
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
    public static function canCreate(): bool
    {
        return false;
    }



    // public static function canDelete(Model $record): bool
    // {
    //     return false;
    // }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'view' => Pages\ViewTransaction::route('/{record}'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
