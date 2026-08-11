<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Photo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class PhotoBulkUploadController extends Controller
{
    /**
     * Handles one batch of the client-side chunked upload (see the inline
     * script in bulk-upload-photos.blade.php). Deliberately a plain
     * multipart POST, not a Livewire/FilePond upload - this bug class
     * (https://github.com/filamentphp/filament/issues/13306) is what the
     * whole page was rewritten to avoid.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'date_text' => ['nullable', 'string', 'max:255'],
            'is_published' => ['nullable', 'boolean'],
            'images' => ['required', 'array', 'min:1', 'max:15'],
            'images.*' => ['image', 'max:25600'],
        ]);

        $count = 0;
        foreach ($request->file('images', []) as $file) {
            Photo::create([
                'title' => $this->titleFromUploadedFile($file),
                'image_path' => $file->store('photos/'.now()->format('Y/m'), 'public'),
                'category_id' => $data['category_id'],
                'location_id' => $data['location_id'] ?? null,
                'date_text' => $data['date_text'] ?? null,
                'is_published' => $data['is_published'] ?? false,
                'uploaded_by' => $request->user()->id,
            ]);
            $count++;
        }

        return response()->json(['created' => $count]);
    }

    private function titleFromUploadedFile(UploadedFile $file): string
    {
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $name = str_replace(['-', '_'], ' ', $name);
        $name = trim(preg_replace('/\s+/', ' ', $name));

        return $name !== '' ? Str::ucfirst($name) : 'Foto';
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->is_admin, 403);
    }
}
