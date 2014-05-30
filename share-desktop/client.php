<?php

// Config
define('TASK_TO_KILL', 'xtightvncviewer');
define('TASK_TO_LAUNCH', 'DISPLAY=:0 xtightvncviewer -fullscreen -quality 1 -viewonly');
define('MASTER_IP', '192.168.100.11');
define('DEFAULT_PORT', 5900);

// Read the client port number here
$port = DEFAULT_PORT;
if (isset($argv[1]))
{
    $port = $argv[1];
}

// See if there are any existing clients running
$psCommand = 'pgrep ' . TASK_TO_KILL;
$psOut = array();
exec($psCommand, $psOut);
$psOutputStr = implode("\n", $psOut);

// Kill them off if they are
$matches = array();
preg_match_all('/^(\d+)/m', $psOutputStr, $matches);

$killed = false;
if ($matches && isset($matches[1]))
{
    foreach ($matches[1] as $pid)
    {
        if ($pid != getmypid())
        {
            exec("kill $pid");
            $killed = true;
            echo "Killed existing client $pid\n";
        }
    }
}

// Wait a bit before relaunching, to give ports a chance to release
if ($killed)
{
    sleep(1);
}

// Run new client in the background, exiting immediately
$command = TASK_TO_LAUNCH . ' ' . MASTER_IP . ':' . $port;
echo "$command\n";
runNohup($command);

function runNohup($command)
{
    return shell_exec("nohup $command > /dev/null 2>&1 & echo $!");
}