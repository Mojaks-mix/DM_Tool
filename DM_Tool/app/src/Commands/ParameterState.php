<?php

namespace src\Commands;

enum ParameterState : int{
    case OPTIONAL   = 50;
    case MANDATORY  = 55;
}