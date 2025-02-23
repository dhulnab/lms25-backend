<?php

namespace App\Filament\Resources\PenaltyResource\Pages;

use App\Filament\Resources\BorrowingsResource;
use App\Filament\Resources\PenaltyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Filament\Tables;

class ListPenalties extends ListRecords
{
    protected static string $resource = PenaltyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->sortable(),
                Tables\Columns\TextColumn::make('borrow_id')
                    ->label('Borrow Records')
                    ->sortable()
                    ->color('primary')
                    ->default('Borrow Record')
                    ->formatStateUsing(fn($state) => 'Borrow Record')
                    ->openUrlInNewTab()
                    ->url(fn($record) => BorrowingsResource::getUrl('view', ['record' => $record->borrow_id])),
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
            ->actions([
                Tables\Actions\EditAction::make(),
                // Exclude the ViewAction to remove the "View" button
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
