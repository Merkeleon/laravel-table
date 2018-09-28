<?php

namespace Merkeleon\Table\Filter;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Merkeleon\Table\Filter;
use DB;


class DateFilter extends Filter
{

    protected $viewPath = 'filters.date';
    protected $validators = 'date';

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

    protected function applyEloquentFilter($dataSource)
    {
        if ($from = array_get($this->value, 'from'))
        {
            $dataSource = $dataSource->where(DB::raw('DATE_FORMAT(' . $dataSource->getModel()
                                                                                 ->getTable() . '.' . $this->name . ', "%Y-%m-%d")'), '>=', date('Y-m-d', strtotime($from)));
        }

        if ($to = array_get($this->value, 'to'))
        {
            $dataSource = $dataSource->where(DB::raw('DATE_FORMAT(' . $dataSource->getModel()
                                                                                 ->getTable() . '.' . $this->name . ', "%Y-%m-%d")'), '<=', date('Y-m-d', strtotime($to)));
        }

        return $dataSource;
    }

    protected function applyCollectionFilter($dataSource)
    {
        if ($from = array_get($this->value, 'from'))
        {
            $dataSource = $dataSource->filter(function ($item, $key) use ($from) {
                return strtotime($item->{$this->name}) >= strtotime($from);
            });
        }

        if ($to = array_get($this->value, 'to'))
        {
            $dataSource = $dataSource->filter(function ($item, $key) use ($to) {
                return strtotime($item->{$this->name}) <= strtotime($to);
            });
        }

        return $dataSource;
    }

    public function validate()
    {
        if (!request()->has('f_' . $this->name)) {
            return true;
        }

        $validator = validator(request()->all(), [
            'f_' . $this->name . '.from' => $this->validators,
            'f_' . $this->name . '.to'   => $this->validators,
        ]);

        if ($validator->fails()) {
            $errors = array_undot($validator->errors()
                                            ->toArray());

            $this->error['from'] = array_get(array_undot($errors), 'f_' . $this->name . '.from.0');
            $this->error['to']   = array_get(array_undot($errors), 'f_' . $this->name . '.to.0');

            return false;
        }

        return true;
    }
}