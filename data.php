<?php

/**
 * Realtime bandwidth meter with php and jquery.
 *
 * @author Andreas Beder <andreas@codejungle.org>
 * @author Nikola Petkanski <nikola@petkanski.com>
 */

$interface = array_key_exists('interface', $_GET) ? $_GET['interface'] : null;

if (!ctype_alnum($interface)) {
    throw new RuntimeException('Invalid interface name. Must contain only alphabetic and numeric characters.');
}

/**
 * WARNING: DRAGONS AHEAD!
 *
 * Do not modify after this line, unless you know what you are doing.
 */

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
$_SESSION['rx'][] = array($time, $round_rx);
$_SESSION['tx'][] = array($time, $round_tx);

/**
 * to make sure that the graph shows only the
 * last minute (saves some bandwitch to)
 */
if (count($_SESSION['rx'])>60)
{
    $x = min(array_keys($_SESSION['rx']));
    unset($_SESSION['rx'][$x]);
}

header('Content-Type: application/json');

echo json_encode(array(
    'label' => $interface,
    'data' => array_values($_SESSION['rx']),
));

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
