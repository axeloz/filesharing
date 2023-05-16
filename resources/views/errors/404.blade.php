@extends('layout')

@section('content')
	<div>
		<div class="relative bg-white border border-primary rounded-lg overflow-hidden">
			<div class="bg-gradient-to-r from-primary-light to-primary px-2 py-4 text-center">
				<h1 class="relative font-title font-medium font-body text-4xl text-center text-white uppercase flex items-center">

					<div class="grow text-center">{{ config('app.name') }}</div>
				</h1>
			</div>

			<div class="my-10 text-center text-base font-title uppercase text-primary">
				<h1 class="text-7xl mb-0 font-black">404</h1>
				@lang('app.page-not-found')
			</div>
		</div>
	</div>
@endsection
