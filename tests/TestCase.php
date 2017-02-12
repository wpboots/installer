<?php

abstract class TestCase extends PHPUnit_Framework_TestCase
{
    protected function exec($command)
    {
        exec($command, $output, $status);
        if ($status !== 0) {
            $error = 'Failed to run shell command.';
            if (count($output)) {
                $error = PHP_EOL . implode(PHP_EOL, $output);
            }
            throw new RuntimeException($error, $status);
        }
    }
}
