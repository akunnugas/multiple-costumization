<?php

namespace Modules\Litabmas\Tests\Service;

use Modules\Litabmas\Services\AspekPenilaianKomposisiProposalService;
use Modules\Gate\Models\User;
use Modules\Litabmas\Models\AspekPenilaianKomposisiProposal;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class AspekPenilaianKomposisiProposalServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var AspekPenilaianKomposisiProposalService
     */
    protected $service = AspekPenilaianKomposisiProposalService::class;
    protected $auth;

    protected function prepare(): void
    {
        // Setup auth
        $user = User::factory()->create();
        $this->auth = $this->actingAs($user);
    }

    /**
     * Test index to retrieve data
     */
    public function test_index_return_data()
    {
        $this->indexReturnData(new AspekPenilaianKomposisiProposal);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new AspekPenilaianKomposisiProposal, 'nama_komposisi_proposal');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new AspekPenilaianKomposisiProposal);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new AspekPenilaianKomposisiProposal);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new AspekPenilaianKomposisiProposal);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new AspekPenilaianKomposisiProposal, [
            [
                'key' => 'nama_komposisi_proposal',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new AspekPenilaianKomposisiProposal);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new AspekPenilaianKomposisiProposal);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new AspekPenilaianKomposisiProposal);
    }
}
