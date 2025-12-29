<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-col gap-4">
            <h2 class="text-lg font-bold tracking-tight text-gray-950 dark:text-white font-serif">
                Akses Cepat
            </h2>
            
            <div class="grid gap-4 md:grid-cols-3">
                @foreach($links as $link)
                    <a href="{{ $link['url'] }}" 
                       class="group relative flex items-center gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition-all hover:shadow-md hover:border-gold-400 dark:border-gray-800 dark:bg-gray-900">
                        
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg {{ $link['color'] }} text-white shadow-sm transition group-hover:scale-110">
                            <x-icon name="{{ $link['icon'] }}" class="h-6 w-6" />
                        </div>

                        <div>
                            <p class="font-semibold text-gray-900 dark:text-white group-hover:text-primary-600">
                                {{ $link['label'] }}
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $link['desc'] }}
                            </p>
                        </div>

                        <div class="absolute right-4 opacity-0 transition group-hover:opacity-100">
                            <x-heroicon-m-arrow-right class="h-5 w-5 text-gray-400" />
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>