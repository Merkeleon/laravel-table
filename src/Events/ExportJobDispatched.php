<?php


namespace Merkeleon\Table\Events;


use Merkeleon\Table\Jobs\AbstractExporterJob;

class ExportJobDispatched
{
    /**
     * @var AbstractExporterJob
     */
    private $job;

    /**
     * ExportJobDispatched constructor.
     * @param AbstractExporterJob $job
     */
    public function __construct(AbstractExporterJob $job)
    {
        $this->job = $job;
    }

    /**
     * @return AbstractExporterJob
     */
    public function getJob()
    {
        return $this->job;
    }
}
