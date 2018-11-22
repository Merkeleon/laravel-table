<?php


namespace Merkeleon\Table\Filter;

use Merkeleon\ElasticReader\Elastic\SearchModel as ElasticSearchModel;


class MultiSelectFilter extends SelectFilter
{
    protected $options  = [];
    protected $viewPath = 'filters.multi_select';

    protected function applyEloquentFilter($dataSource)
    {
        if ($this->value)
        {
            return $dataSource->whereIn($this->name, $this->value);
        }

        return $dataSource;
    }

    protected function applyCollectionFilter($dataSource)
    {
        if ($this->value)
        {
            return $dataSource->filter(function ($item) {
                return in_array($item->{$this->name}, $this->value);
            });
        }

        return $dataSource;
    }

    protected function applyElasticSearchFilter(ElasticSearchModel $dataSource)
    {
        if ($this->value) {
            $dataSource->query()
                       ->whereIn($this->name, $this->value);
        }

        return $dataSource;
    }
}
