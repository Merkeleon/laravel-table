<?php

namespace Merkeleon\Table\Filter;

use Merkeleon\Table\Filter;


class RangeFilter extends Filter
{

    protected $viewPath   = 'filters.range';
    protected $multiplier = 1;
    protected $validators = 'numeric';

    public function params($params)
    {
        if (($multiplier = array_get($params, 'multiplier')) !== null)
        {
            $this->multiplier = $multiplier;
        }

        return parent::params($params);
    }

    protected function prepare()
    {
        $value = request('f_' . $this->name);
        if ($from = array_get($value, 'from'))
        {
            $this->value['from'] = $from;
        }
        if ($to = array_get($value, 'to'))
        {
            $this->value['to'] = $to;
        }
    }

    public function applyFilter($model)
    {

        if ($from = array_get($this->value, 'from'))
        {
            $model = $model->where($model->getModel()
                                         ->getTable() . '.' . $this->name, '>=', $from * $this->multiplier);
        }

        if ($to = array_get($this->value, 'to'))
        {
            $model = $model->where($model->getModel()
                                         ->getTable() . '.' . $this->name, '<=', $to * $this->multiplier);
        }

        return $model;
    }

    public function validate()
    {
        if (!$this->value)
        {
            return true;
        }

        $keyFrom = 'f_' . $this->name . '.from';
        $keyTo   = 'f_' . $this->name . '.to';

        $validator = validator(request()->all(), [
            $keyFrom => $this->validators,
            $keyTo   => $this->validators,
        ], [], [
            $keyFrom => $this->label . ' ' . trans('table::table.filter.range.from'),
            $keyTo   => $this->label . ' ' . trans('table::table.filter.range.to'),
        ]);

        if ($validator->fails())
        {
            $errors = $validator->errors()
                                ->toArray();

            $this->error['from'] = $errors[$keyFrom][0] ?? null;
            $this->error['to']   = $errors[$keyTo][0] ?? null;

            return false;
        }

        return true;
    }

}