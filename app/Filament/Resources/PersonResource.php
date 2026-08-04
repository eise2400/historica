<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersonResource\Pages;
use App\Models\Person;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PersonResource extends Resource
{
    protected static ?string $model = Person::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Fotoarchiv';

    protected static ?string $navigationLabel = 'Personen';

    protected static ?string $modelLabel = 'Person';

    protected static ?string $pluralModelLabel = 'Personen';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('first_name')->label('Vorname'),
            Forms\Components\TextInput::make('last_name')->label('Nachname')->required(),
            Forms\Components\TextInput::make('maiden_name')->label('Geburtsname'),
            Forms\Components\TextInput::make('birth_year')->label('Geburtsjahr')->numeric()->minValue(1700)->maxValue(2100),
            Forms\Components\TextInput::make('death_year')->label('Sterbejahr')->numeric()->minValue(1700)->maxValue(2100),
            Forms\Components\Textarea::make('notes')->label('Anmerkungen')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('last_name')->label('Nachname')->searchable(),
                Tables\Columns\TextColumn::make('first_name')->label('Vorname')->searchable(),
                Tables\Columns\TextColumn::make('maiden_name')->label('Geburtsname')->searchable(),
                Tables\Columns\TextColumn::make('birth_year')->label('Geburtsjahr')->sortable(),
                Tables\Columns\TextColumn::make('death_year')->label('Sterbejahr')->sortable(),
                Tables\Columns\TextColumn::make('photos_count')->label('Anzahl Fotos')->counts('photos'),
            ])
            ->defaultSort('last_name')
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(50)
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPeople::route('/'),
            'create' => Pages\CreatePerson::route('/create'),
            'edit' => Pages\EditPerson::route('/{record}/edit'),
        ];
    }
}
