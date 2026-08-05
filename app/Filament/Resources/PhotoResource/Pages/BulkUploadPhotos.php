<?php

namespace App\Filament\Resources\PhotoResource\Pages;

use App\Filament\Resources\PhotoResource;
use App\Models\Category;
use App\Models\Location;
use App\Models\Photo;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;

class BulkUploadPhotos extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = PhotoResource::class;

    protected static string $view = 'filament.resources.photo-resource.pages.bulk-upload-photos';

    protected static ?string $title = 'Sammelupload';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'is_published' => false,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('category_id')
                    ->label('Kategorie für alle Fotos')
                    ->options(fn () => Category::orderBy('order')->pluck('name', 'id'))
                    ->required()
                    ->searchable(),
                Select::make('location_id')
                    ->label('Ort für alle Fotos (optional)')
                    ->options(fn () => Location::orderBy('name')->pluck('name', 'id'))
                    ->searchable(),
                TextInput::make('date_text')
                    ->label('Datierung für alle Fotos (optional)')
                    ->helperText("z. B. 'um 1965', falls der ganze Stapel aus derselben Zeit stammt."),
                Toggle::make('is_published')
                    ->label('Sofort veröffentlichen')
                    ->helperText('Falls deaktiviert: Fotos werden als Entwurf angelegt und können danach einzeln geprüft, beschriftet und veröffentlicht werden.')
                    ->default(false),
                FileUpload::make('images')
                    ->label('Fotos')
                    ->multiple()
                    ->image()
                    ->preserveFilenames()
                    ->disk('public')
                    ->directory(fn () => 'photos/'.now()->format('Y/m'))
                    ->maxFiles(300)
                    ->minFiles(1)
                    ->required()
                    // Livewire caps uploads at 12 MB by default regardless of
                    // php.ini - match public/.user.ini's upload_max_filesize.
                    ->maxSize(25 * 1024)
                    ->reorderable(false)
                    ->helperText('Bis zu 300 Dateien pro Durchgang. Der Dateiname (ohne Endung) wird als vorläufiger Titel verwendet und kann später pro Foto angepasst werden.'),
            ])
            ->statePath('data');
    }

    public function upload(): void
    {
        $data = $this->form->getState();

        set_time_limit(300);

        $count = 0;
        foreach ($data['images'] as $imagePath) {
            Photo::create([
                'title' => $this->titleFromPath($imagePath),
                'image_path' => $imagePath,
                'category_id' => $data['category_id'],
                'location_id' => $data['location_id'] ?? null,
                'date_text' => $data['date_text'] ?? null,
                'is_published' => $data['is_published'] ?? false,
                'uploaded_by' => auth()->id(),
            ]);
            $count++;
        }

        Notification::make()
            ->title("{$count} Fotos hochgeladen")
            ->body($data['is_published']
                ? 'Die Fotos sind bereits veröffentlicht.'
                : 'Die Fotos wurden als Entwurf angelegt. Bitte einzeln prüfen, beschriften und veröffentlichen.')
            ->success()
            ->send();

        $this->form->fill(['is_published' => false]);

        $this->redirect(PhotoResource::getUrl('index', [
            'tableFilters' => ['is_published' => ['value' => $data['is_published'] ? '1' : '0']],
        ]));
    }

    private function titleFromPath(string $path): string
    {
        $name = pathinfo($path, PATHINFO_FILENAME);
        $name = str_replace(['-', '_'], ' ', $name);
        $name = trim(preg_replace('/\s+/', ' ', $name));

        return $name !== '' ? ucfirst($name) : 'Foto';
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('upload')
                ->label('Fotos anlegen')
                ->submit('upload'),
        ];
    }
}
