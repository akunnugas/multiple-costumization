<div>
    {{-- Breadcrumb --}}
    <x-admission::breadcrumb :$title :$parentNav />

    <section id="auth-page">
        <div class="container">
            <div class="grid">
                <div class="col-12 col-sm-12 col-md-8">
                    <div class="card">
                        <div class="card__header">
                            <h1 class="title">{{ $title }}</h1>
                            <p class="description">{{ $description }}.</p>
                        </div>
                        <div class="line-bold"></div>
                        <div class="card__body">
                            <div class="content-auth">
                                @if (!empty($alert))
                                    <x-core::layouts.html.alert :data="$alert" style="margin-bottom:1rem" />
                                @endif

                                <div class="grid cols-1">
                                    <form id="form-login" method="post" action="{{ route($routeActionForm) }}">
                                        @csrf
                                        @method('POST')
                                        @foreach ($formFields as $item)
                                            @php
                                                $item['name'] ??= $item['field'];
                                                unset($item['field']);

                                                $attributes = \Modules\Core\Helpers\Page::buildAttributes($item);
                                            @endphp
                                            <x-core::controls.form {{ $attributes }} />
                                        @endforeach
                                        <div class="action">
                                            <a wire:click="updateActivePage('{{ $changeActivePage }}')" href="#" class="link">
                                                {{ $copyBackPage }}
                                            </a>
                                            <x-core::button type="submit">
                                                {{ $copySubmitButton }}
                                            </x-core::button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="line-bold"></div>

                <x-admission::auth.sign-up />
            </div>
        </div>
    </section>
</div>
