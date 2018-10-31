<?php

namespace Merkeleon\Table;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Merkeleon\ElasticReader\Elastic\SearchModel as ElasticSearchModel;

abstract class Filter
{

    protected $name;
    protected $params;
    protected $label;
    protected $theme      = 'default';
    protected $value;
    protected $viewPath;
    protected $attributes = [];
    protected $validators = '';
    protected $error;
    protected $cast       = null;

    public static function make($type, $name)
    {
        if ($type instanceof Filter)
        {
            return $type;
        }
        $params = [];
        if (str_contains($type, '|'))
        {
            list ($type, $paramString) = explode('|', $type, 2);
            $paramPairs = explode('|', $paramString);
            foreach ($paramPairs as $param)
            {
                list($key, $valueString) = explode(':', $param);
                $params[$key] = str_contains($valueString, ',') ? explode(',', $valueString) : $valueString;
            }
        }

        $className = 'Merkeleon\Table\Filter\\' . ucfirst(camel_case($type . 'Filter'));

        $filter = self::createFilter($name, $params, $className);

        return $filter;
    }

    protected static function createFilter($name, $params, $className)
    {
        $reflectionClass = new \ReflectionClass($className);
        $preparedParams  = [
            'name'   => $name,
            'params' => $params,
        ];

        return $reflectionClass->newInstanceArgs($preparedParams);
    }

    protected static function exportParameterValue($params, \ReflectionParameter $parameter)
    {
        if ($value = array_get($params, $parameter->getName()))
        {
            return $value;
        }
        if ($parameter->isDefaultValueAvailable())
        {
            return $parameter->getDefaultValue();
        }
        $declaringClass = $parameter->getDeclaringClass();
        if ($declaringClass)
        {
            throw new \InvalidArgumentException(sprintf("Argument \"%s\" for filter \"%s\" is required.", $parameter->getName(), $declaringClass->getName()));
        }
        else
        {
            throw new \InvalidArgumentException(sprintf("Argument \"%s\" is required.", $parameter->getName()));
        }
    }

    public function __construct($name, $params = [])
    {
        $this->name($name);
        if (array_has($params, 'label'))
        {
            $this->label(array_get($params, 'label'));
        }
        $this->params($params);
        $this->prepare();
        $this->prepareCast();
    }

    protected abstract function prepare();

    protected function prepareCast()
    {
        if (!blank($this->value) && in_array($this->cast, ['int', 'integer']))
        {
            $this->value = (int)$this->value;
        }

        if (!blank($this->value) && in_array($this->cast, ['str', 'string']))
        {
            $this->value = (string)$this->value;
        }
    }

    public function applyFilter($dataSource)
    {
        if (blank($this->value))
        {
            return $dataSource;
        }

        if ($dataSource instanceof Builder || $dataSource instanceof Relation)
        {
            return $this->applyEloquentFilter($dataSource);
        }

        if ($dataSource instanceof Collection)
        {
            return $this->applyCollectionFilter($dataSource);
        }

        if ($dataSource instanceof ElasticSearchModel)
        {
            return $this->applyElasticSearchFilter($dataSource);
        }

        return $dataSource;
    }

    public function name($name)
    {
        $this->name = $name;

        return $this;
    }

    public function params($params)
    {
        if (($cast = array_get($params, 'cast')))
        {
            $this->cast = $cast;
        }

        $this->params = $params;

        return $this;
    }

    public function label($label)
    {
        if ($label)
        {
            $this->label = $label;
        }

        return $this;
    }

    public function validators($validators)
    {
        $this->validators = $validators;

        return $this;
    }

    public function theme($theme)
    {
        $this->theme = $theme;

        return $this;
    }

    public function isActive()
    {
        if (is_array($this->value))
        {
            $value = array_filter($this->value);

            return count($value) ? true : false;
        }

        return blank($this->value) ? false : true;
    }

    public function viewPath($viewPath)
    {
        $this->viewPath = $viewPath;

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setDefaultValue($value)
    {
        if (!$this->value)
        {
            $this->value = $value;
        }

        return $this;
    }

    protected function preparedName()
    {
        return str_replace('.', '_', $this->name);
    }

    public function attributes($attributes = [])
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function validate()
    {
        if (!$this->value)
        {
            return true;
        }

        $key = 'f_' . $this->name;

        $validator = validator(request()->all(), [
            $key => $this->validators,
        ], [], [
            $key => $this->label
        ]);

        if ($validator->fails())
        {
            $errors = $validator->errors()
                                ->toArray();

            $this->error = $errors['f_' . $this->name][0] ?? null;

            return false;
        }

        return true;
    }

    public function render()
    {
        return view('table::' . $this->theme . '.' . $this->viewPath, [
            'name'       => $this->preparedName(),
            'label'      => $this->label,
            'value'      => request()->get('f_' . $this->name),
            'attributes' => $this->attributes,
            'error'      => $this->error,
        ]);
    }
}