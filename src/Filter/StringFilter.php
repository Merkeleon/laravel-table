<?php

namespace Merkeleon\Table\Filter;

use Merkeleon\Table\Filter;


class StringFilter extends Filter
{

    protected $viewPath = 'filters.string';
    protected $isStrict = false;
    protected $cast     = false;

    public function params($params)
    {
        if (($isStrict = array_get($params, 'strict')))
        {
            $this->isStrict = $isStrict;
        }

        if (($cast = array_get($params, 'cast')))
        {
            $this->cast = $cast;
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
}
