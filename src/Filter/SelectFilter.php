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

    public function callbackFilter($model, $relations)
    {
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

            return $model->where($field, '=', $this->value);
        }
        else
        {
            $relation = array_shift($relations);

            return $model->whereHas($relation, function ($query) use ($relations) {
                return $this->callbackFilter($query, $relations);
            });
        }
    }

    public function applyFilter($model)
    {
        if ($this->value !== '')
        {
            $relations = explode('.', $this->name);

            return $this->callbackFilter($model, $relations);
        }

        return $model;
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

    public function isActive()
    {
        return $this->value !== '' ? true : false;
    }
}
