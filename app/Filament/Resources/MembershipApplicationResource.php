<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MembershipApplicationResource\Pages;
use App\Models\MembershipApplication;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MembershipApplicationResource extends Resource
{
    protected static ?string $model = MembershipApplication::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'Verein';

    protected static ?string $navigationLabel = 'Aufnahmeanträge';

    protected static ?string $modelLabel = 'Aufnahmeantrag';

    protected static ?string $pluralModelLabel = 'Aufnahmeanträge';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('first_name')->label('Vorname')->disabled(),
            Forms\Components\TextInput::make('last_name')->label('Nachname')->disabled(),
            Forms\Components\TextInput::make('street')->label('Straße, Hausnummer')->disabled(),
            Forms\Components\TextInput::make('postal_code')->label('PLZ')->disabled(),
            Forms\Components\TextInput::make('city')->label('Ort')->disabled(),
            Forms\Components\TextInput::make('email')->label('E-Mail')->disabled(),
            Forms\Components\TextInput::make('phone')->label('Telefon')->disabled(),
            Forms\Components\DatePicker::make('birth_date')->label('Geburtsdatum')->disabled(),
            Forms\Components\Textarea::make('message')->label('Anmerkungen')->disabled()->columnSpanFull(),
            Forms\Components\Toggle::make('handled')->label('Erledigt'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('last_name')->label('Nachname')->searchable(),
                Tables\Columns\TextColumn::make('first_name')->label('Vorname')->searchable(),
                Tables\Columns\TextColumn::make('city')->label('Ort')->searchable(),
                Tables\Columns\TextColumn::make('email')->label('E-Mail')->searchable(),
                Tables\Columns\IconColumn::make('handled')->label('Erledigt')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->label('Eingegangen am')->dateTime('d.m.Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('handled')->label('Erledigt'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMembershipApplications::route('/'),
            'edit' => Pages\EditMembershipApplication::route('/{record}/edit'),
        ];
    }
}
