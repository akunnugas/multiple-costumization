<?php

namespace Modules\DMS\Tests\Service;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Modules\DMS\Models\Dokumen;
use Modules\DMS\Services\DokumenManagementService;
use Illuminate\Support\Str;
use Modules\Core\Helpers\Error;
use Modules\Core\Models\UnitKerja;
use Modules\DMS\Helpers\FolderStructure;
use Modules\DMS\Models\DokumenKolaborator;
use Modules\DMS\Models\DokumenUnit;
use Modules\DMS\Models\DokumenPerizinan;
use Modules\DMS\Models\DokumenTag;
use Modules\DMS\Models\DokumenVersi;
use Modules\DMS\Models\Tag;
use Modules\Gate\Models\User;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class DokumenManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    protected $service = DokumenManagementService::class;
    protected $moduleCode = 'dms';
    protected $auth;
    protected $user;

    protected function prepare(): void
    {
        // Setup auth
        $this->user = User::factory()->create();
        $this->auth = $this->actingAs($this->user);
    }

    /**
     * Test index to retrieve data
     */
    public function test_index_return_data()
    {
        $this->indexReturnData(new Dokumen);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new Dokumen, 'nama_dokumen');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new Dokumen);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new Dokumen);
    }

    /**
     * Test to store data
     */
    public function test_store_data_with_local_storage()
    {
        // Test default storagenya menggunakan lokal
        config()->set('filesystems.default', 'local');
        Storage::fake('local');

        [$data, $stored] = $this->store();

        // Assert store dokumen
        $this->assertDatabaseHas($stored->getTable(), Arr::except($stored->toArray(), ['created_at', 'waktu_diubah']));

        // Assert versi dokumen
        $this->assertDatabaseHas((new DokumenVersi)->getTable(), $stored->versions->first()->toArray());

        // Assert tag dokumen
        $this->assertDatabaseHas((new Tag)->getTable(), [
            'nama_dokumen' => $data['tags'][0]
        ]);
        $this->assertDatabaseHas((new DokumenTag)->getTable(), [
            'id_dokumen' => $stored->id
        ]);
    }

    /**
     * Test to store data
     */
    public function test_store_data_with_s3_storage()
    {
        config()->set('filesystems.default', 's3');
        Storage::fake('s3');

        [$data, $stored] = $this->store();

        // Assert store dokumen
        $this->assertDatabaseHas($stored->getTable(), Arr::except($stored->toArray(), ['created_at', 'waktu_diubah']));

        // Assert versi dokumen
        $this->assertDatabaseHas((new DokumenVersi)->getTable(), $stored->versions->first()->toArray());

        // Assert tag dokumen
        $this->assertDatabaseHas((new Tag)->getTable(), [
            'nama_dokumen' => $data['tags'][0]
        ]);
        $this->assertDatabaseHas((new DokumenTag)->getTable(), [
            'id_dokumen' => $stored->id
        ]);
    }

    /**
     * Test to store data with multiple
     */
    public function test_multiple_store()
    {
        // Test default storagenya menggunakan lokal
        config()->set('filesystems.default', 'local');
        Storage::fake('local');

        [$data, $stored] = $this->storeMultiple();

        $this->service->executeUpload();

        // Assert store dokumen
        $this->assertDatabaseHas($stored[0]->getTable(), Arr::except($stored[0]->toArray(), ['created_at', 'waktu_diubah']));

        // Assert versi dokumen
        $this->assertDatabaseHas((new DokumenVersi)->getTable(), $stored[0]->versions->first()->toArray());

        // Assert tag dokumen
        $this->assertDatabaseHas((new Tag)->getTable(), [
            'nama_dokumen' => $data['tags'][0]
        ]);

        // Assert tag dokumen
        $this->assertDatabaseHas((new DokumenTag)->getTable(), [
            'id_dokumen' => $stored[0]->id
        ]);
    }

    /**
     * Test to revision new Dokumen version data
     */
    public function test_add_revision()
    {
        // Test default storagenya menggunakan lokal
        Storage::fake('local');

        [, $stored] = $this->store();

        $docVersion = $this->service->addRevision(
            id: $stored->id,
            file: UploadedFile::fake()->image('Dokumen2.pdf'),
        );

        $this->assertDatabaseHas($docVersion->getTable(), ['id' => $docVersion->toArray()['id']]);
        $this->assertDatabaseHas($stored->getTable(), ['versi_terbaru' => $docVersion->version, 'alamat_versi_terbaru' => $docVersion->file_path]);
    }

    /**
     * Test to revision with valid all version size
     */
    public function test_add_revision_with_valid_all_version_size()
    {
        // Test default storagenya menggunakan lokal
        Storage::fake('local');

        [, $stored] = $this->store();

        $docVersion = $this->service->addRevision(
            id: $stored->id,
            file: UploadedFile::fake()->image('Dokumen2.pdf'),
        );

        $expectedSize = $stored->size + $docVersion->size;

        $this->assertDatabaseHas($docVersion->getTable(), ['id' => $docVersion->toArray()['id']]);
        $this->assertDatabaseHas($stored->getTable(), ['id' => $stored->id, 'ukuran' => $expectedSize]);
    }

    /**
     * Test show path with authorize collaborator
     */
    public function test_show_path_with_authorize_collaborator()
    {
        // Test default storagenya menggunakan lokal
        Storage::fake('local');

        [, $stored] = $this->store();

        $users = User::factory(3)->create()->pluck('id');

        $this->service->setPermission(
            Dokumen: $stored->id,
            type: DokumenPerizinan::COLLABORATOR,
            data: [...$users->toArray(), $this->user->id]
        );

        $path = $this->service->showPath($stored->slug);

        $this->assertNotNull($path);
        $this->assertTrue(!($path instanceof Error));
    }

    /**
     * Test show path with authorize organization
     */
    public function test_show_path_with_authorize_organization()
    {
        // Test default storagenya menggunakan lokal
        Storage::fake('local');

        [, $stored] = $this->store();

        $organizations = UnitKerja::factory(1)->create([
            'kode_dikti' => Str::password(8, false, true, false, false),
            'jenis_unit' => UnitKerja::UNIVERSITY,
        ])->pluck('id')->toArray();

        $this->user->id_unit_kerja = $organizations[0];

        $this->service->setPermission(
            Dokumen: $stored->id,
            type: DokumenPerizinan::ORGANIZATION,
            data: $organizations
        );

        $path = $this->service->showPath($stored->slug);

        $this->assertNotNull($path);
        $this->assertTrue(!($path instanceof Error));
    }

    /**
     * Test show path by version
     */
    public function test_show_path_by_version()
    {
        // Test default storagenya menggunakan lokal
        Storage::fake('local');

        [, $stored] = $this->store();

        $users = User::factory(3)->create()->pluck('id');

        $this->service->setPermission(
            Dokumen: $stored->id,
            type: DokumenPerizinan::COLLABORATOR,
            data: [...$users->toArray(), $this->user->id]
        );

        $path = $this->service->showPath($stored->slug, $stored->versi_terbaru);

        $this->assertNotNull($path);
        $this->assertTrue(!($path instanceof Error));
        $this->assertTrue($stored->versi_terbaru === 1);
    }

    /**
     * Test show path with unauthorize collaborator
     */
    public function test_show_path_with_unauthorize_action()
    {
        // Test default storagenya menggunakan lokal
        Storage::fake('local');

        [, $stored] = $this->store();

        $path = $this->service->showPath($stored->slug);

        $this->assertEquals($path->code, 401);
    }

    /**
     * Test show path with invalid slug
     */
    public function test_show_path_with_invalid_slug()
    {
        // Test default storagenya menggunakan lokal
        Storage::fake('local');

        [, $stored] = $this->store();

        $path = $this->service->showPath($stored->slug . '0-vv-z');

        $this->assertTrue($path instanceof Error);
        $this->assertEquals($path->code, 404);
    }

    /**
     * Test to set Dokumen permission collaborator
     */
    public function test_set_collaborator()
    {
        $dokumen = Dokumen::factory()->create();

        DokumenPerizinan::create([
            'id_dokumen' => $dokumen->id,
            'nama_perizinan' => 'Collaborator',
            'jenis_perizinan' => DokumenPerizinan::COLLABORATOR
        ]);

        $users = User::factory(5)->create()->pluck('id');

        $this->service->setPermission(
            Dokumen: $dokumen->id,
            type: DokumenPerizinan::COLLABORATOR,
            data: $users->toArray()
        );

        $this->assertDatabaseHas((new DokumenKolaborator)->getTable(), ['id_user' => $users[0]]);
    }

    /**
     * Test to set Dokumen permission organization
     */
    public function test_set_organization()
    {
        $dokumen = Dokumen::factory()->create();

        DokumenPerizinan::create([
            'id_dokumen' => $dokumen->id,
            'nama_perizinan' => 'Organization',
            'jenis_perizinan' => DokumenPerizinan::ORGANIZATION
        ]);

        $organizations = UnitKerja::factory(5)->create()->pluck('id');

        $this->service->setPermission(
            Dokumen: $dokumen->id,
            type: DokumenPerizinan::ORGANIZATION,
            data: $organizations->toArray()
        );

        $this->assertDatabaseHas((new DokumenUnit)->getTable(), ['id_unit_kerja' => $organizations[0]]);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new Dokumen, [
            [
                'key' => 'nama_dokumen',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with file
     */
    public function test_update_with_file()
    {
        // Test default storagenya menggunakan lokal
        config()->set('filesystems.default', 'local');
        Storage::fake('local');

        [$old, $updated] = $this->updateWithFile();

        $this->service->executeUpload();

        // Assert store dokumen
        $this->assertDatabaseHas(
            $updated->getTable(),
            [
                'id' => $updated->id,
                'versi_terbaru' => $updated->versi_terbaru,
                'alamat_versi_terbaru' => $updated->alamat_versi_terbaru,
            ]
        );

        // Assert versi dokumen
        $this->assertDatabaseHas((new DokumenVersi)->getTable(), $updated->versions->first()->toArray());
    }

    public function test_update_with_file_multiple()
    {
        // Test default storagenya menggunakan lokal
        config()->set('filesystems.default', 'local');
        Storage::fake('local');

        [$old, $updated] = $this->updateWithFileMultiple();

        $this->service->executeUpload();

        // Assert store dokumen
        $this->assertDatabaseHas(
            $updated[0]->getTable(),
            [
                'id' => $updated[0]->id,
                'versi_terbaru' => $updated[0]->versi_terbaru,
                'alamat_versi_terbaru' => $updated[0]->alamat_versi_terbaru,
            ]
        );

        // Assert versi dokumen
        $this->assertDatabaseHas((new DokumenVersi)->getTable(), $updated[0]->versions->first()->toArray());
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new Dokumen);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new Dokumen);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new Dokumen);
    }

    private function store()
    {
        UnitKerja::factory()->create([
            'kode_dikti' => Str::password(8, false, true, false, false),
            'jenis_unit' => UnitKerja::UNIVERSITY,
        ]);

        // Factory data
        $data = Arr::except(Dokumen::factory()->make()->toArray(), ['ukuran']);
        $data['resource'] = substr(strtolower(fake()->word()), 0, 4);
        $data['tags'] = ['pembelajaran', 'test'];

        // Mocking file dokumen
        $data['file'] = UploadedFile::fake()->image('Dokumen.pdf');
        $data['kode_folder'] = FolderStructure::SPMI_PENJAMINAN_MUTU;

        // Tambah dokumen
        $stored = $this->service->store($data);

        return [
            $data,
            $stored
        ];
    }

    private function updateWithFile()
    {
        UnitKerja::factory()->create([
            'kode_dikti' => Str::password(8, false, true, false, false),
            'jenis_unit' => UnitKerja::UNIVERSITY,
        ]);

        // Factory data
        $data = Arr::except(Dokumen::factory()->make()->toArray(), ['ukuran']);
        $data['resource'] = substr(strtolower(fake()->word()), 0, 4);
        $data['tags'] = ['pembelajaran', 'test'];

        // Mocking file dokumen
        $data['file'] = UploadedFile::fake()->image('Dokumen.pdf');
        $data['kode_folder'] = FolderStructure::SPMI_PENJAMINAN_MUTU;

        // Tambah dokumen
        $stored = $this->service->store($data);

        // Update dokumen
        $data['nama_dokumen'] = 'Update ' . $data['nama_dokumen'];
        $data['file'] = UploadedFile::fake()->image('Dokumen2.pdf');
        $data['kode_folder'] = FolderStructure::SPMI_PENJAMINAN_MUTU;

        $updated = $this->service->update(
            data: $data,
            id: $stored->id,
            autoExecuteUpload: false,
            isReplace: true
        );

        return [
            $stored,
            $updated
        ];
    }

    private function updateWithFileMultiple()
    {
        UnitKerja::factory()->create([
            'kode_dikti' => Str::password(8, false, true, false, false),
            'jenis_unit' => UnitKerja::UNIVERSITY,
        ]);

        // Factory data
        $data = Arr::except(Dokumen::factory()->make()->toArray(), ['ukuran']);
        $data['resource'] = substr(strtolower(fake()->word()), 0, 4);
        $data['tags'] = ['pembelajaran', 'test'];

        // Mocking file dokumen
        $data['file'] = UploadedFile::fake()->image('Dokumen.pdf');
        $data['kode_folder'] = FolderStructure::SPMI_PENJAMINAN_MUTU;

        // Tambah dokumen
        $stored = $this->service->store($data);

        // Update dokumen
        $data['nama_dokumen'] = 'Update ' . $data['nama_dokumen'];
        $data['file'] = UploadedFile::fake()->image('Dokumen2.pdf');
        $data['kode_folder'] = FolderStructure::SPMI_PENJAMINAN_MUTU;

        $updated = $this->service->updateMultiple(
            data: [
                [
                    'id' => $stored->id, 'file' => $data['file'], 'nama_dokumen' => $data['nama_dokumen']
                ]
            ],
            autoExecuteUpload: false,
            isReplace: true
        );

        return [
            $stored,
            $updated
        ];
    }

    private function storeMultiple()
    {
        UnitKerja::factory()->create([
            'kode_dikti' => Str::password(8, false, true, false, false),
            'jenis_unit' => UnitKerja::UNIVERSITY,
        ]);

        // Factory data
        $data = Arr::except(Dokumen::factory()->make()->toArray(), ['ukuran']);
        $data['resource'] = substr(strtolower(fake()->word()), 0, 4);
        $data['tags'] = ['pembelajaran', 'test'];

        // Mocking file dokumen
        $data['file'] = UploadedFile::fake()->image('Dokumen.pdf');
        $data['kode_folder'] = FolderStructure::SPMI_PENJAMINAN_MUTU;

        // Tambah dokumen
        $stored = $this->service->storeMultiple(data: [$data], autoExecuteUpload: false);

        return [
            $data,
            $stored
        ];
    }
}
