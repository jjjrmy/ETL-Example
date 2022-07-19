<?php

namespace Tests\Unit;

use Illuminate\Support\Collection;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class SaveToCSVTest extends TestCase
{
    protected string $disk = 'backups';
    protected array $columns = ['first_name', 'address', 'job_title'];
    protected array $leads = [
        [
            'first_name' => 'Jeremy Holstein',
            'address' => 'FL',
            'job_title' => 'Laravel Developer',
        ],
        [
            'first_name' => 'Swaggatron',
            'address' => 'FL',
            'color' => '#000000',
        ],
        [
            'first_name' => 'Alicia Baron',
            'job_title' => 'Hiring Manager',
        ],
    ];

    /**
     * Should Create The CSV from a Collection of Arrays, with CSV Headers
     *
     * @return void
     */
    public function test_save_array_to_csv(): void
    {
        Storage::fake($this->disk);

        $leads = collect($this->leads);

        $csv = $leads->toCSV($this->columns, 'array2csv', $this->disk);

        Storage::disk($this->disk)->assertExists($csv);

        $leadsFromCSV = $this->csv2array($csv, true);

        $this->assertTrue($leadsFromCSV->first()->has($this->columns), "CSV Has Headers");
        $this->assertEquals(
            $leadsFromCSV->skip(0)->map(
                fn ($row) =>
                $row->only($this->columns)->filter()
            )->toArray(),
            $leads->map(
                fn ($row) =>
                Arr::only($row, $this->columns)
            )->toArray()
        );
    }

    /**
     * Should Create The CSV from Collection of Arrays, without CSV Header
     *
     * @return void
     */
    public function test_save_array_to_csv_without_headers(): void
    {
        Storage::fake($this->disk);

        $leads = collect($this->leads);

        $csv = $leads->toCSV($this->columns, 'array2csv_without_headers', $this->disk, false);

        Storage::disk($this->disk)->assertExists($csv);

        $leadsFromCSV = $this->csv2array($csv);

        $this->assertFalse($leadsFromCSV->first()->has($this->columns), "CSV Missing Headers");
        $this->assertEquals(
            $leadsFromCSV->map(
                fn ($row) =>
                $row->filter()->values()
            )->toArray(),
            $leads->map(
                fn ($row) =>
                array_values(Arr::only($row, $this->columns))
            )->toArray()
        );
    }

    /**
     * Should Create the CSV from a Collection of Models, with CSV Header
     *
     * @return void
     */
    public function test_save_models_to_csv(): void
    {
        Storage::fake($this->disk);

        $leads = collect($this->leads);

        $csv = $leads->mapInto(\App\Models\Lead::class)->toCSV($this->columns, 'models2csv', $this->disk);

        Storage::disk($this->disk)->assertExists($csv);

        $leadsFromCSV = $this->csv2array($csv, true);

        $this->assertTrue($leadsFromCSV->first()->has($this->columns), "CSV Has Headers");
        $this->assertEquals(
            $leadsFromCSV->skip(0)->map(
                fn ($row) =>
                $row->only($this->columns)->filter()
            )->toArray(),
            $leads->map(
                fn ($row) =>
                Arr::only($row, $this->columns)
            )->toArray()
        );
    }

    /**
     * Return The CSV as an Array
     *
     * @param string $csv   File Path + Name
     * @param bool $withHeaders CSV has Headers to use as Keys
     * @return Collection
     */
    private function csv2array(string $csv, bool $withHeaders = false): Collection
    {
        $file = Storage::disk($this->disk)->get($csv);

        return Str::of($file)
            ->explode("\n")->filter()
            ->transform(
                fn ($row) =>
                Str::of($row)
                    ->explode(",")
                    ->transform(fn ($col) => trim($col, '"'))
            )
            ->when($withHeaders, function ($rows) {
                $headers = $rows->shift();
                return $rows->transform(
                    fn ($row) =>
                    $row->mapWithKeys(fn ($col, $i) => [$headers->get($i, $i) => $col])
                );
            });
    }
}
