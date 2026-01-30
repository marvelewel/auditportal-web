<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class FileViewerModal extends Component
{
    public bool $isOpen = false;
    public ?string $fileUrl = null;
    public ?string $fileName = null;
    public ?string $downloadFileName = null;
    public ?string $fileType = null;
    public ?string $filePath = null;
    public string $disk = 'public';
    public int $zoomLevel = 100;

    protected $listeners = ['openFileViewer' => 'openFile'];

    /**
     * Open file viewer modal
     * 
     * @param string $path File path in storage
     * @param string $disk Storage disk name
     * @param string|null $displayName Custom name to show (e.g., document title)
     */
    public function openFile(string $path, string $disk = 'public', ?string $displayName = null): void
    {
        $this->disk = $disk;
        $this->filePath = $path;

        // Get file extension from original path
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        // Gunakan displayName jika ada, jika tidak gunakan basename
        $this->fileName = $displayName ?? basename($path);

        // Untuk download, pastikan nama file memiliki ekstensi yang benar
        // Sanitize filename untuk menghapus karakter tidak valid
        $safeName = preg_replace('/[^a-zA-Z0-9\s\-\_\.\(\)]+/u', '', $this->fileName);
        $safeName = trim($safeName);

        // Jika displayName tidak memiliki ekstensi, tambahkan
        if ($displayName && !str_ends_with(strtolower($safeName), '.' . strtolower($extension))) {
            $this->downloadFileName = $safeName . '.' . $extension;
        } else {
            $this->downloadFileName = $safeName ?: basename($path);
        }

        // Build URL manually to ensure it uses the correct port
        $this->fileUrl = url('/storage/' . $path);

        $this->fileType = $this->detectFileType($path);
        $this->zoomLevel = 100;
        $this->isOpen = true;
    }

    /**
     * Close modal
     */
    public function closeModal(): void
    {
        $this->isOpen = false;
        $this->fileUrl = null;
        $this->fileName = null;
        $this->downloadFileName = null;
        $this->fileType = null;
        $this->filePath = null;
        $this->zoomLevel = 100;
    }

    /**
     * Zoom in
     */
    public function zoomIn(): void
    {
        if ($this->zoomLevel < 200) {
            $this->zoomLevel += 25;
        }
    }

    /**
     * Zoom out
     */
    public function zoomOut(): void
    {
        if ($this->zoomLevel > 50) {
            $this->zoomLevel -= 25;
        }
    }

    /**
     * Reset zoom
     */
    public function resetZoom(): void
    {
        $this->zoomLevel = 100;
    }

    /**
     * Download file with proper filename
     */
    public function downloadFile()
    {
        if (!$this->filePath) {
            return null;
        }

        // Gunakan downloadFileName yang sudah di-sanitize dengan ekstensi yang benar
        $filename = $this->downloadFileName ?? basename($this->filePath);

        return Storage::disk($this->disk)->download($this->filePath, $filename);
    }

    /**
     * Detect file type from extension
     */
    protected function detectFileType(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($extension) {
            'pdf' => 'pdf',
            'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg' => 'image',
            'doc', 'docx' => 'word',
            'xls', 'xlsx' => 'excel',
            'ppt', 'pptx' => 'powerpoint',
            default => 'unknown',
        };
    }

    /**
     * Get Office Online Viewer URL
     */
    public function getOfficeViewerUrl(): ?string
    {
        if (!in_array($this->fileType, ['word', 'excel', 'powerpoint'])) {
            return null;
        }

        // Microsoft Office Online Viewer
        return 'https://view.officeapps.live.com/op/embed.aspx?src=' . urlencode($this->fileUrl);
    }

    public function render()
    {
        return view('livewire.file-viewer-modal');
    }
}
