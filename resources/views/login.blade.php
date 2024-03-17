@extends('layout')

@section('page_title', __('app.authentication'))

@push('scripts')
<script>
	document.addEventListener('alpine:init', () => {
		Alpine.data('login', () => ({
			user: {
				login: null,
				password: null
			},
			error: null,

			loginUser: function() {
				errors = false
				document.getElementById('user-login').setCustomValidity('')
				document.getElementById('user-password').setCustomValidity('')

				if (this.user.login == null || this.user.login == '') {
					document.getElementById('user-login').setCustomValidity('Field is required')
					errors = true
				}

				if (this.user.password == null || this.user.password == '') {
					document.getElementById('user-password').setCustomValidity('Field is required')
					errors = true
				}

				if (errors === true) {
					return false
				}

				axios({
					url: BASE_URL+'/login',
					method: 'POST',
					data: {
						login: this.user.login,
						password: this.user.password
					}
				})
				.then( (response) => {
					if (response.data.result == true) {
						window.location.href = BASE_URL+'/'
					}
				})
				.catch( (error) => {
					this.error = error.response.data.message
				})
			}
		}))
	})
</script>
@endpush

@section('content')
	<div x-data="login">
		<div class="p-5">
			<h2 class="font-title text-2xl mb-5 text-primary font-medium uppercase flex items-center">
				<p>@lang('app.authentication')</p>
			</h2>

			<template x-if="error">
				<div class="w-full my-3 rounded px-3 py-2 bg-red-100 text-red-600" x-text="error"></div>
			</template>


			{{-- Login --}}
			<div class="">
				<p class="font-title uppercase">
					@lang('app.login')
					<span class="text-base">*</span>
				</p>

				<input
					x-model="user.login"
					class="w-full p-0 bg-transparent text-slate-700 h-8 py-1 rounded-none border-b border-purple-300 outline-none invalid:border-b-red-500 invalid:bg-red-50"
					type="text"
					name="login"
					id="user-login"
					maxlength="40"
					@keyup.enter="loginUser()"
				/>
			</div>

			{{-- Password --}}
			<div class="mt-5">
				<p class="font-title uppercase">
					@lang('app.password')
					<span class="text-base">*</span>
				</p>

				<input
					x-model="user.password"
					class="w-full p-0 bg-transparent text-slate-700 h-8 py-1 rounded-none border-b border-purple-300 outline-none invalid:border-b-red-500 invalid:bg-red-50"
					type="password"
					name="password"
					id="user-password"
					@keyup.enter="loginUser()"
				/>
			</div>

			{{-- Buttons --}}
			<div class="grid grid-cols-2 gap-10 mt-10 text-center">
				<div>&nbsp;</div>
				<div>
					@include('partials.button', [
						'way'		=> 'right',
						'text'		=> __('app.do-login'),
						'icon'		=> '<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />',
						'action'	=> 'loginUser'
					])
				</div>
			</div>
		</div>
	</div>
@endsection
