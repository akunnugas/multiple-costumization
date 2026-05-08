<div class="modal @if ($showModal) is-visible @endif" id="upload-penilaian-ikt">
    <div class="modal__overlay" wire:click="closeModal"></div>

    <div class="modal__wrapper">
        <div class="modal__header">
            <div class="modal__header-wrapper">
                <h3 class="modal__title">Import Data Matriks Penilaian</h3>
            </div>
            <x-core::button variant="outline" id="btn_download-template" leading-icon="arrow-down-circle" size="sm"
                wire:click="downloadTemplate" :disabled="!$panduanId">
                <span class="btn__text">Unduh Template</span>
            </x-core::button>
            <span class="icon icon-x-mark-mini" wire:click="closeModal"></span>
        </div>

        <form enctype="multipart/form-data" wire:submit.prevent="submit">
            @csrf
            <div class="modal__body">
                <div class="modal__content-upload">
                    <div class="form-control">
                        <label for="panduanSelect" class="form-control__label">Panduan Penilaian <span
                                class="important">*</span></label>
                        <x-core::controls.select :options="$panduan" id="panduanSelect" required
                            value="{{ $panduanId }}" wire:change="$set('panduanId', $event.target.value)" />
                        <div class="form-control__helper">
                            <div>
                                Pilih Panduan Penilaian terlebih dahulu sebelum mengunduh dan mengunggah template.
                            </div>
                        </div>
                    </div>
                    <div class="form-control">
                        <label class="form-control__label">Unit Kerja</label>
                        <x-core::controls.select-multiple :options="$unitKerja" :values="$unitKerjaIds"
                            x-on:change="
                        Livewire.dispatch('unitKerjaIdsChanged', {
                            detail: [...$event.target.selectedOptions].map(o => o.value)
                        });
                        " />
                    </div>
                    <div class="form-control">
                        <label class="form-control__label">Periode</label>
                        <x-core::controls.select-multiple :options="$periods" :values="$selectedPeriods"
                            x-on:change="
                        Livewire.dispatch('periodsChanged', {
                            detail: [...$event.target.selectedOptions].map(o => o.value)
                        });
                        " />
                    </div>
                    <input type="hidden" wire:model="fileBase64" id="fileBase64">
                    <div class="form-control" wire:ignore wire:key="{{ $this->uploadId }}">
                        <div class="upload-draggable">
                            <div class="upload-draggable__box">
                                <input type="file" id="fileInput" class="upload-draggable__file-input" name="file"
                                    accept=".xls,.xlsx" required onchange="toBase64(this)">
                                <label class="upload-draggable__icon"><span
                                        class="icon icon-cloud-arrow-up"></span></label>
                                <h2 class="upload-draggable__title">Klik untuk pilih file</h2>
                                <p class="upload-draggable__subtitle">atau seret file ke sini</p>
                                <p class="upload-draggable__support">Format: .xls, .xlsx (Max. 10MB)</p>
                                <script>
                                    function toBase64(input) {
                                        const file = input.files[0];
                                        if (!file) return;
                                        const reader = new FileReader();
                                        reader.onload = function(e) {
                                            // Kirim ke Livewire property
                                            window.Livewire.dispatch('fileBase64Changed', {
                                                detail: e.target.result
                                            });
                                        };
                                        reader.readAsDataURL(file);
                                    }
                                </script>
                            </div>
                            <div class="upload-draggable__success">
                                Berhasil
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal__footer">
                <div class="grid cols-1 cols-sm-2">
                    <button class="btn btn_outline" type="button" data-dismiss="modal" id="batalBtn"
                        wire:click="clearForm">
                        Batal
                    </button>
                    <button class="btn btn_primary" type="submit" id="save-dokumen">Konfirmasi</button>
                </div>
            </div>
        </form>
    </div>
</div>
