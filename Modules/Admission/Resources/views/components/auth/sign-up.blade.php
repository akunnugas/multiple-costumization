<div class="col-12 col-sm-12 col-md-12 col-lg-4">
    <div class="card">
        <div class="card__body with-pattern">
            <div class="right-content">
                <div class="title">{{ __('admission::auth.no_account_title') }}?</div>
                <div class="description">{{ __('admission::auth.no_account_description') }}.</div>
                <x-core::button href="{{ route('admission.registration-path') }}" variant="outline">
                    {{ __('admission::auth.sign_up_now') }}
                </x-core::button>
            </div>
        </div>
    </div>
</div>
