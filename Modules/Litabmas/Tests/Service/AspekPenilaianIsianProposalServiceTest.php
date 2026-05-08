<?php

namespace Modules\Litabmas\Tests\Service;

use Modules\Litabmas\Services\AspekPenilaianIsianProposalService;
use Modules\Gate\Models\User;
use Modules\Litabmas\Models\AspekPenilaianIsianProposal;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class AspekPenilaianIsianProposalServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var AspekPenilaianIsianProposalService
     */
    protected $service = AspekPenilaianIsianProposalService::class;
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
        $this->indexReturnData(new AspekPenilaianIsianProposal);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new AspekPenilaianIsianProposal, 'nama_isian_proposal');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new AspekPenilaianIsianProposal);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new AspekPenilaianIsianProposal);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new AspekPenilaianIsianProposal);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new AspekPenilaianIsianProposal, [
            [
                'key' => 'nama_isian_proposal',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new AspekPenilaianIsianProposal);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new AspekPenilaianIsianProposal);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new AspekPenilaianIsianProposal);
    }
}
