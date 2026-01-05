<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-col gap-4">
            <h2 class="text-lg font-bold tracking-tight text-gray-950 dark:text-white"
                style="font-family: 'Marcellus', serif;">
                Akses Cepat
            </h2>

            <div class="grid gap-4 md:grid-cols-3">
                @foreach($links as $link)
                    <a href="{{ $link['url'] }}"
                        class="group relative flex items-center gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900"
                        style="transition: all 0.5s ease; border-radius: 20px;">

                        {{-- Icon Box dengan warna Wismilak --}}
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg text-white shadow-sm"
                            style="background-color: #1A4D2E; transition: all 0.5s ease; border-radius: 12px;">
                            <x-icon name="{{ $link['icon'] }}" class="h-6 w-6" style="transition: transform 0.3s ease;" />
                        </div>

                        <div class="flex-1">
                            <p class="font-semibold text-gray-900 dark:text-white"
                                style="font-family: 'Noto Sans Display', sans-serif; transition: color 0.3s ease;">
                                {{ $link['label'] }}
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400"
                                style="font-family: 'Noto Sans Display', sans-serif;">
                                {{ $link['desc'] }}
                            </p>
                        </div>

                        {{-- Arrow indicator yang muncul saat hover --}}
                        <div class="absolute right-4 opacity-0 transition-all duration-300 group-hover:opacity-100 group-hover:translate-x-0"
                            style="transform: translateX(-10px); color: #C4A901;">
                            <span style="font-size: 1.25rem;">➔</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>