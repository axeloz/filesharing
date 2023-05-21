<html>
	<head>
		<meta charset="utf-8">
		<title>
			@hasSection('page_title')
				@yield('page_title') -
			@endif
			{{ config('app.name') }}
		</title>
		<meta name="theme-color" content="#319197">
        @vite('resources/css/app.css')
        @stack('styles')
        @vite('resources/js/app.js')

	</head>

	<body class="font-display text-[13px] selection:bg-purple-100 outline-none select-none">

		<div class="md:fixed md:min-w-xl md:max-w-3xl md:left-[50%] md:top-[50%] md:translate-x-[-50%] md:translate-y-[-50%] md:w-2/3">
			<div class="relative bg-white md:border border-primary md:rounded-lg md:overflow-hidden shadow-lg shadow-black/30 hover:shadow-black/40 transition-all">
				@include('header')

				@yield('content')

				@include('footer')
			</div>
		</div>

        @stack('scripts')

	</body>
</html>
