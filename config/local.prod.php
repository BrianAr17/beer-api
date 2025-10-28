<?php

declare(strict_types=1);
// Production environment

return function (array $settings): array {
    $settings['db']['database'] = 'beers_api';
    $settings['db']['hostname'] = 'localhost';

    return $settings;
};
