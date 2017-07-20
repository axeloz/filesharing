@extends('master')

@section('page', 'home')

@section('content')
	<h1>@lang('app.cannot-upload')</h1>
	@lang('app.cannot-upload-blocked-ip')
@endsection
