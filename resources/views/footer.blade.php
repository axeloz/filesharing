<footer class="relative mt-5 h-6 text-xs">
	@if (App\Helpers\Auth::isLogged())
		<span class="ml-3  text-slate-600">
			@lang('app.you-are-logged-in', [
				'username' => App\Helpers\Auth::getLoggedUserDetails()['username']
			])
		</span>
		[<a href="{{ route('logout') }}" class="text-primary hover:underline">@lang('app.logout')</a>]


	@endif

	<div class="absolute right-0 top-0 text-[.6rem] text-slate-100 text-right px-2 py-1 italic bg-primary rounded-tl-lg">
		By <a class="text-white" href="https://strac.com.au" target="_blank">Strac Consulting Engineers</a>
	</div>
</footer>
