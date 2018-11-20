<?php

namespace Merkeleon\Table\Filter;

use Merkeleon\Table\Filter;
use Merkeleon\ElasticReader\Elastic\SearchModel as ElasticSearchModel;


class SelectFilter extends Filter
{

    protected $options  = [];
    protected $viewPath = 'filters.select';
    protected $emptyFirst = false;

    public function params($params)
    {
        return parent::params($params);
    }

    protected function applyEloquentFilter($dataSource)
    {
        if ($this->value)
        {
            $relations = explode('.', $this->name);

            if (count($relations) == 1)
            {
                $field = array_first($relations);

                return $dataSource->where($field, '=', $this->value);
            }
            else
            {
                $relation = array_shift($relations);

                return $dataSource->whereHas($relation, function ($query) use ($relations) {
                    return $this->callbackFilter($query, $relations);
                });
            }
        }

        return $dataSource;
    }

    protected function applyCollectionFilter($dataSource)
    {
        if ($this->value)
        {
            return $dataSource->filter(function ($item) {
                return $item->{$this->name} == $this->value;
            });
        }

        return $dataSource;
    }

    protected function prepare()
    {
        $this->value = request()->input('f_' . $this->preparedName());
    }

    public function options($options)
    {
        $this->options = $options;

        return $this;
    }

    public function addEmptyFirst($trans = '')
    {
        $this->emptyFirst = $trans;
        
        return $this;
    }

    public function render()
    {
        if ($this->emptyFirst !== false)
        {
            $this->options = ['' => $this->emptyFirst] + $this->options;
        }

        $view = parent::render();

        return $view->with('options', $this->options);
    }

    protected function applyElasticSearchFilter(ElasticSearchModel $dataSource)
    {
        $dataSource->query()
                   ->where($this->name, $this->value);

        return $dataSource;
    }
}
