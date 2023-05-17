@extends('layout')

@section('page_title', __('app.upload-files-title'))

@push('scripts')
<script>
	let baseUrl		= @js($baseUrl);
	let metadata	= @js($metadata ?? []);
	let maxFiles	= @js(config('sharing.max_files'));
	let maxFileSize = @js(Upload::fileMaxSize());

	document.addEventListener('alpine:init', () => {
		Alpine.data('upload', () => ({
			bundle: null,
			bundleIndex: null,
			bundles: null,
			dropzone: null,
			uploadedFiles: [],
			metadata: [],
			completed: false,
			step: 0,
			maxFiles: maxFiles,
			modal: {
				show: false,
				text: 'test'
			},
			steps: [
				{
					title: '@lang('app.upload-settings')',
					active: true,
					icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 inline align-text-top text-primary"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12a7.5 7.5 0 0015 0m-15 0a7.5 7.5 0 1115 0m-15 0H3m16.5 0H21m-1.5 0H12m-8.457 3.077l1.41-.513m14.095-5.13l1.41-.513M5.106 17.785l1.15-.964m11.49-9.642l1.149-.964M7.501 19.795l.75-1.3m7.5-12.99l.75-1.3m-6.063 16.658l.26-1.477m2.605-14.772l.26-1.477m0 17.726l-.26-1.477M10.698 4.614l-.26-1.477M16.5 19.794l-.75-1.299M7.5 4.205L12 12m6.894 5.785l-1.149-.964M6.256 7.178l-1.15-.964m15.352 8.864l-1.41-.513M4.954 9.435l-1.41-.514M12.002 12l-3.75 6.495" /></svg>'
				},
				{
					title: '@lang('app.upload-files-title')',
					active: false,
					icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 inline align-text-top text-primary"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 7.5h-.75A2.25 2.25 0 004.5 9.75v7.5a2.25 2.25 0 002.25 2.25h7.5a2.25 2.25 0 002.25-2.25v-7.5a2.25 2.25 0 00-2.25-2.25h-.75m0-3l-3-3m0 0l-3 3m3-3v11.25m6-2.25h.75a2.25 2.25 0 012.25 2.25v7.5a2.25 2.25 0 01-2.25 2.25h-7.5a2.25 2.25 0 01-2.25-2.25v-.75" /></svg>'
				},
				{
					title: '@lang('app.download-links')',
					active: false,
					icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 inline align-text-top text-primary"><path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3l1.5 1.5 3-3.75" /></svg>'
				},
			],

			init: function() {
				this.metadata = metadata

				if (this.getBundle()) {
					// Steps router
					if (this.metadata.completed == true) {
						this.step = 3
					}
					else if (this.metadata.title) {
						this.step = 2
						this.startDropzone()
					}
					else {
						this.step = 1
					}
				}
			},

			getBundle: function() {
				// Getting all bundles store in local storage
				bundles = localStorage.getItem('bundles')

				// If not bundle found, back to homepage
				if (bundles == null || bundles == '') {
					window.location.href = '/'
					return false
				}

				this.bundles = JSON.parse(bundles)

				// Looking for the current bundle
				if (this.bundles != null && Object.keys(this.bundles).length > 0) {
					this.bundles.forEach( (element, index) => {
						if (element.bundle_id == this.metadata.bundle_id) {
							//this.bundle = Object.assign(element)
							//this.bundleIndex = index
							this.bundle = index
						}
					})
				}

				// If current bundle not found, aborting
				if (this.bundle == null) {
					window.location.href = '/'
					return false
				}

				if (this.bundles[this.bundle].owner_token != this.metadata.owner_token) {
					window.location.href = '/'
					return false
				}

				return true
			},

			uploadStep: function() {
				let errors = null
				document.getElementById('upload-title').setCustomValidity('')
				document.getElementById('upload-description').setCustomValidity('')
				document.getElementById('upload-expiry').setCustomValidity('')
				document.getElementById('upload-password').setCustomValidity('')
				document.getElementById('upload-max-downloads').setCustomValidity('')

				if (this.metadata.title == null || this.metadata.title == '') {
					document.getElementById('upload-title').setCustomValidity('Field is required')
					errors = true
				}

				if (this.metadata.expiry == null || this.metadata.expiry == '') {
					document.getElementById('upload-expiry').setCustomValidity('Field is required')
					errors = true
				}

				if (this.metadata.max_downloads < 0 || this.metadata.max_downloads > 999) {
					document.getElementById('upload-max-downloads').setCustomValidity('Invalid number of max downloads')
					errors = true
				}

				if (errors === true) {
					return false
				}

				axios({
					url: '/upload/'+this.metadata.bundle_id,
					method: 'POST',
					data: {
						expiry: this.metadata.expiry,
						title: this.metadata.title,
						description: this.metadata.description,
						max_downloads: this.metadata.max_downloads,
						password: this.metadata.password,
						auth: this.bundles[this.bundle].owner_token
					}
				})
				.then( (response) => {
					this.syncData(response.data)
					window.history.pushState(null, null, baseUrl+'/upload/'+this.metadata.bundle_id);
					this.step = 2

					this.startDropzone()
				})
				.catch( (error) => {
					// TODO: do something here
				})
			},

			completeStep: function() {
				if (Object.keys(this.metadata.files).length == 0) {
					return false;
				}

				this.showModal('{{ __('app.confirm-complete') }}', () => {
					axios({
						url: '/upload/'+this.metadata.bundle_id+'/complete',
						method: 'POST',
						data: {
							auth: this.bundles[this.bundle].owner_token
						}

					})
					.then( (response) => {
						this.step = 3
						this.syncData(response.data)
					})
					.catch( (error) => {
						// TODO: do something here
					})
				})
			},

			back: function() {
				if (this.step > 1) {
					this.step --;
				}
			},

			startDropzone: function() {
				if (! this.dropzone) {
					this.maxFiles = this.maxFiles - this.countFilesOnServer() >= 0 ? this.maxFiles - this.countFilesOnServer() : 0

					this.dropzone = new Dropzone('#upload-frm', {
						url: '/upload/'+this.metadata.bundle_id+'/file',
						method: 'POST',
						headers: {
							'X-Upload-Auth': this.bundles[this.bundle].owner_token
						},
						createImageThumbnails: false,
						disablePreviews: true,
						clickable: true,
						paramName: 'file',
						maxFiles: maxFiles,
						maxFilesize: (maxFileSize / 1000000),
						parallelUploads: 1, // TODO : increase this limit but must fix bug first when creating folders
						dictMaxFilesExceeded: '@lang('app.files-count-limit')',
						dictFileTooBig: '@lang('app.file-too-big')',
						dictDefaultMessage: '@lang('app.dropzone-text')',
						dictResponseError: '@lang('app.server-answered')',
					})

					this.dropzone.on('addedfile', (file) => {
						console.log('added file')

						file.uuid = this.uuid()

						this.metadata.files.push({
							uuid: file.uuid,
							original: file.name,
							filesize: file.size,
							fullpath: '',
							filename: file.name,
							created_at: moment().unix(),
							status: 'uploading'
						});
					})

					this.dropzone.on('sending', (file, xhr, data) => {
						data.append('uuid', file.uuid)
					})

					this.dropzone.on('uploadprogress', (file, progress, bytes) => {
						let fileIndex = null

						if (fileIndex = this.findFileIndex(file.upload.uuid)) {
							this.metadata.files[fileIndex].progress = Math.round(progress)
						}
					})

					this.dropzone.on('error', (file, message) => {
						let fileIndex = this.findFileIndex(file.upload.uuid)
						this.metadata.files[fileIndex].status = false
						this.metadata.files[fileIndex].message = message
					})

					this.dropzone.on('complete', (file) => {
						let fileIndex = this.findFileIndex(file.uuid)
						this.metadata.files[fileIndex].progress = 0

						if (file.status == 'success') {
							this.maxFiles--
							this.metadata.files[fileIndex].status = true
						}
					})
				}
			},

			deleteFile: function(file) {
				// File status is valid so it must be deleted from server
				if (file.status == true) {
					this.showModal('{{ __('app.confirm-delete') }}', () => {
						let lfile = file

						axios({
							url: '/upload/'+this.metadata.bundle_id+'/file',
							method: 'DELETE',
							data: {
								uuid: lfile.uuid,
								auth: this.bundles[this.bundle].owner_token
							}
						})
						.then( (response) => {
							this.syncData(response.data)
						})
						.catch( (error) => {
							// TODO: do something here
						})
					})
				}
				// File not valid, no need to remove it from server, just locally
				else if (file.status == false) {
					let fileIndex = this.findFileIndex(file.uuid)
					this.metadata.files.splice(fileIndex, 1)
				}
				// File has not being uploaded, cannot delete file yet
				else {
					// Nothing here
				}
			},

			deleteBundle: function() {
				this.showModal('{{ __('app.confirm-delete-bundle') }}', () => {
					axios({
						url: '/upload/'+this.metadata.bundle_id+'/delete',
						method: 'DELETE',
						data: {
							auth: this.bundles[this.bundle].owner_token
						}
					})
					.then( (response) => {
						if (! response.data.success) {
							this.syncData(response.data)
						}
					})
				})
			},

			findFile: function(uuid) {
				let index = this.findFileIndex(uuid)
				if (index != null) {
					return this.metadata.files[index]
				}
				return null
			},

			findFileIndex: function (uuid) {
				for (i in this.metadata.files) {
					if (this.metadata.files[i].uuid == uuid) {
						return i
					}
				}
				return null
			},

			syncData: function(metadata) {
				if (Object.keys(metadata).length > 0) {
					this.metadata = metadata
					this.bundles[this.bundle] = metadata
					localStorage.setItem('bundles', JSON.stringify(this.bundles))
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

			uuid: function() {
				return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
					var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
					return v.toString(16);
				});
			},

			showModal: function(text, callback) {
				this.modal.text = text
				this.modal.callback = callback
				this.modal.show = true
			},

			confirmModal: function() {
				this.modal.show = false
				if (this.modal.callback) {
					this.modal.callback()
				}
			},

			selectCopy: function(el) {
				el.select();

				if (navigator.clipboard) {
					navigator.clipboard.writeText(el.value)
					.then(() => {
						alert("Copied to clipboard");
					});
				}
			},

			countFilesOnServer: function() {
				count = 0

				if (this.metadata.hasOwnProperty('files') && Object.keys(this.metadata.files).length > 0) {
					for (i in this.metadata.files) {
						if (this.metadata.files[i].status == true) {
							count ++
						}
					}
				}
				return count
			},

			isBundleExpired: function() {
				if (this.metadata.expires_at == null || this.metadata.expires_at == '') {
					return false;
				}

				return moment.unix(this.metadata.expires_at).isBefore(moment())
			}
		}))
	})
