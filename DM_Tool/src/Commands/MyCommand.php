<?php

require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\SingleCommandApplication;


class Core extends SingleCommandApplication
{
    protected static $defaultName = 'mycommand';
    protected string|null $command;
    protected string|null $methodName;
    protected string|null $className;
    
    public function __construct(InputInterface $input)
    {
        $this->command = $input->getFirstArgument();
        if(str_contains($this->command ?? "", '.')){
            $parts = explode('.', $this->command);
            if (count($parts) === 2) {
                $this->className = $parts[0];
                $this->methodName = $parts[1];
            }
        }
        else{
            $this->className = $this->command;
            $this->methodName = "";
        }
        
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Dynamic command to call methods inside classes')
            ->addOption('classes', 'c', InputOption::VALUE_NONE, 'Show available classes and their methods')
            ->addOption('methods', 'm', InputOption::VALUE_NONE, 'Show methods of a specific class')
            ->addOption('parameters', 'p', InputOption::VALUE_NONE, 'Show parameters of a specific method')
            ->addArgument('command', InputArgument::OPTIONAL, "Execute a class' method\nFormat: Class.Method -parameter_name=vlaue ...")
            ->setMethodParameters($this->command);
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('classes') || $input->getOption('methods') || $input->getOption('parameters')) {
            $this->handleOptions($input, $output);
        } else {
            $this->handleCommand($input, $output);
        }
        
