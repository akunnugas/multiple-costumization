@php($user = Auth::user())
<x-core::layouts.html>
    <main class="main">
        <div class="container">
            <div style="margin:0 auto">
                <div style="padding-bottom:20px">
                    <img height="50" src="{{ asset('images/logo-sevima-platform.png') }}" style="margin:0 auto" />
                </div>
                <div style="padding-bottom:20px">
                    <h4>{{ $user->nama_user }}</h4>
                    <div>{{ $user->email_user }}</div>
                </div>
                <div style="display:flex;flex-direction:column;gap:30px">
                    @foreach ($modules as $module)
                        <div>
                            <h3 style="padding-bottom:20px">{{ $module['nama_modul'] }}</h3>
                            <div style="display:flex;flex-direction:column;gap:20px">
                                @foreach ($module['role'] as $role)
                                    <div style="display:flex;justify-content:space-between;gap:50px">
                                        <div>
                                            <h4>{{ $role['nama_role'] }}</h4>
                                            <div>{{ $role['nama_unit'] }}</div>
                                        </div>
                                        <x-core::button href="{{ $module['url_home'] }}">
                                            Masuk
                                        </x-core::button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                    <div>
                        <h3 style="padding-bottom:20px">Logout</h3>
                        <div>
                            <x-core::form action="{{ url('gate/sessions') }}" method="delete">
                                <x-core::button type="submit" variant="destructive">
                                    Logout
                                </x-core::button>
                            </x-core::form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</x-core::layouts.html>
