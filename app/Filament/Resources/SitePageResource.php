<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SitePageResource\Pages;
use App\Models\SitePage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SitePageResource extends Resource
{
    protected static ?string $model = SitePage::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Verein';

    protected static ?string $navigationLabel = 'Seiten';

    protected static ?string $modelLabel = 'Seite';

    protected static ?string $pluralModelLabel = 'Seiten';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('slug')
                ->label('Slug')
                ->required()
                ->unique(ignoreRecord: true)
                ->helperText('Interner Bezeichner, z. B. impressum, datenschutz, satzung, aufnahmeantrag.'),
            Forms\Components\TextInput::make('title')
                ->label('Titel')
                ->required(),
            Forms\Components\RichEditor::make('content')
                ->label('Inhalt')
                ->columnSpanFull(),
            Forms\Components\FileUpload::make('document_path')
                ->label('Dokument (PDF)')
                ->disk('public')
                ->directory('documents')
                ->acceptedFileTypes(['application/pdf'])
                ->helperText('Optionales Dokument zum Download, z. B. Satzung oder Aufnahmeantrag als PDF.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Titel')->searchable(),
                Tables\Columns\TextColumn::make('slug')->label('Slug')->searchable(),
                Tables\Columns\TextColumn::make('updated_at')->label('Zuletzt geändert')->dateTime('d.m.Y H:i')->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSitePages::route('/'),
            'create' => Pages\CreateSitePage::route('/create'),
            'edit' => Pages\EditSitePage::route('/{record}/edit'),
        ];
    }
}
