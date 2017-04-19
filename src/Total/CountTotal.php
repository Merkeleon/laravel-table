<?php

namespace Merkeleon\Table\Total;

use Merkeleon\Table\Total;
use DB;

class CountTotal extends Total
{
    public function get($model)
    {
        return $model->select(DB::raw('COUNT(' . $model->getModel()->getTable().'.'.$this->column . ') as total'))->first()->total;
    }
}