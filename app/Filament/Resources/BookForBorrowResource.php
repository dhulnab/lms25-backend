<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookForBorrowResource\Pages;
use Illuminate\Support\Str;
use App\Models\Book;
use App\Models\Book_for_borrow_copy;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BookForBorrowResource extends Resource
{
    protected static ?string $model = Book_for_borrow_copy::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $modelLabel = 'Borrow Copies';
    protected static ?string $navigationLabel = 'Borrow Copies';
    protected static ?string $navigationGroup = 'Resources';
    protected static ?int $navigationSort = 11;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('book_id')
                    ->options(Book::all()->pluck('title', 'id'))
                    ->label('Book')
                    ->required(),
                Forms\Components\TextInput::make('serial_number')
                    ->label('Serial Number')
                    ->maxLength(255)
                    ->numeric()
                    ->required(),

                Forms\Components\Select::make('condition')
                    ->label('Condition')
                    ->options([
                        'new' => 'New',
                        'used_good' => 'Used - Good',
                        'used_fair' => 'Used - Fair',
                        'damaged' => 'Damaged',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('book.title')
                    ->label('Book')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('serial_number')
                    ->label('Serial Number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('condition')
                    ->label('Condition')
                    ->badge()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->searchable()
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->searchable()
                    ->sortable(),
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
            'index' => Pages\ListBookForBorrows::route('/'),
            'create' => Pages\CreateBookForBorrow::route('/create'),
            'view' => Pages\ViewBookForBorrow::route('/{record}'),
            'edit' => Pages\EditBookForBorrow::route('/{record}/edit'),
        ];
    }
}
