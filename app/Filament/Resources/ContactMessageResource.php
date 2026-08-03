<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactMessageResource\Pages;
use App\Models\ContactMessage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'Verein';

    protected static ?string $navigationLabel = 'Kontaktanfragen';

    protected static ?string $modelLabel = 'Kontaktanfrage';

    protected static ?string $pluralModelLabel = 'Kontaktanfragen';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Name')->disabled(),
            Forms\Components\TextInput::make('email')->label('E-Mail')->disabled(),
            Forms\Components\TextInput::make('subject')->label('Betreff')->disabled(),
            Forms\Components\Textarea::make('message')->label('Nachricht')->disabled()->columnSpanFull(),
            Forms\Components\Toggle::make('handled')->label('Erledigt'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Name')->searchable(),
                Tables\Columns\TextColumn::make('email')->label('E-Mail')->searchable(),
                Tables\Columns\TextColumn::make('subject')->label('Betreff')->searchable(),
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
            'index' => Pages\ListContactMessages::route('/'),
            'edit' => Pages\EditContactMessage::route('/{record}/edit'),
        ];
    }
}
