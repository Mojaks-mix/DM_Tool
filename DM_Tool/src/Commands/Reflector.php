<?php

namespace src\Commands;

trait Reflector{
    protected function getClassMethodParameters(string|null $className = '', string|null $methodName = '', ParameterState $paramState = ParameterState::MANDATORY): array{
        $method = (new \ReflectionClass($className))->getMethod($methodName);
        $parameters = $method->getParameters();
        
        $optionalParams = [];
        $mandatoryParams = [];
        foreach ($parameters as $parameter) {
            if ($parameter->isDefaultValueAvailable()) {
                $optionalParams[] = [$parameter->getName(), $parameter->getType(), $parameter->getDefaultValue()];
            } else {
                $mandatoryParams[] = [$parameter->getName(), $parameter->getType()];
            }
        }

        if($paramState == ParameterState::MANDATORY)
            return $mandatoryParams;
        return $optionalParams;
    }

    protected function checkClassExists(string|null $className = ''): bool{
        return class_exists($className);
    }

    protected function checkMethodInClass(string|null $className = '', string|null $methodName = ''): bool{
        return !(new \ReflectionClass($className))->hasMethod($methodName);
    }

    protected function getClassesAndMethods(string $path = '', string $nameSpace = ''): array{
        $classes = [];
        foreach (glob($path) as $file) {
            $className = $nameSpace . basename($file, '.php');
            $methods = $this->getPublicUserMethods($className);
            $classes[basename($file, '.php')] = $methods;
        }
        return $classes;
    }

    protected function getPublicUserMethods($className): array{
        $class = new \ReflectionClass($className);
        $methods = array_filter($class->getMethods(\ReflectionMethod::IS_PUBLIC), function($method) {
            return !$method->isConstructor() && !$method->isDestructor();
        });

        $filteredMethods = [];
        foreach ($methods as $method) {
            if (!$this->isPhpBuiltInMethod($method->getDeclaringClass()->getName(), $method->getName())) {
                $filteredMethods[] = $method;
            }
        }

        return $filteredMethods;
    }

    protected function isPhpBuiltInMethod($className, $methodName): bool{
        $reflectionMethod = new \ReflectionMethod($className, $methodName);
        return $reflectionMethod->getFileName() === false;
    }
}