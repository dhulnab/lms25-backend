<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SellCopiesResource\Pages;
use App\Filament\Resources\SellCopiesResource\RelationManagers;
use App\Models\Book;
use App\Models\Book_for_sell_copy;
use Filament\Forms;
use Filament\Forms\Components;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SellCopiesResource extends Resource
{
    protected static ?string $model = Book_for_sell_copy::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $modelLabel = 'Sell Copies';
    protected static ?string $navigationLabel = 'Sell Copies';
    protected static ?int $navigationSort = 12;
    protected static ?string $navigationGroup = 'Resources';

    // protected static ?string $slug = '';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('book_id')
                    ->label('Book')
                    ->options(Book::all()->pluck('title', 'id'))
                    ->required(),
                TextInput::make('serial_number')
                    ->label('Serial Number')
                    ->numeric()
                    ->required(),
                Select::make('condition')
                    ->label('Condition')
                    ->options([
                        'new' => 'New',
                        'used_good' => 'Used - Good',
                        'used_fair' => 'Used - Fair',
                        'damaged' => 'Damaged',
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('book.title')
                    ->label('Book')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('serial_number')
                    ->label('Serial Number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('condition')
                    ->label('Condition')
                    ->badge()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('purchase_date')
                    ->label('Purchase Date')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Purchased By')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListSellCopies::route('/'),
            'create' => Pages\CreateSellCopies::route('/create'),
            'view' => Pages\ViewSellCopies::route('/{record}'),
            'edit' => Pages\EditSellCopies::route('/{record}/edit'),
        ];
    }
}
