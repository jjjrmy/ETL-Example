<?php

namespace App\Actions\ReqresIN;

use App\Actions\GetLeads as GetLeadsAction;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;

final class GetLeads extends GetLeadsAction
{
    protected string $source = 'ReqresIN';

    private string $path = '/users';
    private int $itemsPerPage = 6;

    /**
     * Columns you'd like in the CSV output
     *
     * @return array
     */
    protected array $csvColumns = [
        'id', 'first_name', 'last_name', 'email', 'avatar'
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
        'address' => null,
        'job_title' => null,
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
        return Http::get(env('REQRESIN_ENDPOINT') . $this->path, [
            'per_page' => $this->itemsPerPage,
            'page' => $pageNumber,
        ]);
    }

    /**
     * Extra the Data from the Response that gets pushed to the Collection
     *
     * @param Response $page    The Page Response
     * @return Collection
     */
    public function extractData(Response $page): Collection
    {
        return $page->collect('data');
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
            && $this->leads->count() <= $page->json('total')
            && $this->pageNumber <= $page->json('total_pages');
    }
}
