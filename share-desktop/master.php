<?php

// Config section
define('FORCE_SCREEN_SIZE_DIV_4', true);
define('DEFAULT_REMOTE_DISPLAY', ':0.0');

/* 
 * Remote servers
 * 
 * These all need to be set up with passwordless access. On the master:
 * 
 * ssh-keygen -t rsa (if you do not already have a key pair generated)
 * ssh-copy-id user@192.168.1.1 (where the IP is your remote machine)
 * ssh-add
 * 
 * Then test without a password:
 * 
 * ssh user@192.168.1.1
 */
$wallClients = array(
    array(
        'client-enabled' => true,
        'client-path' => '/home/mark/QuadScreen/client.php',
        'client-user' => 'mark',
        'host' => '192.168.100.1',
        'port' => 5900,
        'display' => DEFAULT_REMOTE_DISPLAY,
//        'user' => DEFAULT_REMOTE_USER,
        'screen-size' => '1024x768',
    ),
    array(
        'client-enabled' => true,
        'client-path' => '/home/talis/QuadScreen/client.php',
        'client-user' => 'talis',
        'host' => '192.168.100.2',
        'port' => 5901,
        'display' => DEFAULT_REMOTE_DISPLAY,
//        'user' => DEFAULT_REMOTE_USER,
        'screen-size' => '1024x768',
    ),
    array(
        'client-enabled' => true,
        'client-path' => '/home/talis/QuadScreen/client.php',
        'client-user' => 'talis',
        'host' => '192.168.100.3',
        'port' => 5902,
        'display' => DEFAULT_REMOTE_DISPLAY,
//        'user' => DEFAULT_REMOTE_USER,
        'screen-size' => '1024x768',
    ),
    array(
        'client-enabled' => true,
        'client-path' => '/home/jon/QuadScreen/client.php',
        'client-user' => 'jon',
        'host' => '192.168.100.4',
        'port' => 5903,
        'display' => DEFAULT_REMOTE_DISPLAY,
//        'user' => DEFAULT_REMOTE_USER,
        'screen-size' => '1024x768',
    ),
);

// Get the current screen metadata
$xOutput = array();
$command = 'xrandr';
exec($command, $xOutput);
$outputStr = implode("\n", $xOutput);

// Read the current resolution
$matches = array();
preg_match('/current (\d+) x (\d+),/', $outputStr, $matches);
if (!$matches)
{
    die('Could not read current display res from xrandr');
}

$width = $matches[1];
$height = $matches[2];

$resized = '';
if (FORCE_SCREEN_SIZE_DIV_4)
{
    $width = intval($width / 4 ) * 4;
    $height = intval($height / 4) * 4;
    $resized = '(resized)';
}

echo "Launching VNC servers for $width x $height desktop {$resized}\n";

// Get the mid width and height
$midWidth = intval($width / 2);
$midHeight = intval($height / 2);
$size = "{$midWidth}x{$midHeight}";

// Set up 2x2 screen geometries
$wallClients[0]['clip-size'] = "{$size}+0+0";
$wallClients[1]['clip-size'] = "{$size}+{$midWidth}+0";
$wallClients[2]['clip-size'] = "{$size}+0+{$midHeight}";
$wallClients[3]['clip-size'] = "{$size}+{$midWidth}+{$midHeight}";

// Set up some VNC servers
$pids = array();
foreach ($wallClients as $wallClient)
{
    // Launch server
    $clipSize = $wallClient['clip-size'];
    $port = $wallClient['port'];
    $serverCommand = "x11vnc -clip {$clipSize} -rfbport {$port} -viewonly -noxdamage -forever";

    // Optionally set a full-screen size for the remote client
    if (isset($wallClient['screen-size']))
    {
        $screenSize = $wallClient['screen-size'];
        $serverCommand .= " -scale {$screenSize}";
    }

    $pid = runNohup($serverCommand);

    echo "Launched server $pid with geometry $clipSize\n";
    $pids[] = $pid;

    $port++;
}

// We need to wait for the servers to start up
sleep(5);

// Set up some remote VNC clients
foreach($wallClients as $wallClient)
{
    if (isset($wallClient['client-enabled']) && $wallClient['client-enabled'])
    {
        print_r($wallClient);
        $user = $wallClient['client-user'];
        $host = $wallClient['host'];
        $path = $wallClient['client-path'];
        $port = $wallClient['port'];
        $clientCommand = "ssh $user@$host \"eval \`php $path $port\` 2>&1 > /tmp/log\"";
        $pid = runNohup($clientCommand);

        echo "Launched client on host {$wallClient['host']} ($clientCommand)\n";
    }
}

echo "Type Ctrl-C to exit the VNC servers and remote clients\n";

while(true)
{
    sleep(5);
}

function runNohup($command)
{
    return shell_exec("nohup $command > /dev/null 2>&1 & echo $!");
}
