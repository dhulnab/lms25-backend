<?php

namespace App\Filament\Resources\BookResource\Pages;

use App\Filament\Resources\BookResource;
use Filament\Actions;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Infolists\Components;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Storage;

class ViewBook extends ViewRecord
{
    protected static string $resource = BookResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        $filePath = $this->record->link;
        $temporaryUrl = $filePath ? Storage::disk('s3')->temporaryUrl($filePath, now()->addMinutes(30)) : null;
        return $infolist
            ->schema([
                Components\ImageEntry::make('cover')
                    ->disk('s3')
                    ->visibility('public')
                    ->label('Cover')
                    ->width(200)
                    ->height(300),
                Components\TextEntry::make('title')
                    ->label('Title'),
                Components\TextEntry::make('author')
                    ->label('Author'),
                Components\TextEntry::make('description')
                    ->label('Description'),
                Components\TextEntry::make('first_category.name')
                    ->label('First Category'),
                Components\TextEntry::make('second_category.name')
                    ->label('Second Category'),
                Components\TextEntry::make('third_category.name')
                    ->label('Third Category'),
                Components\TextEntry::make('publisher')
                    ->label('Publisher'),
                Components\TextEntry::make('published_year')
                    ->label('Published Year'),
                Components\TextEntry::make('isbn')
                    ->label('ISBN'),
                Components\TextEntry::make('language')
                    ->label('Language'),
                Components\TextEntry::make('electronic_available')
                    ->label('Electronic Available')
                    ->formatStateUsing(function (?bool $state): string {
                        return $state ? 'Yes' : 'No';
                    }),
                Components\TextEntry::make('hard_copy_available')
                    ->label('Hard Copy Available')
                    ->formatStateUsing(function (?bool $state): string {
                        return $state ? 'Yes' : 'No';
                    }),
                Components\TextEntry::make('hard_copy_price')
                    ->label('Hard Copy Price'),
                Components\TextEntry::make('electronic_copy_price')
                    ->label('Electronic Copy Price'),
                Components\TextEntry::make('link')
                    ->label('Electronic Book')
                    ->url($temporaryUrl)
                    ->openUrlInNewTab()
                    ->formatStateUsing(fn() => 'Download Electronic Book'),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
