<?php

namespace InnoBrain\OnofficeCli\Commands;

use Illuminate\Console\Command;

class OnofficeCliCommand extends Command
{
    public $signature = 'onoffice-cli';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
