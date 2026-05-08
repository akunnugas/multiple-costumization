@php
    use Modules\Core\Helpers\Format;
    use Modules\DMS\Services\DokumenManagementService;

    $maxSizeByte = Format::formatKbToBytes($attributes['max_size'] ?? 1024);
    $maxSizeKb = $attributes['max_size'];
    $maxSizeLabel = Format::formatBytes($maxSizeByte);

    if (!empty($attributes['file_type'])) {
        $accept = '.' . implode(',.', $attributes['file_type']);
        $acceptJsValidate = implode('|', $attributes['file_type']);

        $acceptDesc = '';
        foreach ($attributes['file_type'] as $key => $value) {
            if ($key > 0 && count($attributes['file_type']) - 1 == $key) {
                $acceptDesc .= 'atau ';
                $acceptDesc .= strtoupper($value);
            } else {
                $acceptDesc .= strtoupper($value) . (count($attributes['file_type']) - 2 == $key || count($attributes['file_type']) == 1 ? ' ' : ', ');
            }
        }
    }

    // multiple
    if (!empty($attributes['multiple'])) {
        $attributes['name'] = $attributes['name'] . '[]';
    }

    if ($attributes['value'] && empty($attributes['multiple'])) {
        $temporaryUrl = '#';
        if (is_int($attributes['value'])) {
            $document = (new DokumenManagementService())->show($attributes['value']);
            $temporaryUrl = $document->lastVersionTemporaryUrl();
        }
        $attributes['required'] = false;
        $attributes['value'] = null;
    }

    // image placement
    $allowedPlacement = ['top', 'bottom'];
    $imagePlacement = $attributes['image_placement'] ?? 'bottom';
    if (!in_array($imagePlacement, $allowedPlacement)) {
        $imagePlacement = 'bottom';
    }

    $isMultiple = $attributes['multiple'] ?? false;

    // unset attribute yg tidak diperlukan di tag html
    unset($attributes['file_type'], $attributes['image_placement'], $attributes['max_size'], $attributes['options']);

    $livewireModel = $attributes->whereStartsWith('wire:model')->first();
@endphp

@if (isset($document) && $imagePlacement == 'top')
    <x-core::quantum-3.file-preview :id="$attributes['id']" :document="$document" :temporaryUrl="$temporaryUrl"
        :placement="'top'" />
@endif

