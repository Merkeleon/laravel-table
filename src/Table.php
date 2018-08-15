<?php

namespace Merkeleon\Table;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Merkeleon\Table\Exporter\JobExporter;

class Table
{

    protected $columns          = [];
    protected $sortables        = [];
    protected $filters          = [];
    protected $filterCallback   = null;
    protected $exporters        = [];
    protected $dataSource;
    protected $theme            = 'default';
    protected $view             = 'table::default.table';
    protected $rows;
    protected $pagination;
    protected $itemsPerPage     = 10;
    protected $orderField       = 'id';
    protected $orderDirection   = 'asc';
    protected $filtersAreActive = false;
    protected $actions          = [];
    protected $totals           = [];
    protected $preparedTotals   = [];

    public static function from($model)
    {
        return new static($model);
    }

    public function __construct($dataSource = null)
    {
        $this->dataSource = $dataSource;
    }

    public function view($viewPath)
    {
        $this->view = $viewPath;

        return $this;
    }

    public function columns($columns = null)
    {
        if (is_null($columns))
        {
            return $this->columns;
        }

        $this->columns = $columns;

        return $this;
    }

    public function sortables($sortables = [])
    {
        $this->sortables = $sortables;

        return $this;
    }

    public function filters($filters = null)
    {
        if (is_null($filters))
        {
            return $this->filters;
        }

        $preparedFilters = [];
        foreach ($filters as $name => $type)
        {
            if ($type instanceof Filter)
            {
                $preparedFilters[$name] = $type;
            }
            else
            {
                $filter = Filter::make($type, $name);
                $filter->label(array_get($this->columns, $name))
                       ->theme($this->theme);

                $preparedFilters[$name] = $filter;
            }
        }

        $this->filters = $preparedFilters;

        return $this;
    }

    public function filterCallback($callback)
    {
        $this->filterCallback = $callback;

        return $this;
    }

    public function exporters($exporters = [])
    {
        $this->exporters = $exporters;

        return $this;
    }

    public function totals($totals = [])
    {
        $this->totals = $totals;

        return $this;
    }

    public function orderBy($field, $direction = 'asc')
    {
        $this->orderField     = $field;
        $this->orderDirection = $direction;

        return $this;
    }

    public function paginate($itemsPerPage)
    {
        $this->itemsPerPage = $itemsPerPage;

        return $this;
    }

    protected function prepareDataSource()
    {
        $this->filterDataSourceResults($this->dataSource);
        $this->sortDataSourceResults($this->dataSource);

        $this->prepareExporters();
        $this->prepareTotals($this->dataSource);

        $result = $this->dataSource->paginate($this->itemsPerPage);

        $this->rows       = $result;
        $this->pagination = $result->appends(request()->all());
    }

    protected function prepareExporters()
    {
        $preparedExporters = [];
        foreach ($this->exporters as $key => $exporter)
        {
            if (is_numeric($key))
            {
                $preparedExporters[$exporter] = Exporter::make($exporter, array_keys($this->columns));
            }
            else
            {
                $preparedExporters[$key] = $exporter;
                if ($exporter instanceof JobExporter)
                {
                    $exporter->setFilters($this->filters);
                    $exporter->setOrder($this->orderField, $this->orderDirection);
                }
            }
        }

        $this->exporters = $preparedExporters;

        if (($exportType = request('export_to')) && ($exporter = array_get($this->exporters, $exportType)))
        {
            $exporter->export($this->model);
        }
    }

    protected function prepareTotals()
    {
        $totals = [];
        foreach ($this->totals as $name => $type)
        {
            $total         = Total::make($type, $name);
            $totals[$name] = [
                'total' => $total->get(clone $this->dataSource),
                'type'  => $total->getType()
            ];
        }

        $this->preparedTotals = $totals;

        return $totals;
    }

    protected function prepareQuery()
    {
        $filters = [];
        foreach ($this->filters as $name => $type)
        {
            $filter = Filter::make($type, $name);
            $filter->label(array_get($this->columns, $name))
                   ->theme($this->theme);

            $filters[$name] = $filter;
        }

        $this->preparedFilters = $filters;

        return $filters;
    }

    protected function filterDataSourceResults($model)
    {
        foreach ($this->filters as $filter)
        {
            $this->dataSource = $filter->applyFilter($this->dataSource);
            if ($filter->isActive())
            {
                $this->filtersAreActive = true;
            }
        }

        if (is_callable($callback = $this->filterCallback))
        {
            $this->dataSource = call_user_func($callback, $this->dataSource);
        }
    }

    protected function sortDataSourceResults($dataSource)
    {
        if ($dataSource instanceof Builder || $dataSource instanceof Relation)
        {
            $this->dataSource = $dataSource->orderBy($this->orderField, $this->orderDirection);
        }
        elseif ($dataSource instanceof Collection)
        {
            if ($this->orderDirection == 'asc')
            {
                $this->dataSource = $dataSource->sortBy($this->orderField);
            }
            else
            {
                $this->dataSource = $dataSource->sortByDesc($this->orderField);
            }
        }
        else
        {
            throw new Exception('Not supported dataSource');
        }

        return $this->dataSource;
    }

    public function row($viewPath)
    {
        $this->rowViewPath = $viewPath;

        return $this;
    }

    protected function setupTable()
    {
        $orderField     = request('orderField', $this->orderField);
        $orderDirection = strtolower(request('orderDirection', $this->orderDirection));
        if (in_array($orderField, $this->sortables) && in_array($orderDirection, ['asc', 'desc']))
        {
            $this->orderField     = $orderField;
            $this->orderDirection = $orderDirection;
        }

        return $this;
    }

    protected function preparedView()
    {
        return view($this->view, [
            'columns'          => $this->columns,
            'sortables'        => $this->sortables,
            'rows'             => $this->rows,
            'pagination'       => $this->pagination,
            'rowViewPath'      => $this->rowViewPath,
            'orderField'       => $this->orderField,
            'orderDirection'   => $this->orderDirection,
            'filters'          => $this->filters,
            'filtersAreActive' => $this->filtersAreActive,
            'exporters'        => $this->exporters,
            'totals'           => $this->preparedTotals
        ]);
    }

    public function render()
    {
        $this->setupTable();
        $this->prepareDataSource();

        return $this->preparedView()
                    ->render();
    }

}
