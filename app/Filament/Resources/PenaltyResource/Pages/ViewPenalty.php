<?php

namespace App\Filament\Resources\PenaltyResource\Pages;

use App\Filament\Resources\BorrowingsResource;
use App\Filament\Resources\PenaltyResource;
use App\Models\User;
use Filament\Actions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\ViewRecord;


class ViewPenalty extends ViewRecord
{
    protected static string $resource = PenaltyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('view_borrowing')
                ->label('View Borrowing Record')
                ->icon('heroicon-o-link')
                ->color('primary')
                ->url(fn($record) => \App\Filament\Resources\BorrowingsResource::getUrl('view', ['record' => $record->borrow_id]))
                ->openUrlInNewTab(),
        ];
    }
    public function form(Form $form): Form
    {
        $penalty = $this->record;
        return $form
            ->schema([
                Select::make('user_id')
                    ->label('User Name')
                    ->options(User::where('id', $penalty->user_id)->pluck('name', 'id'))
                    ->disabled(),
                Select::make('user_id')
                    ->label('User Email')
                    ->options(User::where('id', $penalty->user_id)->pluck('email', 'id'))
                    ->disabled(),
                TextInput::make('penalty_amount')
                    ->label('Penalty Amount')
                    ->disabled(),
                Select::make('penalty_status')
                    ->label('Status')
                    ->options([
                        'unpaid' => 'Unpaid',
                        'paid' => 'Paid',
                        'waived' => 'Waived',
                    ])
                    ->disabled(),
                DateTimePicker::make('assessed_at')
                    ->label('Assessed At')
                    ->disabled(),
                DateTimePicker::make('paid_at')
                    ->label('Paid At')
                    ->disabled(),

            ]);
    }
}
