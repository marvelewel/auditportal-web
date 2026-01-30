{{-- File Viewer Modal - Livewire + Alpine.js --}}
{{-- Optimized: Larger PDF frame, loading indicator, faster transitions --}}
<div x-data="{ 
        show: @entangle('isOpen').live,
        zoomLevel: @entangle('zoomLevel').live,
        pdfLoading: true
    }" x-show="show" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-100"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
    @keydown.escape.window="show && $wire.closeModal()" class="fixed inset-0 z-50 overflow-hidden"
    style="display: none;">
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-black/80 backdrop-blur-sm" @click="$wire.closeModal()"></div>

    {{-- Modal Container - FULLSCREEN untuk maksimal viewing --}}
    <div class="fixed inset-0 flex items-center justify-center p-2 sm:p-4">
        <div x-show="show" x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95" @click.stop
            class="relative bg-white dark:bg-gray-900 rounded-xl shadow-2xl w-full max-w-7xl h-[95vh] flex flex-col overflow-hidden">
            {{-- Header - Compact --}}
            <div
                class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                <div class="flex items-center gap-3">
                    {{-- File Icon --}}
                    @if($fileType === 'pdf')
                        <div class="p-2 bg-red-100 dark:bg-red-900/30 rounded-lg">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                </path>
                            </svg>
                        </div>
                    @elseif($fileType === 'image')
                        <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                        </div>
                    @elseif($fileType === 'word')
                        <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M6 2a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6H6zm7 1.5L18.5 9H13V3.5zM7 17l1.5-6 1.5 4 1.5-4L13 17h-1l-.75-3-.75 3-.75-3-.75 3H7z" />
                            </svg>
                        </div>
                    @elseif($fileType === 'excel')
                        <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M6 2a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6H6zm7 1.5L18.5 9H13V3.5zM8 13h2v2H8v-2zm0 3h2v2H8v-2zm3-3h2v2h-2v-2zm0 3h2v2h-2v-2zm3-3h2v2h-2v-2zm0 3h2v2h-2v-2z" />
                            </svg>
                        </div>
                    @else
                        <div class="p-2 bg-gray-100 dark:bg-gray-700 rounded-lg">
                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                        </div>
                    @endif

                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white truncate max-w-lg">
                            {{ $fileName ?? 'File Viewer' }}
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ strtoupper($fileType ?? 'Unknown') }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    {{-- Zoom Controls (for Image only) --}}
                    @if($fileType === 'image')
                        <div class="flex items-center gap-1 px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded-lg mr-2">
                            <button wire:click="zoomOut"
                                class="p-1.5 hover:bg-gray-200 dark:hover:bg-gray-600 rounded transition-colors"
                                title="Zoom Out">
                                <svg class="w-4 h-4 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4">
                                    </path>
                                </svg>
                            </button>
                            <span
                                class="px-2 text-sm font-medium text-gray-700 dark:text-gray-300 min-w-[50px] text-center">
                                {{ $zoomLevel }}%
                            </span>
                            <button wire:click="zoomIn"
                                class="p-1.5 hover:bg-gray-200 dark:hover:bg-gray-600 rounded transition-colors"
                                title="Zoom In">
                                <svg class="w-4 h-4 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4"></path>
                                </svg>
                            </button>
                            <button wire:click="resetZoom"
                                class="p-1.5 hover:bg-gray-200 dark:hover:bg-gray-600 rounded transition-colors ml-1"
                                title="Reset Zoom">
                                <svg class="w-4 h-4 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                    </path>
                                </svg>
                            </button>
                        </div>
                    @endif

                    {{-- Download Button --}}
                    <button wire:click="downloadFile"
                        class="inline-flex items-center gap-2 px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors text-sm"
                        title="Download File">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        <span class="hidden sm:inline">Download</span>
                    </button>

                    {{-- Close Button --}}
                    <button wire:click="closeModal"
                        class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                        title="Close (ESC)">
                        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Content Area - MAXIMIZED --}}
            <div class="flex-1 overflow-hidden bg-gray-200 dark:bg-gray-800">
                @if($fileUrl)
                    {{-- PDF Viewer - FULL SIZE with loading indicator --}}
                    @if($fileType === 'pdf')
                        <div class="relative w-full h-full">
                            {{-- Loading Skeleton --}}
                            <div x-show="pdfLoading"
                                class="absolute inset-0 flex items-center justify-center bg-gray-100 dark:bg-gray-800">
                                <div class="flex flex-col items-center gap-4">
                                    <div
                                        class="animate-spin rounded-full h-12 w-12 border-4 border-primary-500 border-t-transparent">
                                    </div>
                                    <p class="text-gray-500 dark:text-gray-400 text-sm">Memuat dokumen...</p>
                                </div>
                            </div>

                            {{-- PDF iframe - Full Height --}}
                            <iframe src="{{ $fileUrl }}#toolbar=0&navpanes=0&scrollbar=1&view=FitH"
                                class="w-full h-full border-0" @load="pdfLoading = false"
                                style="min-height: calc(95vh - 120px);"></iframe>
                        </div>

                        {{-- Image Viewer --}}
                    @elseif($fileType === 'image')
                        <div class="w-full h-full flex items-center justify-center overflow-auto p-4">
                            <img src="{{ $fileUrl }}" alt="{{ $fileName }}"
                                class="max-w-none rounded-lg shadow-lg transition-transform duration-200"
                                style="transform: scale({{ $zoomLevel / 100 }});">
                        </div>

                        {{-- Office Files (Word, Excel, PowerPoint) --}}
                    @elseif(in_array($fileType, ['word', 'excel', 'powerpoint']))
                        @php
                            $officeUrl = $this->getOfficeViewerUrl();
                        @endphp

                        @if($officeUrl)
                            <div class="w-full h-full">
                                <iframe src="{{ $officeUrl }}" class="w-full h-full border-0"></iframe>
                            </div>
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center text-center p-8">
                                <div class="p-4 bg-yellow-100 dark:bg-yellow-900/30 rounded-full mb-4">
                                    <svg class="w-12 h-12 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                        </path>
                                    </svg>
                                </div>
                                <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                    Preview Tidak Tersedia
                                </h4>
                                <p class="text-gray-500 dark:text-gray-400 mb-4 max-w-md">
                                    File Office ini tidak dapat ditampilkan secara langsung. Silakan download untuk melihat isinya.
                                </p>
                                <button wire:click="downloadFile"
                                    class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                    </svg>
                                    Download {{ strtoupper($fileType) }}
                                </button>
                            </div>
                        @endif

                        {{-- Unknown File Type --}}
                    @else
                        <div class="w-full h-full flex flex-col items-center justify-center text-center p-8">
                            <div class="p-4 bg-gray-100 dark:bg-gray-700 rounded-full mb-4">
                                <svg class="w-12 h-12 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                Tipe File Tidak Didukung
                            </h4>
                            <p class="text-gray-500 dark:text-gray-400 mb-4 max-w-md">
                                Preview untuk tipe file ini tidak tersedia. Silakan download untuk melihat isinya.
                            </p>
                            <button wire:click="downloadFile"
                                class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                                Download File
                            </button>
                        </div>
                    @endif
                @else
                    {{-- Loading State (no file URL yet) --}}
                    <div class="w-full h-full flex items-center justify-center">
                        <div class="flex flex-col items-center gap-4">
                            <div
                                class="animate-spin rounded-full h-12 w-12 border-4 border-primary-500 border-t-transparent">
                            </div>
                            <p class="text-gray-500 dark:text-gray-400">Memuat file...</p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Footer - Minimal --}}
            <div class="px-4 py-2 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                <p class="text-xs text-gray-400 dark:text-gray-500 text-center">
                    Tekan <kbd class="px-1 py-0.5 bg-gray-200 dark:bg-gray-600 rounded text-xs font-mono">ESC</kbd>
                    untuk menutup
                </p>
            </div>
        </div>
    </div>
</div>