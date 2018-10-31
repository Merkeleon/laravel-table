<?php


namespace Merkeleon\Table;


use Illuminate\Container\Container;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection as LaravelCollection;

class Collection extends LaravelCollection
{
    public function paginate($perPage = 15)
    {
        $page = Paginator::resolveCurrentPage();

        return $this->paginator($this->forPage($page, $perPage), $this->count(), $perPage, $page, [
            'path'     => Paginator::resolveCurrentPath(),
            'pageName' => 'page',
        ]);
    }

    protected function paginator($items, $total, $perPage, $currentPage, $options)
    {
        return Container::getInstance()
                        ->makeWith(LengthAwarePaginator::class, compact(
                            'items', 'total', 'perPage', 'currentPage', 'options'
                        ));
    }
}