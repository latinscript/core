#!/usr/bin/env php
<?php



Phar::mapPhar('phar.phar');

require 'phar://phar.phar/exe.php';

__HALT_COMPILER();

?>
