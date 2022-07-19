<?php

namespace App\Actions;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Support\Collection;
use Illuminate\Support\Arr;

abstract class GetLeads
{
    use AsAction;

    // DOC: https://laravelactions.com/2.x/as-job.html#methods-used
    public int $jobTries = 1;
    public int $jobTimeout = 60 * 30;
    public function getJobDisplayName(): string
    {
        return implode(': ', $this->getJobTags());
    }
    public function getJobTags(): array
    {
        return [__CLASS__, $this->source];
    }

    protected string $source;
    protected int $pageNumber = 1;
    protected Collection $leads;

    /**
     * Columns you'd like in the CSV output
     *
     * @return array
     */
    protected array $csvColumns;

    /**
     * Attributes you'd like to map the Model to
     *
     * @return array
     */
    protected array $dataMap;

    public function __construct()
    {
        $this->leads = new Collection();
    }

    /**
     * Loop Through Pages Until Error or Limit
     *
     * @return Collection
     */
    public function handle(): Collection
    {
        do {
            $page = $this->getPage($this->pageNumber);

            $this->leads->push(...$this->extractData($page));

            $this->pageNumber++;
        } while ($this->whileLoop($page));

        return $this->leads
            ->whenNotEmpty(
                fn ($leads) =>
                $leads->tap(function ($leads) {
                    $leads->toCSV(
                        $this->csvColumns,
                        $this->source
                    );
                })
            )
            ->transform(fn ($lead) => $this->transformData($lead))
            ->tap(function ($leads) {
                $this->saveToDatabase($leads);
            });
    }

    /**
     * Make The HTTP Request
     *
     * @param int $pageNumber     Page Number
     *
     * @return Response
     */
    abstract protected function getPage(int $pageNumber): Response;

    /**
     * Extra the Data from the Response that gets pushed to the Collection
     *
     * @param Response $page    The Page Response
     * @return Collection
     */
    public function extractData(Response $page): Collection
    {
        return $page->collect();
    }

    /**
     * If Condition is met to Stop Pagination
     * This should stop runaway loops
     *
     * @param Response $page    The Page Response
     * @return bool
     */
    abstract public function whileLoop(Response $page): bool;

    /**
     * Transform Data to Lead Format
     *
     * @param array $data     Data from API
     *
     * @return array
     */
    public function transformData(array $data): array
    {
        return collect($this->dataMap)
            ->map(fn ($value) => isset($value) ? Arr::get($data, $value) : $value)
            ->merge(['source' => $this->source])
            ->toArray();
    }

    /**
     * Loop Through Leads And Save To Database
     *
     * @return Void
     */
    public function saveToDatabase(Collection $leads): void
    {
        $leads->each(function ($lead) {
            \App\Models\Lead::updateOrCreate(
                ['source' => $lead['source'], 'foreign_id' => $lead['foreign_id']],
                $lead
            );
        });
    }
}
