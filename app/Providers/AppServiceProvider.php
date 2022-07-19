<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        # Snippet Extended From This Package: https://github.com/amsoell/laravel-csv-helpers
        \Illuminate\Support\Collection::macro('toCSV', function (
            array $columns = [],
            string $file = null,
            string $disk = null,
            bool $withHeaders = true,
        ) {
            $file = Str::of($file)->beforeLast('.csv')
                ->append('-')->append(now()->timestamp)->append('.csv');

            $csv = fopen('php://temp/maxmemory:' . (5 * 1024 * 1024), 'r+');

            if ($withHeaders) {
                fputcsv($csv, $columns);
            }

            $this->each(function ($item) use ($columns, $csv) {
                foreach ($columns as $column) {
                    $row[$column] = $item instanceof \Illuminate\Database\Eloquent\Model
                        ? $item->{$column}
                        : Arr::get($item, $column);
                }

                fputcsv($csv, $row);
            });

            rewind($csv);

            Storage::disk($disk ?? env('FILESYSTEM_DRIVER'))->put($file, stream_get_contents($csv));

            return $file;
        });
    }
}
