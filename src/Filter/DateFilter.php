<?php

namespace Merkeleon\Table\Filter;

use Carbon\Carbon;
use Merkeleon\ElasticReader\Elastic\SearchModel as ElasticSearchModel;
use Merkeleon\Table\Filter;
use DB;


class DateFilter extends Filter
{

    protected $viewPath   = 'filters.date';
    protected $validators = 'nullable|date';
    protected $dateFormat = Carbon::DEFAULT_TO_STRING_FORMAT;

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
        if ($dateFormat = array_get($value, 'date_format'))
        {
            $this->dateFormat = $dateFormat;
        }
    }

    protected function applyEloquentFilter($dataSource)
    {
        if ($preparedFrom = $this->getPreparedFrom())
        {
            $dataSource = $dataSource->where(DB::raw('DATE_FORMAT(' . $dataSource->getModel()
                                                                                 ->getTable() . '.' . $this->name . ', "%Y-%m-%d %H:%i:%s")'), '>=', $preparedFrom);
        }

        if ($preparedTo = $this->getPreparedTo())
        {
            $dataSource = $dataSource->where(DB::raw('DATE_FORMAT(' . $dataSource->getModel()
                                                                                 ->getTable() . '.' . $this->name . ', "%Y-%m-%d %H:%i:%s")'), '<=', $preparedTo);
        }

        return $dataSource;
    }

    protected function applyCollectionFilter($dataSource)
    {
        if ($from = $this->getPreparedFrom())
        {
            $dataSource = $dataSource->filter(function ($item, $key) use ($from) {
                return strtotime($item->{$this->name}) >= strtotime($from);
            });
        }

        if ($to = $this->getPreparedTo())
        {
            $dataSource = $dataSource->filter(function ($item, $key) use ($to) {
                return strtotime($item->{$this->name}) <= strtotime($to);
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

    protected function applyElasticSearchFilter(ElasticSearchModel $dataSource)
    {
        $from = $this->getPreparedFrom(get_class($dataSource)::$dateTimeFormat);
        $to   = $this->getPreparedTo(get_class($dataSource)::$dateTimeFormat);

        $dataSource->query()
                   ->range($this->name, $from, $to);

        return $dataSource;
    }

    protected function getPreparedFrom($format = 'Y-m-d H:i:s')
    {
        $from = array_get($this->value, 'from');

        if (!$from)
        {
            return null;
        }

        return $this->prepareDate($from . '00:00:00', $format);
    }

    protected function getPreparedTo($format = 'Y-m-d H:i:s')
    {
        $to = array_get($this->value, 'to');

        if (!$to)
        {
            return null;
        }

        return $this->prepareDate($to . '23:59:59', $format);
    }

    protected function prepareDate($value, $format)
    {
        if (!$value)
        {
            return null;
        }

        return (new Carbon($value, config('view.timezone')))->timezone(config('app.timezone'))->format($format);
    }
}