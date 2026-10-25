<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:generate-text')]
#[Description('Command description')]
class GenerateText extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->generate();
    }

    private function generate() {
        $until = 25;
        $timegap = 1;
        $i = 0;

        while($i < $until) {
            sleep($timegap);
            $took = $i+$timegap;
            logger()->info("Logged after: {$took} s");
            $i++;
        }

        logger()->info("Finished!");
        return true;
    }
}
