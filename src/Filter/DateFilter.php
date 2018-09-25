<?php

namespace Merkeleon\Table\Filter;

use Merkeleon\Table\Filter;
use DB;


class DateFilter extends Filter
{

    protected $viewPath = 'filters.date';

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
        if(!$this->validate()) {
            return $model;
        }

        if ($from = array_get($this->value, 'from')) {
            $model = $model->where(DB::raw('DATE_FORMAT('.$model->getModel()->getTable().'.'.$this->name.', "%Y-%m-%d")'), '>=', date('Y-m-d', strtotime($from)));
        }

        if ($to = array_get($this->value, 'to')) {
            $model = $model->where(DB::raw('DATE_FORMAT('.$model->getModel()->getTable().'.'.$this->name.', "%Y-%m-%d")'), '<=', date('Y-m-d', strtotime($to)));
        }

        return $model;
    }

    protected function validate()
    {
        $validator = validator(request()->all(), [
            'f_' . $this->name . '.from' => 'date',
            'f_' . $this->name . '.to'   => 'date',
        ]);

        if ($validator->fails()) {
            $errors = array_undot($validator->errors()
                                            ->toArray());

            $this->error['from'] = array_get(array_undot($errors), 'f_' . $this->name . '.from.0');
            $this->error['to'] = array_get(array_undot($errors), 'f_' . $this->name . '.to.0');

            return false;
        }

        return true;
    }
}