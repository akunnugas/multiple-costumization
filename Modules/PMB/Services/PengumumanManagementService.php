<?php

namespace Modules\PMB\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\DMS\Helpers\FolderStructure;
use Modules\DMS\Helpers\UploadDokumen;
use Modules\DMS\Services\DokumenManagementService;
use Modules\Gate\Models\Modul;
use Modules\PMB\Models\Pengumuman;

class PengumumanManagementService
{
    /**
     * @var Pengumuman
     */
    protected $model = Pengumuman::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new Pengumuman;
    }

    /**
     * Menampilkan list data
     *
     * @param int $page
     * @param int $perPage
     * @param array $order
     * @param array $filter
     *
     * @return mixed
     */
    public function index(int|null $page = null, int|null $perPage = null, array $order = [], array $filter = []): mixed
    {
        return ManagementService::create($this->model)->index($page, $perPage, $order, $filter);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return Pengumuman
     */
    public function show(int $id): Pengumuman
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     * @return mixed
     */
    public function store(array $data)
    {
        DB::beginTransaction();

        // gambar jika ada
        $image = $this->processImage($data);
        // attachments jika ada
        $attachments = $this->processAttachments($data);
        // jika ada error
        if ($image instanceof Error || $attachments instanceof Error) {
            DB::rollBack();
            return new Error($image->message ?? $attachments->message);
        }

        try {
            // set image_id
            $data['image_id'] = $image->id ?? null;
            // store data announcement
            $announcement = $this->model->create($data);
            // store announcement files
            $this->storeAnnouncementFiles($attachments, $announcement);

            DB::commit();

            return $announcement;
        } catch (Exception $e) {
            return new Error(exception: $e);
        }
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     * @return mixed
     */
    public function update(array $data, int $id): mixed
    {
        // FIXME: metode update perlu diperjelas lagi terkait file nya
        $model = $this->model->findOrFail($id);

        // gambar jika ada
        $image = $this->processImage($data, $id);
        // attachments jika ada
        $attachments = $this->processAttachments($data, $id);
        // jika ada error
        if ($image instanceof Error || $attachments instanceof Error) {
            return new Error($image->message ?? $attachments->message);
        }

        try {
            DB::beginTransaction();

            // set image_id
            $data['id_file_gambar'] = $image->id ?? null;
            // update data announcement
            $model->update($data);
            // delete announcement files
            $model->announcementFiles()->delete();
            // store announcement files
            $this->storeAnnouncementFiles($attachments, $model);

            DB::commit();

            return $model;
        } catch (Exception $e) {
            return new Error(exception: $e);
        }
    }

    /**
     * Hapus spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function destroy(int $id): mixed
    {
        try {
            DB::beginTransaction();

            $model = $this->model->findOrFail($id);

            // delete image from dms
            if (!empty($model->id_file_gambar)) {
                (new DokumenManagementService)->destroy($model->id_file_gambar);
            }

            // delete announcement file from dms
            $announcementFiles = $model->file()->get();
            foreach ($announcementFiles as $announcementFile) {
                (new DokumenManagementService)->destroy($announcementFile->id_file);
            }
            // delete announcement file from table
            (new PengumumanFileManagementService)->destroySome($announcementFiles->pluck('id'));

            // delete announcement
            $model->destroy($model->id);

            DB::commit();
        } catch (Exception $e) {
            return new Error(exception: $e);
        }

        return null;
    }

    /**
     * Hapus beberapa data berdasarkan id.
     *
     * @param $ids
     * @return Error|null
     */
    public function destroySome($ids)
    {
        return ManagementService::create($this->model)->destroySome($ids);
    }

    /**
     * Upload image
     *
     * @param array $data
     * @return mixed
     */
    private function processImage(array $data, $id = null)
    {
        if (empty($data['id_file_gambar'])) {
            return null;
        }

        $upload = new UploadDokumen();
        if (empty($id)) {
            $image = $upload->upload(
                file: $data['id_file_gambar'],
                name: $data['id_file_gambar']->getClientOriginalName(),
                folderCode: FolderStructure::PMB_PENGUMUMAN,
                note: $data['judul_pengumuman'],
                moduleCode: Modul::CODE_PMB,
                withTransaction: false
            );
        } else {
            $documentId = $this->model->findOrFail($id)->id_file_gambar;
            $image = $upload->update(
                data: [
                    'file' => $data['id_file_gambar'],
                    'name' => $data['id_file_gambar']->getClientOriginalName(),
                    'note' => $data['judul_pengumuman']
                ],
                id: $documentId,
                withTransaction: false
            );
        }

        if ($image instanceof Error) {
            return new Error($image->message);
        }

        return $image->get();
    }

    /**
     * Upload multiple attachments
     *
     * @param array $data
     * @param null $id
     * @return array|Error|null
     */
    private function processAttachments(array $data, $id = null)
    {
        if (empty($data['attachments'])) {
            return null;
        }

        $upload = new UploadDokumen();
        if (empty($id)) {
            $attachments = [];
            foreach ($data['attachments'] as $key => $attachment) {
                $attachments[$key]['file'] = $attachment;
                $attachments[$key]['name'] = $attachment->getClientOriginalName();
                $attachments[$key]['folderCode'] = FolderStructure::PMB_PENGUMUMAN;
                $attachments[$key]['note'] = $data['judul_pengumuman'];
                $attachments[$key]['moduleCode'] = Modul::CODE_PMB;
            }

            $attachments = $upload->uploadMultiple($attachments, withTransaction: false);
        } else {
            $announcementFiles = $this->model->findOrFail($id)->announcementFiles()->get();
            $attachments = [];
            foreach ($data['attachments'] as $key => $attachment) {
                $attachments[$key]['id'] = $announcementFiles[$key]->document_id;
                $attachments[$key]['file'] = $attachment;
                $attachments[$key]['name'] = $attachment->getClientOriginalName();
                $attachments[$key]['note'] = $data['judul_pengumuman'];
            }
            // FIXME: perlu disesuaikan lagi

            $attachments = $upload->updateMultiple($attachments, withTransaction: false);
        }

        if ($attachments instanceof Error) {
            return new Error($attachments->message);
        }

        return $attachments->get();
    }

    /**
     * Store announcement files
     *
     * @param array|null $attachments
     * @param Pengumuman $announcement
     * @return void
     */
    private function storeAnnouncementFiles(array|null $attachments = [], Pengumuman $announcement)
    {
        if (empty($attachments)) {
            return;
        }

        $announcementFileData = [];
        foreach ($attachments as $attachment) {
            $announcementFileData[] = [
                'id_pengumuman' => $announcement->id,
                'id_file' => $attachment->id,
            ];
        }

        $announcement->announcementFiles()->createMany($announcementFileData);
    }
}
