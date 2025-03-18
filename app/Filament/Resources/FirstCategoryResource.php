<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FirstCategoryResource\Pages;
use App\Filament\Resources\FirstCategoryResource\RelationManagers;
use App\Models\First_category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns;
use Filament\Tables\Table;
use Filament\Forms\Components;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FirstCategoryResource extends Resource
{
    protected static ?string $model = First_category::class;

    protected static ?string $modelLabel = 'First Categories';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?string $navigationGroup = 'Categories';
    protected static ?string $navigationLabel = 'First Categories';
    protected static ?int $navigationSort = 13;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\TextInput::make('name')
                    ->label('Name')
                    ->maxLength(255)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
                Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
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
            'index' => Pages\ListFirstCategories::route('/'),
            'create' => Pages\CreateFirstCategory::route('/create'),
            'view' => Pages\ViewFirstCategory::route('/{record}'),
            'edit' => Pages\EditFirstCategory::route('/{record}/edit'),
        ];
    }
}
