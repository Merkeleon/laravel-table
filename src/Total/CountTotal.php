<?php

namespace Merkeleon\Table\Total;

use Merkeleon\Table\Total;
use DB;

class CountTotal extends Total
{
    public function get($model)
    {
        $result = $model->addSelect(DB::raw('COUNT(' . $model->getModel()->getTable().'.'.$this->column . ') as total'))->first();

        return array_get($result->getAttributes(), 'total');
    }
}
