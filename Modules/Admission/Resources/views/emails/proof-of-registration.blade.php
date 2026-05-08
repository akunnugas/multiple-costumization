@props([
    'registrant',
    'person',
    'registrationPeriod',
])

<x-core::layouts.email>
    <h1 style="text-align:center;">
        BUKTI PENDAFTARAN
    </h1>
    <hr />
    <div style="margin-top: 20px; margin-bottom: 10px;">
        <font style="font-family:'Work Sans'; font-size: 12pt; color: #4F4F4F; margin-top:-20px;">
            Data Calon Mahasiswa
        </font>
    </div>
    <table class="content" width="100%" style="margin: 10px auto;">
        <tbody>
            <tr>
                <td colspan="2">
                    <table>
                        <tr>
                            <td style="vertical-align:middle; font-weight:bold" width="100px">ID Pendaftar</td>
                            <td>
                                <h1 class="text-brand no-padding" style="margin: 3px">{{ $registrant['code'] }}</h1>
                            </td>
                        </tr>
                        <tr>
                            <td style="vertical-align:middle; font-weight:bold" width="100px">Nama Lengkap</td>
                            <td>
                                <h1 class="text-brand no-padding" style="margin: 3px">{{ $person['name'] }}</h1>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="cards-attr" style="font-weight:bold">Tanggal Daftar</span><br />
                    <div class="cards-value">
                        {{ \Carbon\Carbon::parse($registrant['created_at'])->isoFormat('D MMMM Y, HH:mm:ss') }}
                    </div>
                </td>
                <td>
                    <span class="cards-attr" style="font-weight:bold">Tempat, Tanggal Lahir</span><br />
                    <div class="cards-value">
                        {{ $person['birth_place'] . ', ' . \Carbon\Carbon::parse($person['birth_date'])->isoFormat('D MMMM Y') }}
                    </div>
                </td>
                <td>
                    <span class="cards-attr" style="font-weight:bold">Jenis Kelamin</span><br />
                    <div class="cards-value">
                        {{ \Modules\Core\Models\Person::GENDER[$person['gender']] }}
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="cards-attr" style="font-weight:bold">Jalur Pendaftaran</span><br />
                    <div class="cards-value">
                        {{ $registrationPeriod['registration_path_name'] }}
                    </div>
                </td>
                <td>
                    <span class="cards-attr" style="font-weight:bold">Gelombang</span><br />
                    <div class="cards-value">
                        {{ $registrationPeriod['batch_name'] }}
                    </div>
                </td>
                <td>
                    <span class="cards-attr" style="font-weight:bold">Periode</span><br />
                    <div class="cards-value">
                        {{ $registrationPeriod['period_name'] }}
                    </div>
                </td>
                <td>
                    <span class="cards-attr" style="font-weight:bold">Sistem Kuliah</span><br />
                    <div class="cards-value">
                        {{ $registrationPeriod['lecture_system_name'] }}
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
    <hr />
    <div style="margin-top: 20px; margin-bottom: 10px;">
        <font style="font-family:'Work Sans'; font-size: 12pt; color: #4F4F4F; margin-top:-20px;">
            Pilihan Jurusan
        </font>
    </div>
    <table width="100%" cellspacing="0" cellpadding="2">
        @php
            // TODO: pilihan prodi dinamis
        @endphp
        <tbody>
            <tr>
                <td style="border-bottom:0px">
                    <span>Pilihan 1</span><br>
                    <div>S1 - Ilmu Informatika</div>
                </td>
            </tr>
        </tbody>
    </table>

    @php
        // TODO: tagihan
    @endphp
</x-core::layouts.email>
