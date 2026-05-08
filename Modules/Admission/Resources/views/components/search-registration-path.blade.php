@props([
    'degreeOpt' => [],
    'studyProgramOpt' => [],
    'lectureSystemOpt' => [],
])

<div>
    <form wire:submit="searchRegistrationPath">
        <div class="flex-form-pmb">
            <div class="form-control">
                <x-core::controls.select label="Jenjang" purpose="form" :options="$degreeOpt"
                                         wire:model="selectedDegree" />
{{--                                    <select class="form__select2" name="jenjang" style="width:100%">--}}
{{--                                        <?php foreach ($a_jenjang as $k => $v) : ?>--}}
{{--                                        <option translate="no" value="<?= $k ?>" <?= $k == $r_jenjang ? 'selected' : '' ?>><?= $v ?></option>--}}
{{--                                        <?php endforeach; ?>--}}
{{--                                    </select>--}}
            </div>
            <div class="form-control">
                <x-core::controls.select label="Program Studi" purpose="form" :options="$studyProgramOpt"
                                         wire:model="selectedStudyProgram" />
{{--                                    <select class="form__select2_prodi" name="unit" style="width:100%">--}}
{{--                                        <?php foreach ($a_unit as $k => $v) : ?>--}}
{{--                                        <option value="<?= $k ?>" <?= $k == $r_unit ? 'selected' : '' ?>><?= $v ?></option>--}}
{{--                                        <?php endforeach; ?>--}}
{{--                                    </select>--}}
            </div>
            <div class="form-control">
                <x-core::controls.select label="Sistem Kuliah" purpose="form" :options="$lectureSystemOpt"
                                         wire:model="selectedLectureSystem" />
{{--                                    <select class="form__select2" name="sistem" style="width:100%">--}}
{{--                                        <?php foreach ($a_sistem as $k => $v) : ?>--}}
{{--                                        <option value="<?= $k ?>" <?= $k == $r_sistem ? 'selected' : '' ?>><?= $v ?></option>--}}
{{--                                        <?php endforeach; ?>--}}
{{--                                    </select>--}}
            </div>

            <x-core::button variant="primary" type="submit">
                Cari Jalur Pendaftaran
            </x-core::button>
        </div>
    </form>
</div>
