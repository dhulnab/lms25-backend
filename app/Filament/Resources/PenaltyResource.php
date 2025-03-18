<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PenaltyResource\Pages;
use App\Filament\Resources\PenaltyResource\RelationManagers;
use App\Models\Book_borrowing;
use App\Models\Penalty;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PenaltyResource extends Resource
{
    protected static ?string $model = Penalty::class;
    protected static ?string $slug = 'Penalty';
    protected static ?string $modelLabel = 'Penalty';
    protected static ?string $navigationLabel = 'Penalties';
    protected static ?string $navigationGroup = 'Finance';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';


    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->sortable(),
                Tables\Columns\TextColumn::make('borrow_id')
                    ->label('Borrow Records')
                    ->sortable()
                    ->formatStateUsing(fn($state) => 'Borrow Record'),
                Tables\Columns\TextColumn::make('penalty_amount')
                    ->label('Penalty Amount')
                    ->numeric()
                    ->money('USD', locale: 'en_US')
                    ->sortable(),
                Tables\Columns\TextColumn::make('penalty_status')
                    ->label('Status')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('assessed_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid_at')
                    ->dateTime()
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
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }



    public static function canDelete(Model $record): bool
    {
        return false;
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPenalties::route('/'),
            'create' => Pages\CreatePenalty::route('/create'),
            'view' => Pages\ViewPenalty::route('/{record}'),
            'edit' => Pages\EditPenalty::route('/{record}/edit'),
        ];
    }
}
