<?php

namespace Merkeleon\Table\Filter;

use Merkeleon\Table\Filter;


class StringFilter extends Filter
{

    protected $viewPath = 'filters.string';
    protected $isStrict = false;

    public function params($params)
    {
        if (($isStrict = array_get($params, 'strict')))
        {
            $this->isStrict = $isStrict;
        }

        return parent::params($params);
    }

    protected function prepare()
    {
        $this->value = request('f_' . $this->name);
    }

    public function applyFilter($model)
    {
        if ($this->value)
        {
            if ($this->isStrict)
            {
                $model = $model->where($model->getModel()->getTable() . '.' . $this->name, '=', $this->value);
            }
            else
            {
                $model = $model->where($model->getModel()->getTable() . '.' . $this->name, 'like', '%' . $this->value . '%');
            }
        }

        return $model;
    }

}