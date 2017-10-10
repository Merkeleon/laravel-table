<?php

namespace Merkeleon\Table\Filter;

use Merkeleon\Table\Filter;


class RangeFilter extends Filter
{

    protected $viewPath = 'filters.range';
    protected $multiplier = 1;

    public function params($params) {
        if (($multiplier = array_get($params, 'multiplier')) !== null) {
            $this->multiplier = $multiplier;
        }

        return parent::params($params);
    }

    protected function prepare() {
        $value = request('f_'.$this->name);
        if ($from = array_get($value, 'from'))
        {
            $this->value['from'] = $from;
        }
        if ($to = array_get($value, 'to'))
        {
            $this->value['to'] = $to;
        }
    }

    public function applyFilter($model) {

        if($from = array_get($this->value, 'from')) {
            $model = $model->where($model->getModel()->getTable().'.'.$this->name, '>=', $from * $this->multiplier);
        }

        if($to = array_get($this->value, 'to')) {
            $model = $model->where($model->getModel()->getTable().'.'.$this->name, '<=', $to * $this->multiplier);
        }

        return $model;
    }

}