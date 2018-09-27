<?php

if (!function_exists('array_undot'))
{
    function array_undot($array)
    {
        $result = [];
        foreach ($array as $key => $value)
        {
            array_set($result, $key, $value);
        }

        return $result;
    }
}