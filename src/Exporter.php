<?php

namespace Merkeleon\Table;

abstract class Exporter
{
    protected $columns = [];
    protected $type = '';
    protected $label;
    const IS_TARGET_BLANK = true;

    public static function make($type, $columns)
    {
        $exporterName = 'Merkeleon\Table\Exporter\\' . ucfirst(camel_case($type . 'Exporter'));
        $exporter = new $exporterName();
        $exporter
            ->columns($columns)
            ->setLabel(trans('table::button.export-to', ['format' => strtoupper($type)]));

        return $exporter;
    }

    abstract public function export($model);

    public function isTargetBlank()
    {
        return static::IS_TARGET_BLANK;
    }

    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function columns($columns = []) {
        $this->columns = $columns;

        return $this;
    }

}
