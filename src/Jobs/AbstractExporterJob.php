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
    protected $orderField;
    protected $orderDirection;

    /**
     * Create a new job instance.
     *
     * @param array|null $filters
     * @param null $orderField
     * @param null $orderDirection
     */
    public function __construct(array $filters = null, $orderField = null, $orderDirection = null)
    {
        $this->filters = $filters;
        $this->orderField = $orderField;
        $this->orderDirection = $orderDirection;
    }

    public function setFilters($filters)
    {
        $this->filters = $filters;

        return $this;
    }

    public function setOrder($field, $direction)
    {
        $this->orderField = $field;
        $this->orderDirection = strtolower($direction) === 'asc' ? 'asc' : 'desc';

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

    protected function applyOrder($orm)
    {
        if ($this->orderField && $this->orderDirection)
        {
            $orm->orderBy($this->orderField, $this->orderDirection);
        }
    }
}
