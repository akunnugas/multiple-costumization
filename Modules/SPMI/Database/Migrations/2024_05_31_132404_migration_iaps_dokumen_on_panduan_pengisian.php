<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Http\UploadedFile;
// use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Helpers\Error;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Services\PengisianPanduanManagementService;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // // Upload panduan pengisian IAPS9 default
        // $panduanFile = Storage::disk('local')->get('template-panduan/template_pengisian.pdf');
        // $panduanBase64 = base64_encode($panduanFile);
        
        // $upload = TemporaryUploadedFile::createFromBase(UploadedFile::fake()->createWithContent('template_pengisian.pdf', $panduanBase64));
        // // Ambil panduan pengisian IAPS9
        // $pengisianPanduan = PengisianPanduan::where('kode_pengisian_panduan', 'IAPS9')->first();

        // $updated = (new PengisianPanduanManagementService)->update([
        //     'id_dokumen' => $upload,
        // ], $pengisianPanduan->id);

        // if (Error::isError($updated)) {
        //     throw new \Exception($updated->getMessage());
        // }

        // // Upload panduan pengisian LEDPS9 default
        // $panduanFile = Storage::disk('local')->get('template-panduan/template_ledps9.pdf');
        // $panduanBase64 = base64_encode($panduanFile);
        
        // $upload = UploadedFile::fake()->createWithContent('template_ledps9.pdf', $panduanBase64);
        // // Ambil panduan pengisian IAPS9
        // $pengisianPanduan = PengisianPanduan::where('kode_pengisian_panduan', 'LEDPS9')->first();

        // $updated = (new PengisianPanduanManagementService)->update([
        //     'id_dokumen' => $upload,
        // ], $pengisianPanduan->id);

        // if (Error::isError($updated)) {
        //     throw new \Exception($updated->getMessage());
        // }
    }

    /**
 * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
