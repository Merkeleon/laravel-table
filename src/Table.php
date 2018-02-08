<?php

namespace Merkeleon\Table;


use Merkeleon\Table\Exporter\JobExporter;

class Table
{

    protected $columns = [];
    protected $sortables = [];
    protected $filters = [];
    protected $preparedFilters = [];
    protected $filterCallback = null;
    protected $exporters = [];
    protected $batchActions = [];
    protected $model;
    protected $theme;
    protected $view;
    protected $rows;
    protected $pagination;
    protected $itemsPerPage = 10;
    protected $rowViewPath;
    protected $orderField = 'id';
    protected $orderDirection = 'asc';
    protected $filtersAreActive = false;
    protected $actions = [];
    protected $totals = [];
    protected $preparedTotals = [];

    public static function from($model)
    {
        return new static($model);
    }

    public function __construct($model = null)
    {
        $this->theme = config('table.theme');
        $this->view = 'table::' . $this->theme . '.table';
        $this->rowViewPath = 'table::' . $this->theme . '.' . config('table.row');
        $this->model = $model;
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
        $this->filters = $filters;

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

    public function actions($actions = [])
    {
        $this->actions = $actions;

        return $this;
    }

    public function totals($totals = [])
    {
        $this->totals = $totals;

        return $this;
    }

    public function batchActions($batchActions = [])
    {
        $this->batchActions = $batchActions;

        return $this;
    }

    public function orderBy($field, $direction = 'asc')
    {
        $this->orderField = $field;
        $this->orderDirection = $direction;

        return $this;
    }

    public function paginate($itemsPerPage)
    {
        $this->itemsPerPage = $itemsPerPage;

        return $this;
    }

    protected function prepareModelResults()
    {
        $this->filterModelResults($this->model);
        $this->sortModelResults($this->model);
        $this->prepareExporters();
        $this->runBatch();
        $this->prepareTotals($this->model);

        $result = $this->model->paginate($this->itemsPerPage);
        $this->rows = $result;
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
                    $exporter->setFilters($this->preparedFilters);
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

    protected function prepareModelFilters()
    {
        $filters = [];
        foreach ($this->filters as $name => $type) {
            $filter = Filter::make($type, $name);
            $filter->label(array_get($this->columns, $name))
                ->theme($this->theme);

            $filters[$name] = $filter;
        }

        $this->preparedFilters = $filters;

        return $filters;
    }

    protected function prepareTotals()
    {
        $totals = [];
        foreach ($this->totals as $name => $type) {
            $total = Total::make($type, $name);
            $totals[$name] = [
                'total' => $total->get(clone $this->model),
                'type' => $total->getType()
            ];
        }

        $this->preparedTotals = $totals;

        return $totals;
    }

    protected function prepareQuery()
    {
        $filters = [];
        foreach ($this->filters as $name => $type) {
            $filter = Filter::make($type, $name);
            $filter->label(array_get($this->columns, $name))
                ->theme($this->theme);

            $filters[$name] = $filter;
        }

        $this->preparedFilters = $filters;

        return $filters;
    }

    protected function runBatch()
    {
        if (request()->has('batch_action')) {
            if ($action = array_get($this->batchActions, request()->get('batch'))) {
                $query = clone $this->model;
                if ($batchWith = request()->get('batch_with')) {
                    if ($batchWith === 'selected')
                    {
                        $ids = request()->get('b');
                        if (count($ids))
                        {
                            $query->whereIn('id', $ids);
                        }
                        else
                        {
                            return redirect()
                                ->back()
                                ->send();
                        }
                    }
                }
                $action($query);

                return redirect()->back()->send();
            }
        }

        return $this;
    }

    protected function filterModelResults($model)
    {
        $this->prepareModelFilters();
        foreach ($this->preparedFilters as $filter) {
            $this->model = $filter->applyFilter($this->model);
            if ($filter->isActive()) {
                $this->filtersAreActive = true;
            }
        }
        if (is_callable($callback = $this->filterCallback)) {
            $this->model = call_user_func($callback, $this->model);
        }
    }

    protected function sortModelResults($model)
    {
        $this->model = $model->orderBy($this->orderField, $this->orderDirection);

        return $this->model;
    }

    public function row($viewPath)
    {
        $this->rowViewPath = $viewPath;

        return $this;
    }

    protected function setupTable()
    {
        $orderField = request('orderField', $this->orderField);
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
            'filters'          => $this->preparedFilters,
            'filtersAreActive' => $this->filtersAreActive,
            'exporters'        => $this->exporters,
            'actions'          => $this->actions,
            'batchActions'     => $this->batchActions,
            'totals'           => $this->preparedTotals
        ]);
    }

    public function render()
    {
        $this->setupTable();
        $this->prepareModelResults();

        return $this->preparedView()->render();
    }

}
