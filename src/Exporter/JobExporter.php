<?php

namespace Merkeleon\Table\Exporter;


use Merkeleon\Table\Exporter;
use Merkeleon\Table\Jobs\AbstractExporterJob;

class JobExporter extends Exporter
{
    /** @var $job AbstractExporterJob */
    protected $job;
    protected $filters;
    protected $orderField;
    protected $orderDirection;
    const IS_TARGET_BLANK = false;

    public function __construct(AbstractExporterJob $job = null)
    {
        $this->job = $job;
    }

    public function export($model)
    {
        if ($this->job)
        {
            $this->job->setFilters($this->filters);
            $this->job->setOrder($this->orderField, $this->orderDirection);
            dispatch($this->job);
        }
        return redirect()->back();
    }

    public function setJob(AbstractExporterJob $job)
    {
        $this->job = $job;

        return $this;
    }

    public function getJob()
    {
        return $this->job;
    }

    public function setFilters($filters)
    {
        $this->filters = $filters;

        return $this;
    }

    public function setOrder($orderField, $orderDirection)
    {
        $this->orderField = $orderField;
        $this->orderDirection = $orderDirection;

        return $this;
    }

    public function getFilters()
    {
        return $this->filters;
    }
}