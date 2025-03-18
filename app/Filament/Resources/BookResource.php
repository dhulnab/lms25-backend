<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookResource\Pages;
use App\Models\Book;
use App\Models\First_category;
use App\Models\Second_category;
use App\Models\Third_category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class BookResource extends Resource
{
    protected static ?string $model = Book::class;

    protected static ?string $modelLabel = 'Books';
    protected static ?string $navigationLabel = 'Books';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';


    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('author')
                ->required()
                ->maxLength(255),
            Forms\Components\Textarea::make('description')
                ->required()
                ->columnSpanFull(),

            // Cover image upload
            Forms\Components\FileUpload::make('cover')
                ->label('Upload Cover Image')
                ->disk('s3')
                ->directory('covers')
                ->visibility('public')
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'application/pdf'])
                ->maxFiles(1)
                ->imageEditor()
                ->imageEditorAspectRatios(['4:3', '16:9'])
                ->maxSize(10240)
                ->required(),

            /*
             * Dependent Category Selects:
             * - First Category: Loads all first categories.
             * - Second Category: Filtered by the selected first category.
             * - Third Category: Filtered by the selected second category.
             */
            Forms\Components\Select::make('first_category_id')
                ->label('First Category')
                ->options(First_category::query()->pluck('name', 'id'))
                ->reactive()
                ->required(),

            Forms\Components\Select::make('second_category_id')
                ->label('Second Category')
                ->options(function (callable $get) {
                    $firstCategory = $get('first_category_id');
                    if ($firstCategory) {
                        return Second_category::query()
                            ->where('parent_id', $firstCategory)
                            ->pluck('name', 'id');
                    }
                    return [];
                })
                ->reactive()
                ->afterStateUpdated(fn(callable $set) => $set('third_category_id', null)),

            Forms\Components\Select::make('third_category_id')
                ->label('Third Category')
                ->options(function (callable $get) {
                    $secondCategory = $get('second_category_id');
                    if ($secondCategory) {
                        return Third_category::query()
                            ->where('parent_id', $secondCategory)
                            ->pluck('name', 'id');
                    }
                    return [];
                }),

            Forms\Components\TextInput::make('publisher')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('published_year')
                ->required()
                ->numeric()
                ->maxLength(255),
            Forms\Components\TextInput::make('isbn')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('language')
                ->required()
                ->maxLength(255),

            // Availability toggles with default values and reactive state
            Forms\Components\Toggle::make('electronic_available')
                ->label('Electronic Available')
                ->default(false)
                ->reactive(),
            Forms\Components\Toggle::make('hard_copy_available')
                ->label('Hard Copy Available')
                ->default(false)
                ->reactive(),

            // Hard copy price: only visible and required if hard copy is available
            Forms\Components\TextInput::make('hard_copy_price')
                ->label('Hard Copy Price')
                ->numeric()
                ->required(fn(callable $get): bool => (bool) $get('hard_copy_available'))
                ->hidden(fn(callable $get): bool => ! (bool) $get('hard_copy_available')),

            // Electronic copy price: only visible and required if electronic is available
            Forms\Components\TextInput::make('electronic_copy_price')
                ->label('Electronic Copy Price')
                ->numeric()
                ->required(fn(callable $get): bool => (bool) $get('electronic_available'))
                ->hidden(fn(callable $get): bool => ! (bool) $get('electronic_available')),

            // Electronic book upload (stored privately): only visible if electronic is available
            Forms\Components\FileUpload::make('link')
                ->label('Upload The Electronic Book')
                ->disk('s3')
                ->directory('books')
                ->visibility('private')
                ->acceptedFileTypes(['application/pdf', 'application/epub'])
                ->maxSize(512000)
                ->required(fn(callable $get): bool => (bool) $get('electronic_available'))
                ->hidden(fn(callable $get): bool => ! (bool) $get('electronic_available')),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('author')
                ->searchable(),
            Tables\Columns\TextColumn::make('title')
                ->searchable(),
            Tables\Columns\TextColumn::make('description')
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),

            // Image column for the cover; using a callback to prepend the directory path
            Tables\Columns\ImageColumn::make('cover')
                ->disk('s3')
                ->visibility('public')
                ->width(50) // Set the desired width
                ->height(75) // Set the desired height
                ->url(fn($record) => Storage::disk('s3')->url($record->cover))
                ->label('Cover Image'),

            Tables\Columns\TextColumn::make('first_category.name')
                ->label('First Category')
                ->sortable(),
            Tables\Columns\TextColumn::make('second_category.name')
                ->label('Second Category')
                ->sortable(),
            Tables\Columns\TextColumn::make('third_category.name')
                ->label('Third Category')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            Tables\Columns\TextColumn::make('publisher')
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('published_year')
                ->sortable()
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('isbn')
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('language')
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),

            Tables\Columns\IconColumn::make('electronic_available')
                ->boolean(),
            Tables\Columns\TextColumn::make('hard_copy_price')
                ->sortable(),
            Tables\Columns\TextColumn::make('electronic_copy_price')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            // Optionally display a download link if the book is electronically available.
            Tables\Columns\TextColumn::make('link')
                ->label('Electronic Book')
                ->url(fn($record) => $record->electronic_available ? route(['filename' => $record->link]) : null)
                ->visible(function ($record) {
                    return (bool) $record?->electronic_available;
                })
                ->toggleable(isToggledHiddenByDefault: true),

            Tables\Columns\TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('updated_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ])->filters([])->actions([
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
        ])->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBooks::route('/'),
            'create' => Pages\CreateBook::route('/create'),
            'view'   => Pages\ViewBook::route('/{record}'),
            'edit'   => Pages\EditBook::route('/{record}/edit'),
        ];
    }
}
