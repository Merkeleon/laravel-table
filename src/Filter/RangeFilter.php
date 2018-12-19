<?php

namespace Merkeleon\Table\Filter;

use Merkeleon\ElasticReader\Elastic\SearchModel as ElasticSearchModel;
use Merkeleon\Log\LogRepository;
use Merkeleon\Table\Filter;


class RangeFilter extends Filter
{

    protected $viewPath   = 'filters.range';
    protected $multiplier = 1;
    protected $validators = 'nullable|numeric';

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

    protected function applyEloquentFilter($dataSource)
    {
        if ($from = array_get($this->value, 'from'))
        {
            $dataSource = $dataSource->where($dataSource->getModel()
                                                        ->getTable() . '.' . $this->name, '>=', $from * $this->multiplier);
        }

        if ($to = array_get($this->value, 'to'))
        {
            $dataSource = $dataSource->where($dataSource->getModel()
                                                        ->getTable() . '.' . $this->name, '<=', $to * $this->multiplier);
        }

        return $dataSource;
    }

    protected function applyCollectionFilter($dataSource)
    {
        if ($from = array_get($this->value, 'from'))
        {
            $dataSource = $dataSource->filter(function ($item, $key) use ($from) {
                return strtotime($item->{$this->name}) >= $from * $this->multiplier;
            });
        }

        if ($to = array_get($this->value, 'to'))
        {
            $dataSource = $dataSource->filter(function ($item, $key) use ($to) {
                return strtotime($item->{$this->name}) <= $to * $this->multiplier;
            });
        }

        return $dataSource;
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

    protected function applyElasticSearchFilter(ElasticSearchModel $dataSource)
    {
        $from = $this->prepareRangeValue(array_get($this->value, 'from'));
        $to   = $this->prepareRangeValue(array_get($this->value, 'to'));

        $dataSource->query()
                   ->range($this->name, $from, $to);

        return $dataSource;
    }

    protected function applyLogRepositoryFilter(LogRepository $dataSource)
    {
        $from = $this->prepareRangeValue(array_get($this->value, 'from'));
        $to   = $this->prepareRangeValue(array_get($this->value, 'to'));

        $dataSource->range($this->name, $from, $to);

        return $dataSource;
    }

    protected function prepareRangeValue($value)
    {
        if (!$value)
        {
            return null;
        }

        return $value * $this->multiplier;
    }
}