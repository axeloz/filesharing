<div {{ $attributes }}
    class="px-2 py-1 rounded text-xs cursor-default z-50 absolute -top-8 left-1/2 bg-primary text-white"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 scale-90"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-90"
    x-cloak>
    @lang('app.copied')
</div>
