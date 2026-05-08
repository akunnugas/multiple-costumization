@props([
    'title' => null,
    'isRemoveProdi' => null,
    'availablePersonBySKOptions' => [],
    'availablePersonOptions' => [],
    'studyPrograms' => [],
    'positionOptions' => [],
    'selectedPersonOptions' => [],
    'selectedStudyProgram' => null,
    'addedFormPerson' => [],
    'withBackdrop' => false,
    'isCreate' => true,
    'isHasErrorStudyProgram' => false,
    'selectedAuditee' => null,
    'positionAuditee' => null,
    'positionMemberAuditee' => null,
])

<div class="backdrop-form-person"></div>

<div class="grid add-person-section" @style($attributes['style'])>
    @php
        // Jika rerender karena error study program, maka tambahkan error ke message bag.
        if ($isHasErrorStudyProgram) {
            $message = new \Illuminate\Support\MessageBag([
                'study-program' => 'Program studi ini sudah digunakan sebelumnya. Silakan pilih program studi lain.',
            ]);

            $errors?->put('default', $message);
        }

        $positionOptionsAuditee = array_filter(
            $positionOptions,
            function ($key) use ($positionAuditee, $positionMemberAuditee) {
                return $key == $positionAuditee || $key == $positionMemberAuditee;
            },
            ARRAY_FILTER_USE_KEY,
        );

        $positionOptionsAuditor = array_filter(
            $positionOptions,
            function ($key) use ($positionAuditee, $positionMemberAuditee) {
                return $key !== $positionAuditee && $key !== $positionMemberAuditee;
            },
            ARRAY_FILTER_USE_KEY,
        );
    @endphp
    <div class="col-12 grid">
        @if (isset($title))
            <div class="col-12">
                <h3>{{ $title }}</h3>
            </div>
            <hr class="col-12 " />
        @endif
        <div class="col-12">
            <x-core::controls.form name="study-program" purpose="form" id="study-program-select" :options="$studyPrograms"
                control="select" variant="search" wire:model.change="selectedStudyProgram" :selected="$selectedStudyProgram"
                :value="$selectedStudyProgram" :disabled="!$isRemoveProdi" />
        </div>
        {{-- <div class="col-12">
            <x-core::controls.form name="auditee" purpose="form" id="auditee-select" :options="$auditeePersonOptions" control="select"
                variant="search" wire:model.change="selectedAuditee" :selected="$selectedAuditee" :value="$selectedAuditee" />
        </div> --}}
        <div class="col-12">
            <div class="select-auditee-section">
                <div class="section__header">
                    <h3>Nama Auditee</h3>
                    <x-core::button variant="ghost" size="xs" wire:click="addNewPersonForm(1)">
                        Tambah Auditee
                    </x-core::button>
                </div>
                <div class="section__body">
                    @foreach ($addedFormPerson as $i => $item)
                        @php
                            if ($item['position'] !== $positionAuditee && $item['position'] !== $positionMemberAuditee){
                                continue;
                            }
                            $selectedPersonId = $this->selectValue($item['person_id']);
                            $selectedPosition = $this->selectValue($item['position']);
                            
                            $auditeePersonOptions = $availablePersonOptions;

                            if (!empty($selectedPersonId)) {
                                $filterSelected = $selectedPersonOptions[$selectedPersonId] ?? null;
    
                                if (isset($filterSelected)) {
                                    $auditeePersonOptions[$selectedPersonId] = $filterSelected;
                                }
                            }
                            $isError = $errors?->has("person-$i");
                            if ($isError) {
                                $helper = $errors->first("person-$i");
                            }
                        @endphp
    
                        <div>
                            <div class="section__person-card">
                                <div class="person-card__form">
                                    <x-core::controls.select label="Auditee" name="person-{{ $i }}"
                                        purpose="form" id="employee-select-{{ $i }}" :options="$auditeePersonOptions"
                                        variant="search" wire:model="addedFormPerson.{{ $i }}.person_id"
                                        wire:change="onChangedPersonId({{ $i }}, {{ $selectedPersonId }})"
                                        selected="{{ $selectedPersonId }}" value="{{ $selectedPersonId }}" />
                                </div>
    
                                <div class="person-card__form" style="width: 30%">
                                    <x-core::controls.select label="Posisi" name="position" purpose="form"
                                        id="employee-select-{{ $i }}" :options="$positionOptionsAuditee" variant="search"
                                        wire:model="addedFormPerson.{{ $i }}.position"
                                        wire:change="onChangedPosition({{ $i }})"
                                        selected="{{ $selectedPosition }}" value="{{ $selectedPosition }}" />
                                </div>
    
                                <div class="person-card__delete">
                                    <x-core::button leading-icon="trash" variant="ghost"
                                        wire:click="deletePersonForm({{ $i }})" />
                                </div>
                            </div>
                            @if ($isError)
                                <div @class(['form-control__helper', 'col-12', 'error' => $isError]) style="padding-top:12px">
                                    {{ $helper ?? 'Terjadi kesalahan' }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="select-auditor-section">
                <div class="section__header">
                    <h3>Nama Auditor</h3>
                    <x-core::button variant="ghost" size="xs" wire:click="addNewPersonForm">
                        Tambah Auditor
                    </x-core::button>
                </div>
                <div class="section__body">
                    @foreach ($addedFormPerson as $i => $item)
                        @php
                            if ($item['position'] === $positionAuditee || $item['position'] === $positionMemberAuditee){
                                continue;
                            }
                            $selectedPersonId = $this->selectValue($item['person_id']);
                            $selectedPosition = $this->selectValue($item['position']);
                            $personOptions = [];
                            $allowedIds = array_column($availablePersonBySKOptions, 'id');
    
                            foreach ($availablePersonOptions as $key => $value) {
                                if (in_array($key, $allowedIds)) {
                                    $personOptions[$key] = $value;
                                }
                            }
    
                            if (!empty($selectedPersonId)) {
                                $filterSelected = $selectedPersonOptions[$selectedPersonId] ?? null;
    
                                if (isset($filterSelected)) {
                                    $personOptions[$selectedPersonId] = $filterSelected;
                                }
                            }
    
                            $isError = $errors?->has("person-$i");
                            if ($isError) {
                                $helper = $errors->first("person-$i");
                            }
    
                            
                        @endphp
    
                        <div>
                            <div class="section__person-card">
                                <div class="person-card__form">
                                    <x-core::controls.select label="Auditor" name="person-{{ $i }}"
                                        purpose="form" id="employee-select-{{ $i }}" :options="$personOptions"
                                        variant="search" wire:model="addedFormPerson.{{ $i }}.person_id"
                                        wire:change="onChangedPersonId({{ $i }}, {{ $selectedPersonId }})"
                                        selected="{{ $selectedPersonId }}" value="{{ $selectedPersonId }}" />
                                </div>
    
                                <div class="person-card__form" style="width: 30%">
                                    <x-core::controls.select label="Posisi" name="position" purpose="form"
                                        id="employee-select-{{ $i }}" :options="$positionOptionsAuditor" variant="search"
                                        wire:model="addedFormPerson.{{ $i }}.position"
                                        wire:change="onChangedPosition({{ $i }}, {{ $selectedPosition }})"
                                        selected="{{ $selectedPosition }}" value="{{ $selectedPosition }}" />
                                </div>
    
                                <div class="person-card__delete">
                                    <x-core::button leading-icon="trash" variant="ghost"
                                        wire:click="deletePersonForm({{ $i }})" />
                                </div>
                            </div>
                            @if ($isError)
                                <div @class(['form-control__helper', 'col-12', 'error' => $isError]) style="padding-top:12px">
                                    {{ $helper ?? 'Terjadi kesalahan' }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="util_d-flex util_w-100">
            <x-core::button variant="ghost" size="xs" class="cancel-person-form" data-toggle="modal"
                data-target="#modal-confirmation">
                Batalkan
            </x-core::button>
            <x-core::button variant="primary" size="xs" wire:click="savePersonData" :disabled="$isHasErrorStudyProgram">
                {{ $isCreate ? 'Tambahkan' : 'Ubah' }}
            </x-core::button>
        </div>
    </div>
</div>
