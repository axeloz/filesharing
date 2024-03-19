@empty($way)
    @php $way = 'right';  @endphp
@endif

<button
    class="inline-flex items-center w-full py-1 rounded bg-primary hover:bg-primary-light text-white text-base text-center"
    x-on:click="{{ $action }}()"
>
    <p class="w-10/12 {{ $way == 'left' ? 'order-last' : '' }}">{{ $text }}</p>

    <p class="w-2/12 {{ $way == 'left' ? 'border-r' : 'border-l' }} px-2 border-primary-light">
        @if (! empty($icon))
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 inline">
                {!! $icon !!}
            </svg>
        @endif
    </p>

</button>
