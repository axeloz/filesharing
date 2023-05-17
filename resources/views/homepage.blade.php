@extends('layout')


@push('scripts')
<script>
	document.addEventListener('alpine:init', () => {
		Alpine.data('bundle', () => ({
			bundles: null,
			pending: [],
			active: [],
			expired: [],
			currentBundle: null,

			init: function() {
				// Getting bundles stored locally
				bundles = localStorage.getItem('bundles');
				// And JSON decoding it
				this.bundles = JSON.parse(bundles)

				if (this.bundles != null && Object.keys(this.bundles).length > 0) {

					this.bundles.forEach( (bundle) => {
						if (bundle.title == null || bundle.title == '') {
							bundle.label = 'untitled'
						}
						else {
							bundle.label = bundle.title
						}

						if (bundle.expires_at != null && moment.unix(bundle.expires_at).isBefore(moment())) {
							this.expired.push(bundle)
						}
						else if (bundle.completed == true) {
							this.active.push(bundle)
						}
						else {
							this.pending.push(bundle)
						}
						bundle.label += ' - {{ __('app.created-at') }} '+moment.unix(bundle.created_at).fromNow()
					})
				}

				// If bundle is empty, initializing it
				if (this.bundles == null || this.bundles == '') {
					this.bundles = []
				}
			},

			newBundle: function() {
				// Generating a new bundle key pair
				const pair = {
					bundle_id: this.generateStr(30),
					owner_token: this.generateStr(15),
					created_at: moment().unix()
				}
				this.bundles.unshift(pair)

				// Storing them locally
				localStorage.setItem('bundles', JSON.stringify(this.bundles))

				axios({
					url: '/new',
					method: 'POST',
					data: {
						bundle_id: pair.bundle_id,
						owner_token: pair.owner_token
					}
				})
				.then( (response) => {
					window.location.href = '/upload/'+response.data.bundle_id
				})
				.catch( (error) => {
					//TODO: do something here
				})
			},

			generateStr: function(length) {
				const characters ='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

				let result = '';
				const charactersLength = characters.length;
				for ( let i = 0; i < length; i++ ) {
					result += characters.charAt(Math.floor(Math.random() * charactersLength));
				}

				return result;
			},

			redirectToBundle: function() {
				if (this.currentBundle != null) {
					window.location.href = '/upload/'+this.currentBundle
				}
			},

			isBundleExpired: function() {
				if (this.metadata.expires_at == null || this.metadata.expires_at == '') {
					return false;
				}

				return moment.unix(this.metadata.expires_at).isBefore(moment())
			},
		}))
	})
</script>
@endpush

@section('content')
	<div x-data="bundle">
		<div class="bg-gradient-to-r from-primary-light to-primary px-2 py-4 text-center">
			<h1 class="relative font-title font-medium font-body text-4xl text-center text-white uppercase flex items-center">
				<div class="grow text-center">{{ config('app.name') }}</div>
			</h1>
		</div>

		<div class="p-5">
			<h2 class="font-title text-2xl mb-5 text-primary font-medium uppercase flex items-center">
				<p>@lang('app.existing-bundles')</p>
				<p class="text-sm bg-primary rounded-full ml-2 text-white px-3" x-text="Object.keys(bundles).length"></p>
			</h2>

			<span x-show="bundles == null || Object.keys(bundles).length == 0">@lang('app.no-existing-bundle')</span>
			<select
				class="w-full py-4 text-slate-700 bg-transparent h-8 p-0 py-1 border-b border-primary-superlight focus:ring-0 invalid:border-b-red-500 invalid:bg-red-50"
				name="expiry"
				id="upload-expiry"
				x-model="currentBundle"
				x-on:change="redirectToBundle()"
				x-show="bundles != null && Object.keys(bundles).length > 0"
			>
				<option>-</option>

				<template x-if="Object.keys(pending).length > 0">
					<optgroup label="{{ __('app.pending') }}">
						<template x-for="bundle in pending">
							<option :value="bundle.bundle_id" x-text="bundle.label"></option>
						</template>
					</optgroup>
				</template>

				<template x-if="Object.keys(active).length > 0">
					<optgroup label="{{ __('app.active') }}">
						<template x-for="bundle in active">
							<option :value="bundle.bundle_id" x-text="bundle.label"></option>
						</template>
					</optgroup>
				</template>

				<template x-if="Object.keys(expired).length > 0">
					<optgroup label="{{ __('app.expired') }}">
						<template x-for="bundle in expired">
							<option :value="bundle.bundle_id" x-text="bundle.label"></option>
						</template>
					</optgroup>
				</template>
			</select>

			<h2 class="mt-10 font-title text-2xl mb-5 text-primary font-medium uppercase">@lang('app.or-create')</h2>

			<div class="my-8 text-center text-base font-title uppercase text-primary">
				<a x-on:click="newBundle()" class="cursor-pointer border px-5 py-3 border-primary rounded hover:bg-primary hover:text-white text-primary">
					@lang('app.create-new-upload')
				</a>
			</div>
		</div>
	</div>
@endsection
