<div class="cta-navbar-mobile">
    <div class="info">
        <h3>{{ __('admission::home.already_has_account') }} ?</h3>
        <p>{{ __('admission::home.login_to_continue_registration') }}</p>
    </div>
    <x-core::button variant="outline" href="{{ route('admission.login') }}">
        {{ __('admission::home.login') }}
    </x-core::button>
</div>
