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
		<link rel="icon" type="image/png" href="/favicon/favicon-96x96.png" sizes="96x96" />
		<link rel="icon" type="image/svg+xml" href="/favicon/favicon.svg" />
		<link rel="shortcut icon" href="/favicon/favicon.ico" />
		<link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png" />
		<link rel="manifest" href="/favicon/site.webmanifest" />
        @vite('resources/css/app.css')
        @stack('styles')
        @vite('resources/js/app.js')

		<script>
			const BASE_URL = '{{ route('homepage') }}'
		</script>

	</head>

	<body class="font-display  selection:bg-purple-100 outline-none select-none">

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
