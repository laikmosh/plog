<div>
    @if($label)
        <label class="block text-sm font-medium text-gray-700 mb-2">{{ $label }}</label>
    @endif

    <!-- Selected values display -->
    @if(!empty($values))
        <div class="mb-2 flex flex-wrap gap-1">
            @foreach($values as $value)
                <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 border border-blue-200 rounded-full text-xs">
                    {{ Str::limit($value, 20) }}
                    <button wire:click="removeValue('{{ $value }}')" class="text-blue-600 hover:text-blue-800">×</button>
                </span>
            @endforeach
        </div>
    @endif

    <div class="relative">
        <input type="text"
               value="{{ $this->display_text }}"
               wire:click="toggleDropdown"
               placeholder="{{ $icon }} {{ $placeholder }}"
               autocomplete="off"
               readonly
               class="w-full {{ $label ? 'px-3 py-2' : 'px-2 py-1 text-xs' }} pr-8 border border-gray-300 {{ $label ? 'rounded-lg' : 'rounded' }} bg-white cursor-pointer focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
        @if(!empty($values))
            <button type="button" wire:click="clearAllValues" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 {{ $label ? 'text-xl' : 'text-sm' }} leading-none">
                ×
            </button>
        @endif
    </div>

    @if($showDropdown)
        <div class="absolute z-50 w-72 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto">
            @forelse($this->options as $option)
                <div wire:click.stop="selectValue('{{ $option['value'] }}')"
                     class="flex justify-between items-center px-3 py-2 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0
                            {{ in_array($option['value'], $values) ? 'bg-blue-50 border-blue-200' : '' }}">
                    <div class="flex items-center gap-2">
                        @if(in_array($option['value'], $values))
                            <span class="text-blue-600">✓</span>
                        @endif
                        <span class="font-medium text-gray-700 truncate">{{ $option['label'] }}</span>
                    </div>
                    <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full ml-2 flex-shrink-0">{{ $option['count'] }} logs</span>
                </div>
            @empty
                <div class="px-3 py-2 text-gray-500 text-sm">No options available</div>
            @endforelse
        </div>
    @endif
</div>