<div x-data="uploadDraggable" >
    <div class="upload-draggable upload-draggable_inline" 
        @dragover.prevent="dragOver = true"
        @dragleave.prevent="dragOver = false" 
        @drop.prevent="dropFiles($event)"
        @click="$refs.fileInput.click()">
    
        <div class="upload-draggable__box-inline">
            <input type="file" class="upload-draggable__file-input" 
                accept="{{ $accept ?? 'application/pdf,.doc' }}" 
                x-ref="fileInput"
                @change="selectFiles($event)"
                {{ $attributes }}>
    
            <div class="upload-draggable__inline-wrapper">
                <label class="upload-draggable__icon">
                    <x-core::quantum-3.icon icon="upload-cloud" />
                </label>
                <div class="upload-draggable__wrapper">
                    <h2 class="upload-draggable__title">Klik untuk pilih file <span class="upload-draggable__subtitle">atau
                            seret file ke sini</span></h2>
                    <p class="upload-draggable__support">{{ ($acceptDesc ?? 'PDF atau DOC') . " (Max. $maxSizeLabel)" }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-column gap-2 pt-3">
        <template x-for="(fileData, index) in uploadedFiles">
            <div class="attachment" :key="`fileData.file.name`">
                <div class="attachment__wrapper">
                    <div class="attachment__wrapper-icon">
                        <img x-bind:src="`${fileData.icon ?? fileIcons['0']}`">
                    </div>
                    <div class="attachment__wrapper-text">
                        <div class="attachment__title">
                            <h3 class="attachment__heading" x-text="fileData.file.name"></h3>
                            <span class="attachment__description" x-text="fileData.sizeText"></span>
                        </div>
                    </div>
                    <div class="attachment__wrapper-action">
                        <template x-if="fileData.path">
                            <x-core::quantum-3.button 
                                variant="outline-secondary" 
                                size="sm" 
                                leadingIcon="download-cloud" 
                                :icon="true" 
                                x-bind:href="`${fileData.path}`"
                                href="test"
                                download
                            />
                        </template>
                        <x-core::quantum-3.button 
                            data-test="hapus-dokumen"
                            variant="outline-secondary" 
                            size="sm" 
                            leadingIcon="trash" 
                            :icon="true" 
                            @click="removeUploadedFile(index)"
                        />
                    </div>
                </div>
            </div>
        </template>
        <template x-for="(fileData, index) in files">
            <div class="attachment" :key="`fileData.file.name`">
                <div class="attachment__wrapper">
                    <div class="attachment__wrapper-icon">
                        <img x-bind:src="`${fileData.icon ?? fileIcons['0']}`">
                    </div>
                    <div class="attachment__wrapper-text">
                        <div class="attachment__title">
                            <h3 class="attachment__heading" x-text="fileData.file.name"></h3>
                            <span class="attachment__description" x-text="fileData.sizeText"></span>
                        </div>
                    </div>
                    <div class="attachment__wrapper-action">
                        <x-core::quantum-3.button 
                            variant="outline-secondary" 
                            size="sm" 
                            leadingIcon="trash" 
                            :icon="true" 
                            data-test="hapus-dokumen"
                            @click="removeFile(index)"
                        />
                    </div>
                </div>
            </div>
        </template>
        <template x-for="(file, index) in tempFiles">
            <div class="attachment attachment_loading">
                <div class="attachment__wrapper">
                    <div class="attachment__wrapper-icon">
                        <img :src="`${file.icon ?? fileIcons['0']}`">
                    </div>
                    <div class="attachment__wrapper-text">
                        <div class="attachment__title">
                            <h3 class="attachment__heading" x-text="file.name"></h3>
                            <span class="attachment__description" x-text="file.sizeText"></span>
                        </div>
                    </div>
                </div>
                <div class="progress" :data-file="file.name">
                    <div 
                        class="progress-bar progress-bar-striped progress-bar-animated" 
                        role="progressbar" 
                        aria-valuemin="0" 
                        aria-valuemax="100" 
                        x-bind:style="`width: ${loading[file.name] || 0}%`">
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

@if (isset($document) && $imagePlacement == 'bottom')
    <x-core::quantum-3.file-preview :id="$attributes['id']" :document="$document" :temporaryUrl="$temporaryUrl" />
@endif

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('uploadDraggable', () => ({
                tempFiles: [],
                files: [],
                uploadedFiles: JSON.parse(`{!! $dokumenUploaded !!}`),
                
                isMultiple: @json($isMultiple),

                fileQueue: [],
                fileFormData: new FormData(),
                dragOver: false,
                maxSize: {{ $maxSizeByte }},
                acceptedTypes: '{{ $acceptJsValidate ?? 'pdf|doc' }}'.split('|'),
                modelName: @json($livewireModel),
                loading: {},
                fileIcons: {
                    'application/pdf': @json(asset('images/pdf-solid.svg')),
                    'pdf': @json(asset('images/pdf-solid.svg')),
                    '0': @json(asset('quantum/assets/images/misc-icons/file-default/document-solid.svg'))
                },

                init() {
                    const component = Livewire.find(@this.__instance.id);

                    component.on("generatedSignedUrlForS3Bucket", (data) => {
                        if (data[0] === this.modelName) {
                            this.handleSignedUrl(data[0], data[1]);
                        }
                    });

                    this.uploadedFiles = this.uploadedFiles.map(document => {
                        return {
                            file: document,
                            sizeText: this.formatSize(document.size),
                            icon: this.fileIcons[document.type],
                            path: document.url
                        };
                    });

                    this.$watch('loading', (value) => {
                        const buttons = document.querySelectorAll(`button[wire\\:click="save"]`);
                        if (value.length != 0) {
                            buttons.forEach(btn => btn.setAttribute('disabled', true))
                        } else{
                            buttons.forEach(btn => btn.removeAttribute('disabled'))
                        }
                    })
                },

                selectFiles(event) {
                    this.processFiles(event.target.files);
                },

                dropFiles(event) {
                    event.preventDefault();
                    this.dragOver = false;
                    this.processFiles(event.dataTransfer.files);
                },

                removeFile(index) {
                    this.files.splice(index, 1);
                    Livewire.find(@this.__instance.id)._removeFile(this.modelName, index);
                },

                removeUploadedFile(index) {
                    this.uploadedFiles.splice(index, 1);
                    Livewire.find(@this.__instance.id)._removeUploadedFile(this.modelName, index);
                },

                processFiles(fileList) {
                    Array.from(fileList).forEach(file => {
                        if (!this.validateFile(file)) return;
                        this.fileQueue.push(file);

                        // kebutuhan loading
                        this.tempFiles.push({
                            name: file.name,
                            size: file.size,
                            type: file.type,
                            sizeText: this.formatSize(file.size),
                            icon: this.fileIcons[file.type] ?? null 
                        });

                        this.loading[file.name] = 0;
                    });

                    if (this.fileQueue.length > 0) {
                        this.startUpload();
                    }
                },

                startUpload() {
                    const component = Livewire.find(@this.__instance.id);
                    let fileInfos = this.fileQueue.map(file => ({
                        name: file.name,
                        size: file.size,
                        type: file.type
                    }));
                    
                    component._startUpload(this.modelName, fileInfos, this.isMultiple);
                },

                async handleSignedUrl(name, payload) {
                    const file = this.fileQueue.shift();
                    if (!file) return;

                    let headers = payload.headers || {};
                    if (headers.Host) delete headers.Host;
                    this.uploadToS3(name, file, payload.url, headers, () => [payload.path]);
                },

                uploadToS3(name, file, url, headers, retrievePaths) {
                    const xhr = new XMLHttpRequest();
                    xhr.open("PUT", url);

                    Object.entries(headers).forEach(([key, value]) => {
                        xhr.setRequestHeader(key, value);
                    });

                    xhr.upload.onprogress = (event) => {
                        if (event.lengthComputable) {
                            this.loading[file.name] = ((event.loaded / event.total) * 100).toFixed(2);
                        }
                    };

                    xhr.onload = () => {
                        if (xhr.status >= 200 && xhr.status < 300) {
                            const paths = retrievePaths(xhr);

                            this.tempFiles.shift()
                            delete this.loading[file.name];

                            this.files.push({
                                file, 
                                sizeText: this.formatSize(file.size),
                                icon: this.fileIcons[file.type] ?? null,
                                path: null
                            });
                            
                            Livewire.find(@this.__instance.id)._finishUpload(name, paths, this.isMultiple);
                            
                            if (this.fileQueue.length > 0) {
                                this.startUpload();
                            }
                        } else {
                            console.error(`Failed to upload file ${file.name}`);
                        }
                    };

                    xhr.send(file);
                },

                validateFile(file) {
                    const fileType = file.name.split('.').pop().toLowerCase();
                    const component = Livewire.find(@this.__instance.id);

                    if (!this.isMultiple && (this.files.length > 0 || this.uploadedFiles.length > 0 || this.tempFiles.length > 0)) {
                        component._throwValidationError(this.modelName, `Anda hanya dapat mengunggah satu file. Hapus file yang ada untuk mengganti.`)
                        return false;
                    }

                    if (!this.acceptedTypes.includes(fileType)) {
                        component._throwValidationError(this.modelName, `Format file tidak valid. Hanya diperbolehkan: ${this.acceptedTypes.join(', ')}`)
                        return false;
                    }

                    if (file.size > this.maxSize) {
                        component._throwValidationError(this.modelName, `Ukuran file terlalu besar (Max: ${this.formatSize(this.maxSize)})`)
                        return false;
                    }
                    return true;
                },

                formatSize(size) {
                    return size > 1024 * 1024 ? (size / (1024 * 1024)).toFixed(2) + ' MB' : (size / 1024).toFixed(2) + ' KB';
                },
            }));
        });
    </script>
@endPush