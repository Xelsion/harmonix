<?php

namespace lib\core\resolver;

use lib\core\ClassManager;
use lib\exceptions\SystemException;
use ReflectionClass;
use ReflectionException;

/**
 * Tries to get an Instance of the given namespace.
 * checks the class behind the namespace and if there are any dependencies required to create
 * and instance of it. If there are dependencies it uses the ClassManager to resolve them recursively.
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class ClassResolver {

    /**
     * The class constructor
     *
     * @param ClassManager $cm
     * @param string $namespace
     * @param array $args
     */
    public function __construct(protected ClassManager $cm, protected string $namespace, protected array $args = []) {
    }

    /**
     * Tries to build an instance of the current namespace nad returns it.
     *
     * @return object
     *
     * @throws ReflectionException
     * @throws SystemException
     */
    public function getInstance(): object {
        // check for container entry
        if( $this->cm->has($this->namespace) ) {
            $binding = $this->cm->get($this->namespace);

            // return if there is a container instance / singleton
            if( is_object($binding) ) {
                return $binding;
            }
            // sets the namespace to the bound container namespace
            $this->namespace = $binding;
        }
        // create a reflection class
        $refClass = new ReflectionClass($this->namespace);

        // get the constructor
        $constructor = $refClass->getConstructor();

        // check constructor exists and is accessible
        if( $constructor && $constructor->isPublic() ) {
            // check constructor has parameters and resolve them
            if( count($constructor->getParameters()) > 0 ) {
                $argumentResolver = new ParameterResolver($this->cm, $constructor->getParameters(), $this->args);
                // resolve the constructor arguments
                $this->args = $argumentResolver->getArguments();
            }
            // create the new instance with the constructor arguments
            return $refClass->newInstanceArgs($this->args);
        }
        // no arguments so create the new instance without calling the constructor
        return $refClass->newInstanceWithoutConstructor();
    }

}