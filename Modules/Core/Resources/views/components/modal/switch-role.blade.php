@props([
    'roles' => [],
    'selectedRole' => null,
    'selectedOrganization' => null,
    'action' => null,
    'module' => null,
])

@php
    $attributes = $attributes->merge([
        'title' => 'Ganti Role',
        'formMethod' => 'POST',
        'width' => 'width: 400px',
    ]);
@endphp
<x-core::form id="form-switch" :action="$action">
    <x-core::modal {{ $attributes }}>
        <x-core::modal.body>
            @foreach ($roles as $key => $value)
                @php
                    $isMatchSelectedRole = $value['kode_role'] === $selectedRole ? true : false;
                    $isMatchSelectedOrganization = $value['id_unit'] === $selectedOrganization ? true : false;

                    $isSelected = $isMatchSelectedRole && $isMatchSelectedOrganization;
                @endphp
                <div class="box-switch{{ $isSelected ? ' selected' : '' }}" data-module="{{ $module }}"
                    data-name="{{ $value['kode_role'] }}" data-organization="{{ $value['id_unit'] }}"
                    onclick="switchRole(this)">
                    <h3>
                        {{ $value['nama_role'] }}
                        <br>
                        <span style="font-weight: 400;font-size:0.875rem">{{ $value['nama_unit'] }}</span>
                    </h3>
                    @if ($isSelected)
                        <div>
                            <i class="icon icon-check-circle-solid" style="color:#5d88f6"></i>
                        </div>
                    @endif
                </div>
            @endforeach
        </x-core::modal.body>
    </x-core::modal>
</x-core::form>


@pushOnce('scripts')
    <script>
        // click function to show modal
        document.getElementById("switch-role").addEventListener("click", function() {
            const modal = document.getElementById("modal-switch-role");
            modal.classList.add("is-visible");
        });

        function switchRole(el) {
            if (el.classList.contains("selected")) return;
            const organization = el.getAttribute("data-organization");
            const role = el.getAttribute("data-name");
            const module = el.getAttribute("data-module");
            const form = document.getElementById("form-switch");
            // create 2 input element
            const inputRole = document.createElement("input");
            inputRole.setAttribute("type", "hidden");
            inputRole.setAttribute("name", "role");
            inputRole.setAttribute("value", role);
            form.appendChild(inputRole);

            // organization
            const inputOrganization = document.createElement("input");
            inputOrganization.setAttribute("type", "hidden");
            inputOrganization.setAttribute("name", "organization");
            inputOrganization.setAttribute("value", organization);
            form.appendChild(inputOrganization);

            const inputModule = document.createElement("input");
            inputModule.setAttribute("type", "hidden");
            inputModule.setAttribute("name", "module");
            inputModule.setAttribute("value", module);
            form.appendChild(inputModule);

            document.body.appendChild(form);
            form.submit();
        }
    </script>
@endPushOnce
