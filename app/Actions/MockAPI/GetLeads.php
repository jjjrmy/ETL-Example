<?php

namespace App\Actions\MockAPI;

use App\Actions\GetLeads as GetLeadsAction;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

final class GetLeads extends GetLeadsAction
{
    protected string $source = 'MockAPI';

    private string $path = '/user/users';
    private int $itemsPerPage = 10;

    /**
     * Columns you'd like in the CSV output
     *
     * @return array
     */
    protected array $csvColumns = [
        'id', 'first_name', 'last_name', 'address', 'job_title', 'createdAt'
    ];

    /**
     * Attributes you'd like to map the Model to
     *
     * @return array
     */
    protected array $dataMap = [
        'foreign_id' => 'id',
        'first_name' => 'first_name',
        'last_name' => 'last_name',
        'address' => 'address',
        'job_title' => 'job_title',
        'created_at' => 'createdAt',
    ];

    /**
     * Make The HTTP Request
     *
     * @param int $pageNumber     Page Number
     *
     * @return Response
     */
    public function getPage(int $pageNumber): Response
    {
        return Http::get(env('MOCKAPI_ENDPOINT') . $this->path, [
            'limit' => $this->itemsPerPage,
            'page' => $pageNumber,
        ]);
    }

    /**
     * If Condition is met to Stop Pagination
     *
     * @param Response $page    The Page Response
     * @return bool
     */
    public function whileLoop(Response $page): bool
    {
        return $page->successful()
            && $this->extractData($page)->isNotEmpty()
            && !($this->leads->count() % $this->itemsPerPage);
    }
}
