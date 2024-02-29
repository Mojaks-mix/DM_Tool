<?php

require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\SingleCommandApplication;
use Symfony\Component\HttpClient\HttpClient;

(new SingleCommandApplication())
    ->setName('MyCommand')

    ->addArgument('className', InputArgument::REQUIRED)
    ->addArgument('methodName', InputArgument::REQUIRED)
    ->addOption('parameters','-p', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'parameters', [])
    ->setCode(function (InputInterface $input, OutputInterface $output): int {
        //  getting inputs ( arguments , options ... )
        $className = (string)$input->getArgument('className');
        $methodName = (string)$input->getArgument('methodName');
        $parameters = (array)$input->getOption('parameters');

        //making the class fully qualified name
        $className = 'src\\Core\\' . $className;

        //to make a command call of a function
        if (class_exists($className)) {
            $class = new $className;

            if (method_exists($class, $methodName)) {
                try {
                    call_user_func_array([$class, $methodName], $parameters);
                } catch (Exception $e) {
                    // Handle the exception
                    $output->writeln(sprintf("\n \"%s\" 'An error occurred: \"%s\"\n", $e->getMessage()));
                } 
            } else {
                $output->writeln(sprintf("\n \"%s\" Method Doesn't Exist in Class \"%s\".\n", $methodName,$className ));
            }
        } else {
            $output->writeln(sprintf("\n %s Class Doesn't Exist.\n", $className ));
        }

        return Command::SUCCESS;
    })
    ->run();