</script>
@endpush

@section('content')
	<div x-data="upload">
		<div class="relative bg-white border border-primary rounded-lg overflow-hidden">
			<div class="bg-gradient-to-r from-primary-light to-primary px-2 py-4 mb-3 text-center">
				<h1 class="relative font-title font-medium font-body text-4xl text-center text-white uppercase flex items-center">
					<div class="w-1/12 text-center">
						{{-- If bundle is locked --}}
						<p title="{{ __('app.bundle-locked') }}" x-show="metadata.completed">
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="inline w-8 h-8 text-purple-200">
							  	<path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
							</svg>
						</p>
					</div>

					{{-- App's title --}}
					<div class="grow text-center">{{ config('app.name') }}</div>


					{{-- Bundle status --}}
					<div class="w-1/12 gap-2 item-right">
						{{-- If bundle is expired --}}
						<p title="{{ __('app.bundle-expired') }}" x-show="isBundleExpired()">
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="inline w-8 h-8 text-purple-200">
								<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
							</svg>
						</p>

					</div>

				</h1>
			</div>

			{{-- Modal box --}}
			<template x-if="modal.show">
				<div class="absolute z-40 top-0 left-0 right-0 bottom-0 w-full bg-[#848A97EE]">
					<div class="absolute z-50 top-[50%] left-[50%] translate-x-[-50%] translate-y-[-50%] rounded-lg bg-white w-1/2 p-6 text-center shadow-lg border-2 border-gray-300">
						<div class="w-full text-center">
							<p class="relative mx-auto bg-orange-200 rounded-full w-14 h-14">
								<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="absolute top-[50%] left-[50%] translate-x-[-50%] translate-y-[-50%] w-8 h-8 inline text-orange-600">
					  			<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
								</svg>
							</p>
						</div>
						<p class="mt-4 font-title font-medium text-primary text-lg">{{ __('app.confirmation') }}</p>
						<div class="mb-6 text-gray-500" x-text="modal.text"></div>
						<div class="flex">
							<div class="w-1/2 text-right px-1">
								<button class="bg-gray-300 text-black rounded px-3 py-1" x-on:click="modal.show = false">{{ __('app.cancel') }}</button>
							</div>
							<div class="w-1/2 text-left px-1"><button class="bg-primary text-white rounded px-3 py-1" x-on:click="confirmModal()">{{ __('app.confirm') }}</button></div>
						</div>
					</div>
				</div>
			</template>

			<div class="p-5">
				{{-- Steps --}}
				<div class="rounded-t-md grid grid-cols-3 gap-2 leading-3 mb-10">
					<template x-for="(s, i) in steps">
						<div class="p-2">
							<div class="rounded mb-4 w-full h-1" :class="(i + 1) <= step ? 'bg-primary' : 'bg-gray-200'"></div>

							<div class="flex items-center">
								<div x-html="s.icon"></div>
								<div>
									<p class="leading-[.9rem] font-title px-1 uppercase text-sm text-primary" x-text="'{{ __('app.step') }} '+ (i + 1)"></p>
									<h2 class="leading-[.9rem] px-1 text-xs font-medium"  x-text="s.title"></h2>
								</div>
							</div>
						</div>
					</template>
				</div>

				<div class="mt-10">

					{{-- STEP 1 --}}
					<div x-cloak x-show="step == 1">
						<h2 class="font-title text-2xl mb-5 text-primary font-medium uppercase">
							@lang('app.upload-settings')
						</h2>

						{{-- Title --}}
						<div class="">
							<p class="font-title uppercase">
								@lang('app.upload-title')
								<span class="text-base">*</span>
							</p>

							<input
								x-model="metadata.title"
								class="w-full p-0 bg-transparent text-slate-700 h-8 py-1 rounded-none border-b border-purple-300 outline-none invalid:border-b-red-500 invalid:bg-red-50"
								type="text"
								name="title"
								id="upload-title"
								maxlength="70"
							/>
						</div>

						{{-- Description --}}
						<div class="mt-5">
							<span class="font-title uppercase">@lang('app.upload-description')</span>

							<textarea
								x-model="metadata.description"
								maxlength="300"
								class="w-full p-0 bg-transparent text-slate-700 h-18 py-1 rounded-none border-b border-purple-300 outline-none  invalid:border-b-red-500 invalid:bg-red-50"
								type="text"
								name="description"
								id="upload-description"
							/></textarea>
						</div>

						{{-- Expiration --}}
						<div class="flex flex-wrap items-center mt-5">
							<div class="w-1/3 pr-2">
								<p class="font-title uppercase">
									@lang('app.upload-expiry')
									<span class="text-base">*</span>
								</p>

								<select
									x-model="metadata.expiry""
									class="w-full text-slate-700 bg-transparent h-8 p-0 py-1 border-b border-primary-superlight focus:ring-0 invalid:border-b-red-500 invalid:bg-red-50"
									name="expiry"
									id="upload-expiry"
								>
									<option value="0"></option>
									@foreach (config('sharing.expiry_values') as $k => $e)
										<option value="{{ Upload::getExpirySeconds($k) }}" {{ $e == config('sharing.default_expiry') ? 'selected' : '' }}>@lang('app.'.$e)</option>
									@endforeach
								</select>
							</div>

							{{-- Max downloads --}}
							<div class="w-1/3 pr-2">
								<p class="font-title uppercase">
									@lang('app.max-downloads')
								</p>

								<input
									x-model="metadata.max_downloads"
									class="w-full p-0 bg-transparent text-slate-700 h-8 py-1 rounded-none border-b border-purple-300 outline-none invalid:border-b-red-500 invalid:bg-red-50"
									type="number"
									name="max_downloads"
									id="upload-max-downloads"
									min="0"
									max="999"
								/>
							</div>

							{{-- Password --}}
							<div class="w-1/3">
								<span class="font-title uppercase">@lang('app.bundle-password')</span>

								<input
									x-model="metadata.password"
									class="w-full bg-transparent text-slate-700 h-8 p-0 py-1 rounded-none border-b border-primary-superlight outline-none invalid:border-b-red-500 invalid:bg-red-50"
									placeholder="@lang('app.leave-empty')"
									type="text"
									name="password"
									id="upload-password"
								/>
							</div>
						</div>

						{{-- Buttons --}}
						<div class="grid grid-cols-2 gap-10 mt-10 text-center">
							<div>&nbsp;</div>
							<div>
								@include('partials.button', [
									'way'		=> 'right',
									'text'		=> __('app.start-uploading'),
									'icon'		=> '<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />',
									'action'	=> 'uploadStep'
								])
							</div>
						</div>
					</div>

					{{-- STEP 2 --}}
					<div x-cloak class="" x-show="step == 2">
						<h2 class="font-title text-2xl mb-5 text-primary font-medium uppercase">
							@lang('app.upload-files-title')
						</h2>

						<div class="grid grid-cols-4 gap-2">
							{{-- Dropzone --}}
							<div>
								<form class="relative dropzone border-primary border" id="upload-frm" enctype="multipart/form-data">
									<div class="absolute right-2 bottom-1 text-[.6rem] text-slate-800 italic">
										@lang('app.maximum-filesize')
										{{ Upload::fileMaxSize(true) }}
									</div>
								</form>
							</div>

							<div class="col-span-3 ml-2">
								<h3 class="font-title flex items-center text-base mb-2 text-primary font-medium uppercase">
									<p>@lang('app.files-list')</p>
									<div class="ml-3 flex text-xs bg-primary rounded-full px-1 text-center text-white divide-x">
										<p
											class="px-2 text-center"
											x-text="countFilesOnServer()"
											:title="'{{ __('app.files-count-on-server') }}'"
										></p>
										<p
											class="px-2 text-center"
											x-text="maxFiles"
											:title="'{{ __('app.files-remaining-files') }}'"
										></p>
									</div>
								</h3>

								<span class="text-xs text-slate-400" x-show="countFilesOnServer() == 0">@lang('app.no-file')</span>

								{{-- Files list --}}
								<ul id="output" class="text-xs max-h-32 overflow-y-scroll pb-3" x-show="countFilesOnServer() > 0">
									<template x-for="(f, k) in metadata.files" :key="k">
										<li
											title="{{ __('app.click-to-remove') }}"
											class="relative flex items-center leading-5 list-inside even:bg-gray-50 rounded px-2 cursor-pointer overflow-hidden"
											x-on:click="deleteFile(f)"
										>
											{{-- Progress bar --}}
											<p class="absolute top-0 left-0 bottom-0 bg-[#9333EA66] w-0" :style="'width: '+f.progress+'%;'">&nbsp;</p>

											{{-- Status icon --}}
											<p class="w-[5%]">
												<template x-if="f.status == true">
													<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="inline w-4 h-4 text-green-600">
  													<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
													</svg>
												</template>
												<template x-if="f.status == false">
													<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="inline w-4 h-4 text-red-600">
													<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
													</svg>
												</template>
												<template x-if="f.status == 'uploading'">
													<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="inline w-4 h-4 text-orange-600">
													<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
													</svg>
												</template>
											</p>

											{{-- File name --}}
											<p class="w-[80%] overflow-hidden whitespace-nowrap relative">
												<span x-text="f.original"></span>
												<template x-if="f.message">
													<span class="w-full px-1 rounded bg-red-100 absolute opacity-0 hover:opacity-95 transition-all duration-300 top-0 left-0" x-html="f.message"></span>
												</template>
											</p>

											{{-- File size --}}
											<p class="w-[15%] text-right" float-right text-xs" x-text="humanSize(f.filesize)"></p>
										</li>
									</template>
								</ul>
							</div>
						</div>

						{{-- Buttons --}}
						<div class="grid grid-cols-2 gap-10 mt-10 text-center">
							<div>
								@include('partials.button', [
									'way'		=> 'left',
									'text'		=> __('app.back'),
									'icon'		=> '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />',
									'action'	=> 'back'
								])
							</div>
							<div>
								@include('partials.button', [
									'way'		=> 'right',
									'text'		=> __('app.complete-upload'),
									'icon'		=> '<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />',
									'action'	=> 'completeStep'
								])
							</div>
						</div>
					</div>

					{{-- STEP 3 --}}
					<template x-if="step == 3">
						<div class="" x-show="step == 3">
							<h2 class="font-title text-2xl mb-5 text-primary font-medium uppercase">
								@lang('app.download-links')
							</h2>

							{{-- Preview link --}}
							<div class="flex flex-wrap items-center">
								<div class="w-1/3 text-right px-2">
									@lang('app.preview-link')
								</div>
								<div class="w-2/3 shadow">
									<input x-model="metadata.preview_link" class="w-full bg-transparent text-slate-700 h-8 px-2 py-1 rounded-none border border-primary-superlight outline-none" type="text" readonly x-on:click="selectCopy($el)" />
								</div>
							</div>

							{{-- Direct download link --}}
							<div class="flex flex-wrap items-center mt-5">
								<div class="w-1/3 text-right px-2">
									@lang('app.direct-link')
								</div>
								<div class="w-2/3 shadow">
									<input x-model="metadata.download_link" class="w-full bg-transparent text-slate-700 h-8 px-2 py-1 rounded-none border border-primary-superlight outline-none" type="text" readonly x-on:click="selectCopy($el)" />
								</div>
							</div>

							{{-- Buttons --}}
							<div class="grid grid-cols-2 gap-10 mt-10 text-center">
								<div>
									&nbsp;
								</div>
								<div>
									<template x-if="! isBundleExpired()">
										@include('partials.button', [
											'way'		=> 'right',
											'text'		=> __('app.delete-bundle'),
											'icon'		=> '<path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />',
											'action'	=> 'deleteBundle'
										])
									</template>
									<template x-if="isBundleExpired()">
										<p class="text-xs">
											@lang('app.bundle-expired')
										</p>
									</template>
								</div>
							</div>
						</div>
					</template>
				</div>
			</div>
		</div>
	</div>
@endsection
