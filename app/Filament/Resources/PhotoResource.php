<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PhotoResource\Pages;
use App\Models\Category;
use App\Models\Photo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PhotoResource extends Resource
{
    protected static ?string $model = Photo::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Fotoarchiv';

    protected static ?string $navigationLabel = 'Fotos';

    protected static ?string $modelLabel = 'Foto';

    protected static ?string $pluralModelLabel = 'Fotos';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Titel')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state)))
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->columnSpanFull(),
                    Forms\Components\FileUpload::make('image_path')
                        ->label('Bild')
                        ->image()
                        ->disk('public')
                        ->directory('photos')
                        ->required()
                        // Livewire caps uploads at 12 MB by default regardless
                        // of php.ini - match public/.user.ini's upload_max_filesize.
                        ->maxSize(25 * 1024)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('description')
                        ->label('Beschreibung')
                        ->columnSpanFull(),
                    Forms\Components\Select::make('category_id')
                        ->label('Kategorie')
                        ->options(fn () => Category::orderBy('order')->pluck('name', 'id'))
                        ->required()
                        ->searchable(),
                    Forms\Components\Select::make('location_id')
                        ->label('Ort')
                        ->relationship('location', 'name')
                        ->searchable()
                        ->preload(),
                ]),
            Forms\Components\Section::make('Zeitliche Einordnung')
                ->columns(3)
                ->schema([
                    Forms\Components\DatePicker::make('date_from')->label('Datum von'),
                    Forms\Components\DatePicker::make('date_to')->label('Datum bis'),
                    Forms\Components\TextInput::make('date_text')
                        ->label('Datierung (Text)')
                        ->helperText("z. B. 'um 1930' oder '1950er Jahre'."),
                ]),
            Forms\Components\Section::make('Herkunft & Status')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('source')->label('Quelle / Bildrechte'),
                    Forms\Components\TextInput::make('inventory_number')->label('Inventarnummer'),
                    Forms\Components\Toggle::make('is_published')
                        ->label('Veröffentlicht')
                        ->default(true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail_path')
                    ->label('')
                    ->disk('public')
                    ->getStateUsing(fn (Photo $record) => $record->thumbnail_path ?: $record->image_path),
                Tables\Columns\TextColumn::make('title')->label('Titel')->searchable(),
                Tables\Columns\TextColumn::make('category.name')->label('Kategorie')->badge(),
                Tables\Columns\TextColumn::make('location.name')->label('Ort'),
                Tables\Columns\TextColumn::make('date_text')->label('Datierung'),
                Tables\Columns\IconColumn::make('is_published')->label('Veröffentlicht')->boolean(),
                Tables\Columns\TextColumn::make('person_tags_count')->label('Personen')->counts('personTags'),
                Tables\Columns\TextColumn::make('created_at')->label('Hochgeladen am')->dateTime('d.m.Y')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(50)
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Kategorie')
                    ->relationship('category', 'name'),
                Tables\Filters\SelectFilter::make('location_id')
                    ->label('Ort')
                    ->relationship('location', 'name'),
                Tables\Filters\TernaryFilter::make('is_published')->label('Veröffentlicht'),
            ])
            ->actions([
                Tables\Actions\Action::make('tagPersons')
                    ->label('Personen markieren')
                    ->icon('heroicon-o-user-plus')
                    ->url(fn (Photo $record): string => static::getUrl('tags', ['record' => $record])),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPhotos::route('/'),
            'create' => Pages\CreatePhoto::route('/create'),
            'bulk-upload' => Pages\BulkUploadPhotos::route('/bulk-upload'),
            'edit' => Pages\EditPhoto::route('/{record}/edit'),
            'tags' => Pages\ManageTags::route('/{record}/tags'),
        ];
    }
}
