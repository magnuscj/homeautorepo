<?php
date_default_timezone_set('Europe/Stockholm');

$server_ip = '192.168.1.45';
$server_port = 3322;
$beat_period = 5;
$guardPeriode = 2000;
$payloadDelimiter = '&&';
$socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

while(true)
{
    $directory = getcwd()."\\pictures";
    $filesInDir = scandir($directory);

    

    foreach($filesInDir as $filename )
    {
        if (file_exists($directory."\\".$filename) && is_file($directory."\\".$filename))
        {
            $diff = time() - filemtime($directory."\\".$filename);

            if($diff > $guardPeriode)
            {
                $message = $payloadDelimiter.$filename." - ".$diff;
                print $message;
                socket_sendto($socket, $message, strlen($message), 0, $server_ip, $server_port);
            }
        }
    }
    sleep($beat_period);
}
socket_close($socket);


?>