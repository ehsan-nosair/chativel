<?php

namespace EhsanNosair\Chativel\Commands;

use Illuminate\Console\Command;

class ChativelCommand extends Command
{
    public $signature = 'chativel';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
