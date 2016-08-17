<?php

require_once __DIR__.'/../vendor/autoload.php';

Mockery::getConfiguration()->allowMockingNonExistentMethods(false);
Mockery::getConfiguration()->allowMockingMethodsUnnecessarily(false);
