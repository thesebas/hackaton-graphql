<?php

namespace thesebas\graphql\tools;

if (!defined('STDERR')) {
    define('STDERR', fopen("php://stderr", 'w'));
}
function dump($val)
{
    \fprintf(STDERR, "%s\n", \var_export($val, true));
}
