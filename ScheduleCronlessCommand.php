<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class ScheduleCronlessCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'schedule:cronless {--stop-after-minutes=} {--debug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start a schedule worker that stops automatically';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $stopAfter = (int) $this->option('stop-after-minutes');
        $debug = (bool) $this->option('debug');

        $now = now()->startOfMinute();
        $end = now()->addMinutes($stopAfter)->startOfMinute();

        $this->info('['.date('c').'] Schedule worker started'.($stopAfter ? ', will end at : '.$end : ''));

        $lastExecutionStartedAt = null;
        $keyOfLastExecutionWithOutput = null;
        $executions = [];

        while (! $stopAfter || $now < $end) {
            sleep(1);
            $now = now();
            $isMinute = $now->second === 0;
            $now = $now->startOfMinute();

            if ($isMinute && ! $now->equalTo($lastExecutionStartedAt)) {
                $executions[] = $execution = new Process([PHP_BINARY, base_path().'/artisan', 'schedule:run']);

                $execution->start();

                $lastExecutionStartedAt = $now;
            }

            foreach ($executions as $key => $execution) {
                $output = trim($execution->getIncrementalOutput()).
                          trim($execution->getIncrementalErrorOutput());

                if (! empty($output)) {
                    if ($debug && $key !== $keyOfLastExecutionWithOutput) {
                        $this->info(PHP_EOL.'['.date('c').'] Execution #'.($key + 1).' output:');

                        $keyOfLastExecutionWithOutput = $key;
                    }

                    $this->line($output);
                }

                if (! $execution->isRunning()) {
                    unset($executions[$key]);
                }
            }
        }

        foreach ($executions as $key => $execution) {
            if ($execution->isRunning()) {
                $this->info(PHP_EOL.'['.date('c').'] Stopping execution #'.($key + 1));
                $execution->stop();
            }
        }

        $this->info(PHP_EOL.'['.date('c').'] Schedule worker stopped.');
    }
}
