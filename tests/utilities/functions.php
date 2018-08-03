<?php

use Imanghafoori\HeyMan\Facades\HeyMan;

function create($class, $attributes = [], $times = null)
{
    return Heyman::turnOff()->eloquentChecks(function () use ($class, $times, $attributes) {
        return factory($class, $times)->create($attributes);
    });
}

function make($class, $attributes = [], $times = null)
{
    return Heyman::turnOff()->eloquentChecks(function () use ($class, $times, $attributes) {
        return factory($class, $times)->make($attributes);
    });
}
