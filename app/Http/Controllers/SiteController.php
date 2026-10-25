<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SiteController extends Controller
{
    private const string PID_FILE_NAME = 'app/generate-text.pid';

    private function getPid()
    {
        $pid = @file_get_contents(storage_path(self::PID_FILE_NAME));
        if (!$pid) return -1;
        return $pid;
    }

    private function isRunning()
    {
        $pid = $this->getPid();
        if ($pid < 0) {
            return null;
        }

        $running = posix_kill((int) $pid, 0);

        return $running;
    }

    public function index()
    {
        $state = "idle";
        if ($this->isRunning() === null) {
            $state = "idle";
        } else if ($this->isRunning()) {
            $state = "running";
        } else {
            $state = "finished";
        }

        return view('index', ['state' => $state, 'pid' => $this->getPid()]);
    }

    public function start()
    {
        if ($this->isRunning()) return redirect()->route('home');

        $commandToRun = 'php artisan app:generate-text';

        logger()->info('starting');

        $pidFile = escapeshellarg(storage_path(self::PID_FILE_NAME));
        $projectPath = escapeshellarg(base_path());
        $stdOutFile = escapeshellarg(storage_path('logs/generate-text-stdout.log'));

        $command = 'sh -c ' . escapeshellarg(
            "cd {$projectPath}"
            . " && exec {$commandToRun}"
            . " >> {$stdOutFile}"
            . " 2>&1 < /dev/null"
            . " </dev/null >/dev/null 2>&1 & echo $! > {$pidFile}"
        );

        exec($command);

        return redirect()->route('home');
    }
}