        return Command::SUCCESS;
    }
    
    protected function handleOptions(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('classes')) {
            $this->showAvailableClasses($output);
        }
        
        if ($input->getOption('methods')) {
            $this->showClassMethods($output);
        }
        
        if ($input->getOption('parameters')) {
            $this->showMethodParameters($output);
        }
    }
    
    protected function handleCommand(InputInterface $input, OutputInterface $output)
    {
        $command = $input->getArgument('command');
        if ($command) {
            // Parse command
            $parts = explode('.', $command);
            if (count($parts) != 2) {
                $this->coustomErrorMessage('Invalid command format. Use: className.methodName', $output);
                return;
            }
            $className = $parts[0];
            $classFullName = 'src\Core\\' . $className;
            $methodName = $parts[1];
            
            // Check if class exists
            if (!class_exists($classFullName)) {
                $this->coustomErrorMessage("Class $className not found", $output);
                return;
            }
            
            // Check if method exists
            $class = new ReflectionClass($classFullName);
            if (!$class->hasMethod($methodName)) {
                $this->coustomErrorMessage("Method $methodName not found in class $className", $output);
                return;
            }
            
            $params = $this->getParameters($class->getMethod($methodName), $input, $output);

            $instance = new $classFullName();

            try {
                $instance->$methodName(...$params);
            } catch (\Throwable $exception) {
                $this->coustomErrorMessage("An error occurred while performing $methodName: " . $exception->getMessage(), $output);
            }

        } else {
            $this->coustomErrorMessage('No command provided', $output);
        }
    }

    protected function showAvailableClasses(OutputInterface $output)
    {
        $classes       = [];
        $coreNamespace = 'src\Core\\';
        $corePath      = dirname(__DIR__) . DIRECTORY_SEPARATOR ."Core/*.php";
        
        foreach (glob($corePath) as $file) {
            $className = $coreNamespace . basename($file, '.php');
            $class = new ReflectionClass($className);
            $methods = array_filter($class->getMethods(ReflectionMethod::IS_PUBLIC), function($method) {
                return !$method->isConstructor() && !$method->isDestructor();
            });
    
            $filteredMethods = [];
            foreach ($methods as $method) {
                if (!$this->isPhpBuiltInMethod($method->getDeclaringClass()->getName(), $method->getName())) {
                    $filteredMethods[] = $method;
                }
            }
    
            $classes[basename($file, '.php')] = $filteredMethods;
        }
    
        $table = new Table($output);
        $table->setHeaderTitle("Avilable Commands");
        $table->setHeaders(['Class', 'Method']);
        foreach ($classes as $className => $methods) {
            foreach ($methods as $method) {
                $table->addRow([$className, $method->getName()]);
            }
        }
        $table->render();
    }
    
    protected function showClassMethods(OutputInterface $output)
    {
        $classFullName = 'src\Core\\' . $this->className;

        if (!class_exists($classFullName)) {
            $this->coustomErrorMessage("Class $this->className not found", $output);
            return;
        }
        
        $class = new ReflectionClass($classFullName);
        $methods = array_filter($class->getMethods(ReflectionMethod::IS_PUBLIC), function($method) {
            return !$method->isConstructor() && !$method->isDestructor();
        });

        $filteredMethods = [];
        foreach ($methods as $method) {
            if (!$this->isPhpBuiltInMethod($method->getDeclaringClass()->getName(), $method->getName())) {
                $filteredMethods[] = $method;
            }
        }
        
        $table = new Table($output);
        $table->setHeaderTitle($this->className);
        $table->setHeaders(['Method']);
        foreach ($filteredMethods as $method) {
            $table->addRow([$method->getName()]);
        }
        $table->render();
    }
    
    protected function showMethodParameters(OutputInterface $output)
    {   
        $classFullName = 'src\Core\\' . $this->className;

        if (!class_exists($classFullName)) {
            $this->coustomErrorMessage("Class $this->className not found", $output);
            return;
        }
        
        $class = new ReflectionClass($classFullName);
        if (!$class->hasMethod($this->methodName)) {
            $this->coustomErrorMessage("Method $this->methodName not found in class $this->className", $output);
            return;
        }
        
        $method = $class->getMethod($this->methodName);
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
        
        if (!empty($optionalParams)) {
            $tableOptional = new Table($output);
            $tableOptional->setHeaderTitle('Optional');
            $tableOptional->setHeaders(['Parameter', 'Type', 'Default Value']);
            foreach ($optionalParams as $param) {
                $tableOptional->addRow($param);
            }
            $tableOptional->render();
        }
        
        if (!empty($mandatoryParams)) {
            $tableMandatory = new Table($output);
            $tableMandatory->setHeaderTitle('Mandatory');
            $tableMandatory->setHeaders(['Parameter', 'Type']);
            foreach ($mandatoryParams as $param) {
                $tableMandatory->addRow($param);
            }
            $tableMandatory->render();
        }
    }

    protected function isPhpBuiltInMethod($className, $methodName)
    {
        $reflectionMethod = new ReflectionMethod($className, $methodName);
        return $reflectionMethod->getFileName() === false;
    }

    protected function setMethodParameters(string|null $command): static{
        if($command !== null){
            $parts = explode('.', $command);
            if (count($parts) === 2) {
                [$className, $methodName] = $parts;
                $classFullName = 'src\Core\\' . $className;
                if (class_exists($classFullName)) {
                    $class = new ReflectionClass($classFullName);
                    if ($class->hasMethod($methodName)) {
                        $method = $class->getMethod($methodName);
                        $parameters = $method->getParameters();
                        foreach ($parameters as $parameter) {
                            $paramName = $parameter->getName();
                            $defaultValue = $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null;
                            $mode = $parameter->isDefaultValueAvailable() ? InputOption::VALUE_OPTIONAL : InputOption::VALUE_REQUIRED;
                            $this->addOption($paramName, null, $mode, "Value for parameter $paramName", $defaultValue);
                        }
                    }
                }
            }
        }
        return $this;
    }

    protected function getParameters(ReflectionMethod $reflectionMethod, InputInterface $input, OutputInterface $output): array{
        $parameters = [];

        $methodParameters = $reflectionMethod->getParameters();
    
        foreach ($methodParameters as $parameter) {
            $parameterName = $parameter->getName();
            
            if ($input->hasOption($parameterName)) {
                $optionValue = $input->getOption($parameterName);
                if (!$parameter->isDefaultValueAvailable() && $optionValue === null) {
                    $this->coustomErrorMessage("Required option --$parameterName is missing", $output);
                }

                $parameters[$parameterName] = $optionValue;
            }
        }

        return $parameters;
    }

    protected function coustomErrorMessage(string $message, OutputInterface $output): void
    {
        $table = new Table($output);
        $table->setHeaderTitle('Error');
        $table->addRow(["<error>$message</error>"]);
        $table->render();
        exit;
    }
}

$input = new ArgvInput();
$output = new ConsoleOutput();
(new Core($input))->run($input, $output);
