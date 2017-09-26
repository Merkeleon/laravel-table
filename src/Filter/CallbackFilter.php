<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 07.11.16
 * Time: 15:24
 */

namespace Merkeleon\Table\Filter;


use Closure;
use Merkeleon\Table\Filter;

class CallbackFilter extends Filter
{
    protected $viewPath = 'filters.callback';
    /** @var Closure $callback */
    protected $callback = null;

    protected function prepare()
    {
        $this->value = request('f_' . str_replace('.', '_', $this->name));
    }

    public function applyFilter($model)
    {
        if (is_callable($this->callback))
        {
            $this->callback->call($this, $model, $this->value);
        }

        return $model;
    }

    public function setCallback(Closure $callback)
    {
        $this->callback = $callback;

        return $this;
    }
}