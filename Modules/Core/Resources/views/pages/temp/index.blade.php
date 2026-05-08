<x-core::layouts.html>
    <main class="main">
        <div class="container">
            <div style="margin:0 auto">
                <div style="padding-bottom:20px">
                    <img height="50" src="{{ asset('images/logo-sevima-platform.png') }}" />
                </div>
                <div style="display:flex;flex-direction:column;gap:30px">
                    <div>
                        <h3 style="padding-bottom:20px">Login dengan SSO</h3>
                        <div style="display:flex;gap:10px">
                            <x-core::button href="{!! $ssoURL !!}">
                                Login
                            </x-core::button>
                            <x-core::form action="{{ url('temp/migrate') }}">
                                <x-core::button type="submit" variant="destructive">
                                    Migrasi User
                                </x-core::button>
                            </x-core::form>
                        </div>
                    </div>
                    @if ($admin)
                        <div>
                            <h3 style="padding-bottom:20px">Login ke {{ $admin['name'] }}</h3>
                            <div style="display:flex;justify-content:space-between">
                                <div>
                                    <h4>{{ $admin['user']['nama_user'] }}</h4>
                                    <div>{{ $admin['user']['role']['nama_role'] }}</div>
                                </div>
                                <x-core::button href="{{ $admin['url'] }}">
                                    Login
                                </x-core::button>
                            </div>
                        </div>
                    @endif
                    @if ($sample)
                        <div>
                            <h3 style="padding-bottom:20px">Login ke {{ $sample['name'] }}</h3>
                            <div style="display:flex;flex-direction:column;gap:20px">
                                @foreach ($sample['users'] as $user)
                                    @php
                                        if (!empty($user['id'])) {
                                            continue;
                                        }
                                    @endphp
                                    <div style="display:flex;justify-content:space-between;gap:50px">
                                        <div>
                                            <h4>{{ $user['name'] }}</h4>
                                            <div>{{ $user['role_name'] }}</div>
                                        </div>
                                        <x-core::button href="{{ $user['url'] }}">
                                            Login
                                        </x-core::button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>
</x-core::layouts.html>
