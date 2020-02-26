@extends('master')

@section('page', 'download')

@section('content')
	<h1>@lang('app.bundle-preview-title')</h1>

	@if (!empty($metadata['files']) && count($metadata['files']) > 0)

		@lang('app.bundle-preview-intro')<br />
		@lang('app.download-all-or-one')<br /><br />

		@if (! empty($metadata['title']))
			<h2>{{ $metadata['title'] }}</h2>
		@endif

		<ul id="files-list">
			@foreach ($metadata['files'] as $f)
				<li>
					<a href="{{ route('file.download', ['bundle' => $bundle_id, 'file' => $f['filename'], 'auth' => $metadata['view-auth'] ]) }}">
						{{ $f['original'] }}
					</a>
					<span class="filesize">({{ Upload::humanFilesize($f['filesize']) }})</span>
				</li>
			@endforeach
		</ul>

		@if (count($metadata['files']) > 1)
			<p class="download-all-btn">
				<a href="{{ route('bundle.download', ['bundle' => $bundle_id, 'auth' => $metadata['view-auth'] ])}}">
					@lang('app.download-all')
				</a>
				<br />
				<span class="bundle-info">
					({{ Upload::humanFilesize($metadata['fullsize']) }} @lang('app.for') {{ count($metadata['files']) }} {{ trans_choice('app.files', count($metadata['files'])) }})
				</span>
			</p>
		@else
			<p class="download-all-btn">
				<a href="{{ route('file.download', ['bundle' => $bundle_id, 'file' => $f['filename'], 'auth' => $metadata['view-auth'] ])}}">
					@lang('app.download-all')
				</a>
				<br />
				<span class="bundle-info">
					({{ Upload::humanFilesize($metadata['fullsize']) }} @lang('app.for') {{ count($metadata['files']) }} {{ trans_choice('app.files', count($metadata['files'])) }})
				</span>
			</p>
		@endif

		@if (!empty($metadata['expires_at_carbon']))
			<p class="expiry-warning">
				@lang('app.warning-bundle-expiration', ['date' => $metadata['expires_at_carbon']->diffForHumans()])
			</p>
		@endif
	@else
		<p class="error">@lang('app.no-file-in-this-bundle')</p>
	@endif
@endsection
