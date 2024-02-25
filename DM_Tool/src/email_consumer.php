<?php

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor\autoload.php';

use src\Core\EmailTask;

$mailTaskConsumer = new EmailTask();
$mailTaskConsumer->consume();

// call_user_func_array([$mailTaskConsumer, 'consume'], []);




//to make a command call of a function
// if (class_exists($className)) {
//     $class = new $this->className;

//     if (method_exists($class, $this->action)) {

//         call_user_func_array([$class, $this->action], [$this->parameters]);
        
//     } else {
//         throw new CustomException("");
//     }
// } else {
//     throw new CustomException("");
// }