<?php

namespace InnoBrain\OnofficeCli\Commands;

use Exception;
use Illuminate\Console\Command;
use InnoBrain\OnofficeCli\Concerns\OutputsJson;
use InnoBrain\OnofficeCli\Exceptions\OnOfficeCliException;

abstract class OnOfficeCommand extends Command
{
    use OutputsJson;

    public function handle(): int
    {
        try {
            return $this->executeCommand();
        } catch (OnOfficeCliException $e) {
            return $this->outputError($e->getMessage(), $e->getHttpCode());
        } catch (Exception $e) {
            return $this->outputError($e->getMessage(), 500);
        }
    }

    abstract protected function executeCommand(): int;
}
