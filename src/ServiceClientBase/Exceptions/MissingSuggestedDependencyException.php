<?php
namespace Cego\ServiceClientBase\Exceptions;

use Exception;

class MissingSuggestedDependencyException extends Exception
{
    /**
     * MissingDependencyException constructor.
     *
     * @param string $feature
     * @param string $dependency
     */
    public function __construct(string $feature, string $dependency)
    {
        $message = sprintf('To use the feature "%s" you need to install the dependency "%s" first!', $feature, $dependency);

        parent::__construct($message);
    }
}
