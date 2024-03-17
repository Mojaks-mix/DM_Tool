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
use src\Commands\ParameterState;
use src\Commands\Reflector;

class Core extends SingleCommandApplication
{
    use Reflector;

    protected static $defaultName   = 'mycommand';
    protected string $coreNamespace = 'src\Core\\';
    protected string $corePath;
    protected string $classFullName;
    protected string|null $command;
    protected string|null $methodName;
    protected string|null $className;
    

    public function __construct(InputInterface $input)
    {
        $this->corePath      = dirname(__DIR__) . DIRECTORY_SEPARATOR ."Core/*.php";
        $this->command = $input->getFirstArgument();
        $this->processCommand();
        
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
            ->setMethodParametersOptions();
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
        if ($this->checkCommandFormat()) {
            $this->customErrorMessage('Invalid command format. Use: className.methodName', $output);
        }

        if (!$this->checkClassExists($this->classFullName)) {
            $this->customErrorMessage("Class $this->className not found", $output);
        }

        if ($this->checkMethodInClass($this->classFullName, $this->methodName)) {
            $this->customErrorMessage("Method $this->methodName not found in class $this->className", $output);
        }

        $params = $this->validateParameters($input, $output);
        $instance = new $this->classFullName();
        try {
            $methodName = $this->methodName;
            $instance->$methodName(...$params);
        } catch (\Throwable $exception) {
            $this->customErrorMessage("An error occurred while performing $this->methodName: " . $exception->getMessage(), $output);
        }
    }

    protected function showAvailableClasses(OutputInterface $output)
    {
        $classesAndMethods = $this->getClassesAndMethods($this->corePath, $this->coreNamespace);  

        $table = new Table($output);
        $table->setHeaderTitle("Avilable Commands");
        $table->setHeaders(['Class', 'Method']);
        foreach ($classesAndMethods as $className => $methods) {
            foreach ($methods as $method) {
                $table->addRow([$className, $method->getName()]);
            }
        }
        $table->render();
    }
    
    protected function showClassMethods(OutputInterface $output)
    {
        if (!$this->checkClassExists($this->classFullName)) {
            $this->customErrorMessage("Class $this->className not found", $output);
        }
        
        $methods = $this->getPublicUserMethods($this->classFullName);
        
        $table = new Table($output);
        $table->setHeaderTitle($this->className);
        $table->setHeaders(['Method']);
        foreach ($methods as $method) {
            $table->addRow([$method->getName()]);
        }
        $table->render();
    }
    
    protected function showMethodParameters(OutputInterface $output)
    {   
        if (!$this->checkClassExists($this->classFullName)) {
            $this->customErrorMessage("Class $this->className not found", $output);
        }

        if ($this->checkMethodInClass($this->classFullName, $this->methodName)) {
            $this->customErrorMessage("Method $this->methodName not found in class $this->className", $output);
        }
        

        $optionalParams = $this->getClassMethodParameters($this->classFullName, $this->methodName, ParameterState::OPTIONAL);
        $mandatoryParams = $this->getClassMethodParameters($this->classFullName, $this->methodName);
        
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

    protected function setMethodParametersOptions(): static{
        if($this->command){
            if ($this->checkClassExists($this->classFullName)) {
                $class = new ReflectionClass($this->classFullName);

                if ($class->hasMethod($this->methodName)) {
                    $method = $class->getMethod($this->methodName);
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
        return $this;
    }

    protected function customErrorMessage(string $message, OutputInterface $output): void{
        $table = new Table($output);
        $table->setHeaderTitle('Error');
        $table->addRow(["<error>$message</error>"]);
        $table->render();
        exit;
    }

    protected function validateParameters(InputInterface $input, OutputInterface $output): array{
        $parameters = [];

        $reflectionMethod = (new ReflectionClass($this->classFullName))->getMethod($this->methodName);
        $methodParameters = $reflectionMethod->getParameters();
    
        foreach ($methodParameters as $parameter) {
            $parameterName = $parameter->getName();
            
            if ($input->hasOption($parameterName)) {
                $optionValue = $input->getOption($parameterName);
                if (!$parameter->isDefaultValueAvailable() && $optionValue === null) {
                    $this->customErrorMessage("Required option --$parameterName is missing", $output);
                }

                $parameters[$parameterName] = $optionValue;
            }
        }

        return $parameters;
    }

    protected function processCommand(): void{
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

        $this->classFullName = $this->coreNamespace . $this->className;
    }

    protected function checkCommandFormat(): bool{
        return empty($this->className) || empty($this->methodName);
    }
}

$input = new ArgvInput();
$output = new ConsoleOutput();
(new Core($input))->run($input, $output);
