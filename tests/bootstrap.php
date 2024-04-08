<?php

declare(strict_types=1);

use Symfony\Component\ErrorHandler\ErrorHandler;

// Register the error handler to avoid the error handler warning from Phpunit 11
// https://github.com/symfony/symfony/issues/53812#issuecomment-1962311843
ErrorHandler::register(null, false);
