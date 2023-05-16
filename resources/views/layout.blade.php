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

		<div class="fixed min-w-xl max-w-3xl left-[50%] top-[50%] translate-x-[-50%] translate-y-[-50%] md:w-2/3">@yield('content')</div>

        @stack('scripts')

	</body>
</html>
