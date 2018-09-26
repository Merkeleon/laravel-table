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
        $this->value = request('f_' . $this->name);
    }

    protected function applyEloquentFilter($dataSource)
    {
        if ($this->value)
        {
            if ($this->isStrict)
            {
                $dataSource = $dataSource->where($dataSource->getModel()
                                                            ->getTable() . '.' . $this->name, '=', $this->value);
            }
            else
            {
                $dataSource = $dataSource->where($dataSource->getModel()
                                                            ->getTable() . '.' . $this->name, 'like', '%' . $this->value . '%');
            }
        }

        return $dataSource;
    }

    protected function applyCollectionFilter($dataSource)
    {
        if ($this->value)
        {
            return $dataSource->filter(function ($item) {
                if ($this->isStrict)
                {
                    return $item->{$this->name} == $this->value;
                }
                else
                {
                    return str_contains($item->{$this->name}, $this->value);
                }
            });
        }

        return $dataSource;
    }

    protected function applyElasticSearchFilter(ElasticSearchModel $dataSource)
    {
        if ($this->isStrict)
        {
            $dataSource->query()
                       ->term($this->name, $this->value);
        }
        else
        {
            $name = $this->searchInObject ? $this->name . '.*' : $this->name;
            $dataSource->query()
                       ->matchSubString($name, $this->value);
        }

        return $dataSource;
    }
}
