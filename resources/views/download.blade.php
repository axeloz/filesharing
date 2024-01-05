@extends('layout')

@section('page_title', $metadata['title'] ?? null)

@push('scripts')
<script>
	let bundle  		= @js($bundle);
	let bundle_expires	= '{{ __('app.warning-bundle-expiration') }}'
	let bundle_expired	= '{{ __('app.warning-bundle-expired') }}'

	document.addEventListener('alpine:init', () => {
		Alpine.data('download', () => ({
			metadata: @js($bundle),
			created_at: null,
			expires_at: null,
			expired: null,
			interval: null,

			init: function() {
				this.updateTimes()

				this.interval = window.setInterval( () => {
					this.updateTimes()
				}, 5000)

			},

			updateTimes: function() {
				this.created_at = moment(this.metadata.created_at).fromNow()

				if (this.metadata.expiry) {
					if (! this.isExpired()) {
						this.expires_at = moment(this.metadata.expires_at).fromNow()
					}
				}
			},

			isExpired: function() {
				if (moment().isAfter(moment.unix(this.metadata.expires_at))) {
					this.expired = true
					return true
				}
				else {
					this.expired = false
					return false
				}
			},

			humanSize: function(val) {
				if (val >= 100000000) {
					return (val / 1000000000).toFixed(1) + ' Go'
				}
				else if (val >= 1000000) {
					return (val / 1000000).toFixed(1) + ' Mo'
				}
				else if (val >= 1000) {
					return (val / 1000).toFixed(1) + ' Ko'
				}
				else {
					return val + ' o'
				}
			},

			downloadAll: function() {
				window.location.href = this.metadata.download_link
			}
		}))
	})

</script>
@endpush

@section('content')
	<div x-data="download">

		<div class="p-5">
			<h2 class="font-title text-2xl mb-5 text-primary font-medium uppercase">
				@lang('app.preview-bundle')
			</h2>

			<div class="flex flex-wrap justify-between items-center text-xs">
				<p class="w-full px-1">
					<span class="font-title text-xs text-primary uppercase mr-1">
						@lang('app.upload-title')
					</span>
					<span x-text="metadata.title"></span>
				</p>
				<p class="w-1/2 px-1 mt-1">
					<span class="font-title text-xs text-primary uppercase mr-1">
						@lang('app.created-at')
					</span>
					<span x-text="created_at"></span>
				</p>
				<p class="w-1/2 px-1 mt-1">
					<span class="font-title text-xs text-primary uppercase mr-1">
						@lang('app.upload-expiry')
					</span>
					<template x-if="expires_at">
						<span x-text="expires_at"></span>
					</template>
					<template x-if="! expires_at">
						<span>@lang('app.forever')</span>
					</template>
				</p>
				<p class="w-1/2 px-1 mt-1">
					<span class="font-title text-xs text-primary uppercase mr-1">
						@lang('app.files')
					</span>
					<span x-text="Object.keys(metadata.files).length"></span>
				</p>
				<p class="w-1/2 px-1 mt-1">
					<span class="font-title text-xs text-primary uppercase mr-1">
						@lang('app.fullsize')
					</span>
					<span x-text="humanSize(metadata.fullsize)"></span>
				</p>
				<p class="w-1/2 px-1 mt-1">
					<span class="font-title text-xs text-primary uppercase mr-1">
						@lang('app.current-downloads')
					</span>
					<span x-text="metadata.downloads"></span>
				</p>
				<p class="w-1/2 px-1 mt-1">
					<span class="font-title text-xs text-primary uppercase mr-1">
						@lang('app.max-downloads')
					</span>
					<span x-text="metadata.max_downloads > 0 ? metadata.max_downloads : '∞'"></span>
				</p>
				<p class="w-full px-1 mt-1" x-show="metadata.description">
					<span class="font-title text-xs text-primary uppercase mr-1">
						@lang('app.upload-description')
					</span>
					<span x-html="metadata.description_html"></span>
				</p>
			</div>

			<div>
				<h2 class="font-title font-xs uppercase text-primary px-1 mt-5">Files</h2>
			</div>

			<ul id="output" class="text-xs mt-1 max-h-32 overflow-y-scroll pb-3" x-show="Object.keys(metadata.files).length > 0">
				<template x-for="f in metadata.files" :key="f.uuid">
					<li class="leading-5 list-inside even:bg-gray-50 rounded px-2">
						<p class="float-left w-9/12 overflow-hidden" x-text="f.original.substring(0, 40)"></p>
						<p class="float-right w-2/12 text-right" float-right text-xs" x-text="humanSize(f.filesize)"></p>
						<span class="clear-both">&nbsp;</span>
					</li>
				</template>
			</ul>

			<div class="grid grid-cols-2 gap-10 mt-10 text-center items-center">
				<div>
					&nbsp;
				</div>
				<div>
					@include('partials.button', [
						'way'		=> 'right',
						'text'		=> __('app.download-all'),
						'icon'		=> '<path stroke-linecap="round" stroke-linejoin="round" d="M7.5 7.5h-.75A2.25 2.25 0 004.5 9.75v7.5a2.25 2.25 0 002.25 2.25h7.5a2.25 2.25 0 002.25-2.25v-7.5a2.25 2.25 0 00-2.25-2.25h-.75m-6 3.75l3 3m0 0l3-3m-3 3V1.5m6 9h.75a2.25 2.25 0 012.25 2.25v7.5a2.25 2.25 0 01-2.25 2.25h-7.5a2.25 2.25 0 01-2.25-2.25v-.75" />',
						'action'	=> 'downloadAll'
					])
				</div>
			</div>
		</div>
	</div>
@endsection
