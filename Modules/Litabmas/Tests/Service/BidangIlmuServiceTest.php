<?php

namespace Modules\Litabmas\Tests\Service;

use Modules\Litabmas\Services\BidangIlmuService;
use Modules\Gate\Models\User;
use Modules\Litabmas\Models\BidangIlmu;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class BidangIlmuServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var BidangIlmuService
     */
    protected $service = BidangIlmuService::class;
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
        $this->indexReturnData(new BidangIlmu);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new BidangIlmu, 'sm.nama_bidang_ilmu');
    }
}
