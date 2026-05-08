@php
    $registrationSteps = \Modules\Admission\Helpers\Menu::stepRegistrationStepMenu();
    $activeMenu = $registrationSteps['activeMenu'];
    // TODO: language
@endphp

<aside class="content-right">
    <div class="side-menu">
        <div class="side-menu-header">
            <h4>Langkah Pendaftaran</h4>
            <button type="button" onclick="toggleMenu()" class="side-menu-toggle">
                <span class="icon icon-chevron-down"></span>
            </button>
        </div>
        <ul class="menu" id="menu-sidebar">
            @foreach($registrationSteps['steps'] as $step)
                <li @class(['active' => $step['active'], 'disabled' => $step['disabled']])>
                    @if(!empty($step['visible']))
                        <a href="{{ url('admission/' . $step['path']) }}" {!! $step['add'] ?? null !!}>
                            <span class="step-number">{{ $step['stepNumber'] }}</span>
                            <span class="title">{{ $step['title'] }}</span>
                            @if(!empty($step['checked']))
                                <span class="icon icon-check-circle-solid"></span>
                            @endif
                        </a>
                    @else
                        <a class="link-disabled" href="#" {!! $step['add'] ?? null !!}>
                            <span class="step-number">{{ $step['stepNumber'] }}</span>
                            <span class="title">{{ $step['title'] }}</span>
                            @if(!empty($step['checked']))
                                <span class="icon icon-check-circle-solid"></span>
                            @endif
                        </a>
                    @endif
                </li>
            @endforeach
        </ul>
        <ul class="menu" id="menu-sidebar-active">
            <li class="active">
                <a href="{{ url('admission/' . $activeMenu['path']) }}">
                    <span class="step-number">{{ $activeMenu['stepNumber'] }}</span>
                    <span class="title">{{ $activeMenu['title'] }}</span>
                    @if(!empty($activeMenu['checked']))
                        <span class="icon icon-check-circle-solid"></span>
                    @endif
                </a>
            </li>
        </ul>
    </div>

    @push('scripts')
        <script>
            // using vaniilla javascript
            function toggleMenu() {
                let menu = document.getElementById("menu-sidebar");
                let menuActive = document.getElementById("menu-sidebar-active");
                let menuToggle = document.querySelector('.side-menu-toggle span.icon');

                if (menu.style.display === "block" || menu.style.display == undefined) {
                    menu.style.display = "none";
                    menuActive.style.display = "block";
                    menuToggle.classList.remove('icon-chevron-up');
                    menuToggle.classList.add('icon-chevron-down');
                } else {
                    menu.style.display = "block";
                    menuActive.style.display = "none";
                    menuToggle.classList.remove('icon-chevron-down');
                    menuToggle.classList.add('icon-chevron-up');
                }
            }
        </script>
    @endpush
</aside>
