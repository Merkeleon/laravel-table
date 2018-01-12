<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 07.11.16
 * Time: 15:24
 */

namespace Merkeleon\Table\Total;

use Merkeleon\Table\Total;
use Closure;

class CallbackTotal extends Total
{
    /** @var Closure $callback */
    protected $callback = null;
    protected $type     = 'callback';

    public function get($model)
    {
        if (is_callable($this->callback))
        {
            return $this->callback->call($this, $model);
        }

        return null;
    }

    public function setCallback(Closure $callback)
    {
        $this->callback = $callback;

        return $this;
    }
}