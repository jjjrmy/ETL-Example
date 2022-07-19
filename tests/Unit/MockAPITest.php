<?php

namespace Tests\Unit;

use App\Actions\MockAPI\GetLeads;
use Illuminate\Support\Collection;
use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MockAPITest extends TestCase
{
    use RefreshDatabase;

    /**
     * Fake User data for the Request
     *
     * @param int $count    Number Of Records
     * @return \Illuminate\Support\Collection
     */
    private function fakeUsers(int $count): Collection
    {
        $fakeUsers = new Collection();

        for ($i = 1; $i <= $count; $i++) {
            $fakeUsers->push([
                'id' => $i,
                'first_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
                'address' => fake()->streetAddress(),
                'job_title' => fake()->jobTitle(),
                'createdAt' => fake()->iso8601(),
            ]);
        }

        return $fakeUsers;
    }

    /**
     * Fake Request Response
     *
     * @param int $count    Number Of Records
     * @param int $chunk    How Many Records Per Page
     * @return array
     */
    private function fakeRequests(int $count, int $chunk): array
    {
        return $this->fakeUsers($count)->chunk($chunk)->toArray();
    }

    /**
     * Should Only Make 1 Request for the 5 Users
     * Records Count Has Remaining Modulo From Page, No More Records
     *
     * @return void
     */
    public function test_request_stops_on_modulo_remaining_one_page(): void
    {
        Http::fake([
            env('MOCKAPI_ENDPOINT') . '/*' => Http::sequence(
                $this->fakeRequests(5, 10)
            )->whenEmpty(Http::response([], 200))
        ]);

        $fakeLeads = GetLeads::run();

        Http::assertSentCount(1);
        $this->assertEquals(5, $fakeLeads->count());
    }

    /**
     * Should Only Make 2 Request for the 10 Users
     * Page Response Is Empty, No More Records
     * It makes the last request because there might be more on the next page
     *
     * @return void
     */
    public function test_request_stops_on_page_empty_one_page(): void
    {
        Http::fake([
            env('MOCKAPI_ENDPOINT') . '/*' => Http::sequence(
                $this->fakeRequests(10, 10)
            )->whenEmpty(Http::response([], 200))
        ]);

        $fakeLeads = GetLeads::run();

        Http::assertSentCount(2);
        $this->assertEquals(10, $fakeLeads->count());
    }

    /**
     * Should Only Make 2 Request for the 15 Users
     * Records Count Has Remaining Modulo From Page, No More Records
     *
     * @return void
     */
    public function test_request_stops_on_modulo_remaining_multiple_pages(): void
    {
        Http::fake([
            env('MOCKAPI_ENDPOINT') . '/*' => Http::sequence(
                $this->fakeRequests(15, 10)
            )->whenEmpty(Http::response([], 200))
        ]);

        $fakeLeads = GetLeads::run();

        Http::assertSentCount(2);
        $this->assertEquals(15, $fakeLeads->count());
    }

    /**
     * Should Only Make 3 Request for the 20 Users
     * Page Response Is Empty, No More Records
     * It makes the last request because there might be more on the next page
     *
     * @return void
     */
    public function test_request_stops_on_page_empty_multiple_page(): void
    {
        Http::fake([
            env('MOCKAPI_ENDPOINT') . '/*' => Http::sequence(
                $this->fakeRequests(20, 10)
            )->whenEmpty(Http::response([], 200))
        ]);

        $fakeLeads = GetLeads::run();

        Http::assertSentCount(3);
        $this->assertEquals(20, $fakeLeads->count());
    }

    /**
     * Should Only Make 1 Request that Errors
     * Page Response Failed
     *
     * @return void
     */
    public function test_request_stops_on_fail_one_page(): void
    {
        Http::fake([
            env('MOCKAPI_ENDPOINT') . '/*' => Http::sequence(
                [],
                500
            )->whenEmpty(Http::response([], 500))
        ]);

        $fakeLeads = GetLeads::run();

        Http::assertSentCount(1);
        $this->assertEquals(0, $fakeLeads->count());
    }

    /**
     * Should Only Make 3 Request for the 20 Users then Errors
     * Page Response Failed
     * It makes the last request because there might be more on the next page
     *
     * @return void
     */
    public function test_request_stops_on_fail_multiple_pages(): void
    {
        Http::fake([
            env('MOCKAPI_ENDPOINT') . '/*' => Http::sequence(
                $this->fakeRequests(20, 10)
            )->whenEmpty(Http::response([], 500))
        ]);

        $fakeLeads = GetLeads::run();

        Http::assertSentCount(3);
        $this->assertEquals(20, $fakeLeads->count());
    }

    /**
     * Should Transform Data To Model Fields
     *
     * @return void
     */
    public function test_data_is_transformed(): void
    {
        Http::fake([
            env('MOCKAPI_ENDPOINT') . '/*' => Http::sequence(
                $this->fakeRequests(5, 10)
            )->whenEmpty(Http::response([], 500))
        ]);

        $fakeLeads = GetLeads::run();

        $fakeLeads->each(function ($lead) {
            foreach (['foreign_id', 'first_name', 'last_name', 'address', 'job_title'] as $key) {
                $this->assertArrayHasKey($key, $lead);
            }
        });
    }
}
