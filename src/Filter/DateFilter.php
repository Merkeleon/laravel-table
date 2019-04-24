<?php

namespace Merkeleon\Table\Filter;

use DB;
use Merkeleon\Table\Filter;


class DateFilter extends Filter
{

    protected $viewPath   = 'filters.date';
    protected $validators = 'nullable|date';

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
            $model = $model->where(DB::raw('DATE_FORMAT(' . $model->getModel()
                                                                  ->getTable() . '.' . $this->name . ', "%Y-%m-%d")'), '>=', date('Y-m-d', strtotime($from)));
        }

        if ($to = array_get($this->value, 'to'))
        {
            $model = $model->where(DB::raw('DATE_FORMAT(' . $model->getModel()
                                                                  ->getTable() . '.' . $this->name . ', "%Y-%m-%d")'), '<=', date('Y-m-d', strtotime($to)));
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
            $keyFrom => $this->label . ' ' . trans('table::table.filter.date.from'),
            $keyTo   => $this->label . ' ' . trans('table::table.filter.date.to'),
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