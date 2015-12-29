<?php

$interface="en4";
session_start();

define('OS_LINUX', 'Linux');
define('OS_FREEBSD', 'FreeBSD');
define('OS_OSX', 'Darwin');
define('PATH_NETSTAT', '/usr/sbin/netstat');

$rx[] = getInterfaceReceivedBytes($interface);
$tx[] = getInterfaceSentBytes($interface);
sleep(1);
$rx[] = getInterfaceReceivedBytes($interface);
$tx[] = getInterfaceSentBytes($interface);

$tbps = $tx[1] - $tx[0];
$rbps = $rx[1] - $rx[0];

$round_rx=round($rbps/1024, 2);
$round_tx=round($tbps/1024, 2);

$time=date("U")."000";
$_SESSION['rx'][] = "[$time, $round_rx]";
$_SESSION['tx'][] = "[$time, $round_tx]";

$data['label'] = $interface;
$data['data'] = $_SESSION['rx'];

/**
 * to make sure that the graph shows only the
 * last minute (saves some bandwitch to)
 */
if (count($_SESSION['rx'])>60)
{
    $x = min(array_keys($_SESSION['rx']));
    unset($_SESSION['rx'][$x]);
}

/**
 * json_encode didnt work, if you found a workarround pls write me
 * echo json_encode($data, JSON_FORCE_OBJECT);
 */

echo '
{"label":"eth0","data":['.implode($_SESSION['rx'], ",").']}
';

function isOSX()
{
    $uname = explode(' ', php_uname());
    return $uname[0] === OS_OSX;
}

function isLinux()
{
    $uname = explode(' ', php_uname());
    return $uname[0] === OS_LINUX;
}

function isFreeBSD()
{
    $uname = explode(' ', php_uname());
    return $uname[0] === OS_FREEBSD;
}

function getInterfaceReceivedBytes($interface)
{
    if (isLinux()) {
        $filepath = '/sys/class/net/%s/statistics/rx_bytes';
        $output = file_get_contents(sprintf($filepath, $interface));

        return $output;
    }

    if (isFreeBSD() || isOSX()) {
        $command = "%s -ibn| grep %s | grep Link | awk '{print $7}'";
        exec(sprintf($command, PATH_NETSTAT, $interface), $output);

        return array_shift($output);
    }

    throw new Exception('Unable to guess OS');
}

function getInterfaceSentBytes($interface)
{
    if (isLinux()) {
        $filepath = '/sys/class/net/%s/statistics/tx_bytes';
        $output = file_get_contents(sprintf($filepath, $interface));

        return $output;
    }

    if (isFreeBSD() || isOSX()) {
        $command = "%s -ibn| grep %s | grep Link | awk '{print $10}'";
        exec(sprintf($command, PATH_NETSTAT, $interface), $output);

        return array_shift($output);
    }

    throw new Exception('Unable to guess OS');
}