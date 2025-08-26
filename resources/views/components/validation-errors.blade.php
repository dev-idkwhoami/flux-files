@props(['errors' => [], 'title' => null])

@if(!empty($errors))
    <div {{ $attributes->merge(['class' => 'space-y-2']) }}>
        @if($title)
            <flux:heading level="6" class="text-red-600 dark:text-red-400">
                {{ $title }}
            </flux:heading>
        @endif

        @if(is_array($errors))
            @foreach($errors as $error)
                @if(is_string($error))
                    <flux:error class="text-sm">{{ $error }}</flux:error>
                @elseif(is_array($error))
                    @foreach($error as $subError)
                        <flux:error class="text-sm">{{ $subError }}</flux:error>
                    @endforeach
                @endif
            @endforeach
        @elseif(is_string($errors))
            <flux:error class="text-sm">{{ $errors }}</flux:error>
        @endif
    </div>
@endif
