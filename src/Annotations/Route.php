<?php

namespace Zeus\Annotations;

/**
 * @Annotation
 * @Target("METHOD")
 */
class Route
{

    /** @var string */
    public $pattern;
}
