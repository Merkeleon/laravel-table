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
use Opis\Closure\SerializableClosure;

class CallbackFilter extends Filter
{
    protected $viewPath = 'filters.callback';
    /** @var SerializableClosure $callback */
    protected $callback = null;

    protected function prepare()
    {
        $this->value = request('f_' . str_replace('.', '_', $this->name));
    }

    public function applyFilter($dataSource)
    {
        if (is_callable($this->callback))
        {
            $this->callback->getClosure()
                           ->call($this, $dataSource, $this->value);
        }

        return $dataSource;
    }

    public function setCallback(Closure $callback)
    {
        $callback = new SerializableClosure($callback);

        $this->callback = $callback;

        return $this;
    }
}