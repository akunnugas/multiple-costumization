@php($user = Auth::user())
<x-core::layouts.html>
    <main class="main">
        <div class="container">
            <div style="margin:0 auto">
                <div style="padding-bottom:20px">
                    <img height="50" src="{{ asset('images/logo-sevima-platform.png') }}" style="margin:0 auto" />
                </div>
                <h3>{{ $user->module_name }}</h3>
                <div style="display:flex;gap:50px;padding-top:20px">
                    <div>
                        <h4>{{ $user->name }}</h4>
                        <div>{{ $user->email }}</div>
                    </div>
                    <div>
                        <h4>{{ $user->role_name }}</h4>
                        <div>{{ $user->organization_name }}</div>
                    </div>
                </div>
                <div style="padding:20px 0">
                    @dump($user->toArray())
                </div>
                <div style="display:flex;gap:10px;padding-bottom:20px">
                    @foreach (request()->permission as $method => $can)
                        @if ($can)
                            <x-core::badge type="outline">
                                <x-slot:decoration>
                                    <x-core::icon type="check" />
                                </x-slot:decoration>
                                {{ strtoupper($method) }}
                            </x-core::badge>
                        @endif
                    @endforeach
                </div>
                <div style="display:flex;justify-content:center;gap:10px">
                    <x-core::button href="{{ url(\App\Providers\RouteServiceProvider::HOME) }}">
                        Kembali ke Menu
                    </x-core::button>
                    <x-core::form action="{{ url('gate/sessions') }}" method="delete">
                        <x-core::button type="submit" variant="destructive">
                            Logout
                        </x-core::button>
                    </x-core::form>
                </div>
            </div>
        </div>
    </main>
</x-core::layouts.html>
