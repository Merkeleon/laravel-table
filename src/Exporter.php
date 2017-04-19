<?php

namespace Merkeleon\Table;

class Exporter
{
    protected $columns = [];

    public static function make($type, $columns)
    {
        $exporterName = 'Merkeleon\Table\Exporter\\' . ucfirst(camel_case($type . 'Exporter'));
        $exporter = new $exporterName();
        $exporter->columns($columns);

        return $exporter;
    }

    public function columns($columns = []) {
        $this->columns = $columns;

        return $this;
    }

}