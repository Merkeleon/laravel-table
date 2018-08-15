<?php

namespace Merkeleon\Table\Filter;

use Merkeleon\Table\Filter;


class SelectFilter extends Filter
{

    protected $options  = [];
    protected $viewPath = 'filters.select';
    protected $cast     = null;

    public function params($params)
    {
        if (($cast = array_get($params, 'cast')))
        {
            $this->cast = $cast;
        }

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
                if ($this->cast)
                {
                    if (in_array($this->cast, ['int', 'integer']))
                    {
                        $this->value = (int)$this->value;
                    }

                    if (in_array($this->cast, ['str', 'string']))
                    {
                        $this->value = (string)$this->value;
                    }
                }

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
            //@TODO add relations support
            if ($this->cast)
            {
                if (in_array($this->cast, ['int', 'integer']))
                {
                    $this->value = (int)$this->value;
                }

                if (in_array($this->cast, ['str', 'string']))
                {
                    $this->value = (string)$this->value;
                }
            }

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

    public function render()
    {
        $view = parent::render();

        return $view->with('options', $this->options);
    }
}
