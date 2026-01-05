@php
    $properties = $getRecord()->properties ?? [];
    $old = $properties['old'] ?? [];
    $new = $properties['attributes'] ?? [];
    
    // Merge keys from both arrays
    $allKeys = array_unique(array_merge(array_keys($old), array_keys($new)));
    
    // Format field name helper
    $formatField = fn($field) => ucwords(str_replace('_', ' ', $field));
    
    // Format value helper
    $formatValue = function($value) {
        if (is_null($value)) return '-';
        if (is_bool($value)) return $value ? 'Yes' : 'No';
        if (is_array($value)) return json_encode($value, JSON_PRETTY_PRINT);
        if (is_string($value) && strlen($value) > 100) return substr($value, 0, 100) . '...';
        return (string) $value;
    };
@endphp

<div class="space-y-4">
    @if(empty($allKeys))
        <div class="text-sm text-gray-500 dark:text-gray-400 italic">
            Tidak ada detail perubahan yang tercatat.
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="py-2 px-4 text-left font-semibold text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-800" style="width: 25%;">
                            Field
                        </th>
                        <th class="py-2 px-4 text-left font-semibold text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-800" style="width: 37.5%;">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-100 dark:bg-red-900">
                                    <x-heroicon-o-minus class="w-4 h-4 text-red-600 dark:text-red-400" />
                                </span>
                                Sebelum
                            </div>
                        </th>
                        <th class="py-2 px-4 text-left font-semibold text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-800" style="width: 37.5%;">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-green-100 dark:bg-green-900">
                                    <x-heroicon-o-plus class="w-4 h-4 text-green-600 dark:text-green-400" />
                                </span>
                                Sesudah
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allKeys as $key)
                        @php
                            $oldValue = $old[$key] ?? null;
                            $newValue = $new[$key] ?? null;
                            $hasChanged = $oldValue !== $newValue;
                        @endphp
                        <tr class="border-b border-gray-100 dark:border-gray-800 {{ $hasChanged ? 'bg-amber-50/50 dark:bg-amber-900/10' : '' }}">
                            <td class="py-3 px-4 font-medium text-gray-900 dark:text-gray-100 align-top">
                                {{ $formatField($key) }}
                                @if($hasChanged)
                                    <span class="ml-1 inline-flex items-center rounded-full bg-amber-100 dark:bg-amber-900 px-2 py-0.5 text-xs font-medium text-amber-800 dark:text-amber-200">
                                        Changed
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-gray-600 dark:text-gray-400 align-top {{ $hasChanged && !is_null($oldValue) ? 'bg-red-50 dark:bg-red-900/20' : '' }}">
                                <div class="font-mono text-xs whitespace-pre-wrap break-words">
                                    {{ $formatValue($oldValue) }}
                                </div>
                            </td>
                            <td class="py-3 px-4 text-gray-600 dark:text-gray-400 align-top {{ $hasChanged && !is_null($newValue) ? 'bg-green-50 dark:bg-green-900/20' : '' }}">
                                <div class="font-mono text-xs whitespace-pre-wrap break-words">
                                    {{ $formatValue($newValue) }}
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        {{-- Summary --}}
        <div class="flex items-center gap-4 pt-2 text-xs text-gray-500 dark:text-gray-400">
            <span class="flex items-center gap-1">
                <span class="w-3 h-3 rounded-full bg-amber-200 dark:bg-amber-700"></span>
                {{ count(array_filter($allKeys, fn($k) => ($old[$k] ?? null) !== ($new[$k] ?? null))) }} field(s) changed
            </span>
        </div>
    @endif
</div>
