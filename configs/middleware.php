<?php

declare(strict_types=1);

use Slim\App;
use App\Config;
use Slim\Views\Twig;
use Clockwork\Clockwork;
use App\Enum\AppEnvironment;
use Slim\Views\TwigMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\CsrfFieldsMiddleware;
use App\Middleware\OldFormDataMiddleware;
use App\Middleware\StartSessionsMiddleware;
use Slim\Middleware\MethodOverrideMiddleware;
use App\Middleware\ValidationErrorsMiddleware;
use Clockwork\Support\Slim\ClockworkMiddleware;
use App\Middleware\ValidationExceptionMiddleware;


return function (App $app) {
    $container = $app->getContainer();
    $config = $container->get(Config::class);

    $app->add(MethodOverrideMiddleware::class);
    $app->add(CsrfFieldsMiddleware::class);
    $app->add('csrf');
    $app->add(TwigMiddleware::create($app, $container->get(Twig::class)));
    $app->add(ValidationExceptionMiddleware::class);
    $app->add(ValidationErrorsMiddleware::class);
    $app->add(OldFormDataMiddleware::class);
    $app->add(CsrfMiddleware::class);
    if(AppEnvironment::isDevelopment($config->get('app_environment'))){
        $app->add(new ClockworkMiddleware($app, $container->get(Clockwork::class)));
    }
    $app->addBodyParsingMiddleware();

    $app->add(StartSessionsMiddleware::class);


    $app->addErrorMiddleware(
        (bool) $config->get('display_error_details'),
        (bool) $config->get('log_errors'),
        (bool) $config->get('log_error_details'),
    );
};