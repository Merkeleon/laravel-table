<?php

namespace Merkeleon\Table\Jobs;


use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

abstract class AbstractExporterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filters;

    /**
     * Create a new job instance.
     *
     * @param array|null $filters
     */
    public function __construct(array $filters = null)
    {
        $this->filters = $filters;
    }

    public function setFilters($filters)
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    abstract public function handle();

    protected function applyFilters($orm)
    {
        foreach ($this->filters as $filter)
        {
            $filter->applyFilter($orm);
        }
    }
}
