<?php

namespace Merkeleon\Table\Filter;

use Merkeleon\Table\Filter;


class SelectFilter extends Filter
{

    protected $options = [];
    protected $viewPath = 'filters.select';

    public function applyFilter($model)
    {
        if ($this->value) {
            $model = $model->where($model->getModel()->getTable().'.'.$this->name, $this->value);
        }
        return $model;
    }

    protected function prepare()
    {
        $this->value = request('f_' . $this->name);
    }

    public function options($options)
    {
        $this->options = $options;
        return $this;
    }

    public function render()
    {
        return view('table::' . $this->theme . '.' . $this->viewPath, [
            'name' => $this->name,
            'label' => $this->label,
            'value' => $this->value,
            'options' => $this->options,
        ]);
    }
}