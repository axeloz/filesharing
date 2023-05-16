@extends('layout')


@push('scripts')
<script>
	document.addEventListener('alpine:init', () => {
		Alpine.data('bundle', () => ({
			bundles: null,

			init: function() {
				bundles = localStorage.getItem('bundles');

				if (bundles == null || bundles == '') {
					console.log('creating bundles')
					this.bundles = []
				}
				else {
					this.bundles = JSON.parse(bundles)
				}
			},

			newBundle: function() {
				// Generating a new bundle key pair
				const pair = {
					bundle_id: this.generateStr(30),
					owner_token: this.generateStr(15)
				}
				this.bundles.push(pair)

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
			}
		}))
	})
</script>
@endpush

@section('content')
	<div x-data="bundle">
		<div class="relative bg-white border border-primary rounded-lg overflow-hidden">
			<div class="bg-gradient-to-r from-primary-light to-primary px-2 py-4 text-center">
				<h1 class="relative font-title font-medium font-body text-4xl text-center text-white uppercase flex items-center">

					<div class="grow text-center">{{ config('app.name') }}</div>
				</h1>
			</div>

			<div class="my-10 text-center text-base font-title uppercase text-primary">
				<a x-on:click="newBundle()" class="cursor-pointer border px-5 py-3 border-primary rounded hover:bg-primary hover:text-white text-primary">
					@lang('app.create-new-upload')
				</a>
			</div>
		</div>
	</div>
@endsection
