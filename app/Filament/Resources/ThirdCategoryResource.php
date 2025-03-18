<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ThirdCategoryResource\Pages;
use App\Filament\Resources\ThirdCategoryResource\RelationManagers;
use App\Models\Second_category;
use App\Models\Third_category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns;
use Filament\Forms\Components;

class ThirdCategoryResource extends Resource
{
    protected static ?string $model = Third_category::class;

    protected static ?string $modelLabel = 'Third Categories';
    protected static ?string $navigationLabel = 'Third Categories';
    protected static ?int $navigationSort = 15;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?string $navigationGroup = 'Categories';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Select::make('parent_id')
                    ->label('Parent Category')
                    ->options(Second_category::all()->pluck('name', 'id'))
                    ->required(),

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
                Columns\TextColumn::make('second_category.name')
                    ->label('Parent Category')
                    ->formatStateUsing(fn($state) => $state ?? 'N/A')
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
            'index' => Pages\ListThirdCategories::route('/'),
            'create' => Pages\CreateThirdCategory::route('/create'),
            'view' => Pages\ViewThirdCategory::route('/{record}'),
            'edit' => Pages\EditThirdCategory::route('/{record}/edit'),
        ];
    }
}
