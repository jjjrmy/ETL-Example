<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Queue;

class GetLeadsTest extends TestCase
{
    /**
     * Should Dispatch The Sub Jobs per API
     *
     * @return void
     */
    public function test_job_children_are_dispatched()
    {
        Queue::fake();

        (new \App\Jobs\GetLeads())->handle();

        \App\Actions\MockAPI\GetLeads::assertPushed();
        \App\Actions\ReqresIN\GetLeads::assertPushed();
    }
}
