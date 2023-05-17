@extends('layout')

@section('page_title', $metadata['title'] ?? null)

@push('scripts')
<script>
	let auth		  	= @js($auth);
	let bundleId  		= @js($bundleId);
	let bundle_expires	= '{{ __('app.warning-bundle-expiration') }}'
	let bundle_expired	= '{{ __('app.warning-bundle-expired') }}'

	document.addEventListener('alpine:init', () => {
		Alpine.data('download', () => ({
			metadata: @js($metadata),
			created_at: null,
			expires_at: null,
			expired: null,

			init: function() {
				this.updateTimes()

				window.setInterval( () => {
					this.updateTimes()
				}, 5000)
			},

			updateTimes: function() {
				this.created_at = moment.unix(this.metadata.created_at).fromNow()

				if (this.isExpired()) {
					this.expires_at = bundle_expired
				}
				else {
					this.expires_at = bundle_expires+' '+moment.unix(this.metadata.expires_at).fromNow()
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
		<div class="relative bg-white border border-primary rounded-lg overflow-hidden">
			<div class="bg-gradient-to-r from-primary-light to-primary px-2 py-4 mb-3 text-center">
				<h1 class="relative font-title font-medium font-body text-4xl text-center text-white uppercase flex items-center">
					<div class="w-1/12 text-center">&nbsp;</div>

					<div class="grow text-center">{{ config('app.name') }}</div>
				</h1>
			</div>

			<div class="p-5">
				<h2 class="font-title text-2xl mb-5 text-primary font-medium uppercase">
					@lang('app.preview-bundle')
				</h2>

				<div class="flex flex-wrap items-center">
					<p class="w-6/12 px-1">
						<span class="font-title text-xs text-primary uppercase mr-1">
							@lang('app.upload-title')
						</span>
						<span x-text="metadata.title"></span>
					</p>
					<p class="w-4/12 px-1">
						<span class="font-title text-xs text-primary uppercase mr-1">
							@lang('app.created-at')
						</span>
						<span x-text="created_at"></span>
					</p>
					<p class="w-2/12 px-1">
						<span class="font-title text-xs text-primary uppercase mr-1">
							@lang('app.fullsize')
						</span>
						<span x-text="humanSize(metadata.fullsize)"></span>
					</p>
					<p class="w-full px-1" x-show="metadata.description">
						<span class="font-title text-xs text-primary uppercase mr-1">
							@lang('app.upload-description')
						</span>
						<span x-text="metadata.description"></span>
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
						<p class="font-xs font-medium">
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="inline w-4 h-4">
								<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
							</svg>
					    	<span x-text="expires_at"></span>
						</p>
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
	</div>
@endsection