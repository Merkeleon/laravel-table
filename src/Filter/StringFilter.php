<?php

namespace Merkeleon\Table\Filter;

use Merkeleon\Table\Filter;
use Merkeleon\ElasticReader\Elastic\SearchModel as ElasticSearchModel;

class StringFilter extends Filter
{

    protected $viewPath       = 'filters.string';
    protected $isStrict       = false;
    protected $searchInObject = false;

    public function params($params)
    {
        if (($isStrict = array_get($params, 'strict')))
        {
            $this->isStrict = $isStrict;
        }

        if (($searchInObject = array_get($params, 'search_in_object')))
        {
            $this->searchInObject = $searchInObject;
        }

        return parent::params($params);
    }

    protected function prepare()
    {
        $this->value = trim(request('f_' . $this->name));
    }

    protected function applyEloquentFilter($dataSource)
    {
        if (blank($this->value))
        {
            return $dataSource;
        }

        if ($this->isStrict)
        {
            return $dataSource->where($dataSource->getModel()
                                                 ->getTable() . '.' . $this->name, '=', $this->value);
        }

        return $dataSource->where($dataSource->getModel()
                                             ->getTable() . '.' . $this->name, 'like', '%' . $this->value . '%');

    }

    protected function applyCollectionFilter($dataSource)
    {
        if (blank($this->value))
        {
            return $dataSource;
        }

        return $dataSource->filter(function ($item) {
            if ($this->isStrict)
            {
                return $item->{$this->name} == $this->value;
            }

            return str_contains($item->{$this->name}, $this->value);

        });
    }

    protected function applyElasticSearchFilter(ElasticSearchModel $dataSource)
    {
        if (blank($this->value))
        {
            return $dataSource;
        }

        if ($this->isStrict)
        {
            return $dataSource->query()
                       ->where($this->name, $this->value);
        }

        $name = $this->searchInObject ? null : $this->name;

        return $dataSource->query()
                   ->matchSubString($this->value, $name);

    }
}
