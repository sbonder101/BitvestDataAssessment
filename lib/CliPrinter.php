<?php

namespace Studentcli;

class CliPrinter
{
    public function out($message)
    {
        echo $message;
    }

    public function newline()
    {
        $this->out("\n");
    }

    public function display($message)
    {
        $this->newline();
        $this->out($message);
        $this->newline();
        $this->newline();
    }

    public function prompt(string $message = null)
    {
      echo $message;
      $handle = fopen ("php://stdin","r");
      $output = fgets ($handle);
      return trim ($output);
    }
}
