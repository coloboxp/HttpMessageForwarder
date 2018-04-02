<?php

    function processSocket($server) {

        if($server == '')
        {
            return;
        }

        $hostname = $server->host;
        $port = $server->port;
    
        foreach ($server->commands as $command) {
            $repeats = 0;

            if(isset($command->repeats))
            {                
                $repeats = $command->repeats;
            }

            $i = 0;

            do {
                $send = True;

                if(isset($command->beforeDelay))
                {
                    sleep($command->beforeDelay / 1000);
                }

                if ($command->type ='onkyo')
                {
                    switch ($command->message)
                    {
                        case 'PWR':
                        case 'AMT':
                        case 'NTC':
                        case 'SLI':
                        case 'TUN':
                            $message = '!1' . $command->message . $command->params;
                            //echo($message);
                            break;
                        case 'MVL':
                            if ($params > 60)
                                exit(1);
                            $message = '!1MVL' . strtoupper(str_pad(dechex($command->params), 2, '0', STR_PAD_LEFT));
                            break;
                        default:
                            echo('Unknown message: \'' . $command->message . '\'');
                            $send = FALSE;
                            //break;
                            exit(1);
                    }

                    $packet = "ISCP\x00\x00\x00\x10\x00\x00\x00" . chr(strlen($message) + 1) . "\x01\x00\x00\x00" . $message . "\x0D";                
                }
                elseif($command->type != 'http')
                {
                    switch($command->content)
                    {
                        case 'hex':
                            $packet = $command->message;
                            break;
                        case 'ascii':
                            $packet = unpack('H*', $command->message);
                            break;
                    }

                    $packet = strtoupper($packet);
                }
                else
                {
                    // do the http request here
                }

                if($command->type != 'http')
                {
                    if($send)
                    {
                        $fp = pfsockopen($hostname, $port);
                        fwrite($fp, $packet);
                        
                        fclose($fp);

                        if(isset($command->afterDelay))
                        {
                            sleep($command->afterDelay / 1000);
                        }
                    }
                }

                $i = $i + 1;
            } while ($i < $repeats);
        }
    }

    function processHttp($server) {

        if($server == '')
        {
            return;
        }

        $hostname = $server->host;
        $port = $server->port;
    
        $repeats = 0;

        if(isset($command->repeats))
        {                
            $repeats = $command->repeats;
        }

        $i = 0;

        do {
            $url = $server->type . '://' . $hostname . ':' . $port . '/' . $server->path;

            $data = array();

            if(isset($server->params))
            {
                foreach($server->params as $paramSet)
                {
                    $data[$paramSet->name] = $paramSet->value;
                }
            }
            ini_set('default_socket_timeout',    1); 
            $options = array(
                $server->type => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded",//\r\n",
                    'method'  => strtoupper($server->method),
                    'content' => http_build_query($data),
                    'timeout' => 1
                )
            );

            $context  = stream_context_create($options);

            $result = file_get_contents($url, false,$context, -1, 0);

            if(isset($command->afterDelay))
            {
                sleep($command->afterDelay / 1000);
            }

            $i = $i + 1;
        } while ($i < $repeats);
    }

    function debugDump($variable)
    {
        ob_start();
        var_dump($variable);
        $result = ob_get_clean();
    }

    $command = $_POST['qry'];

    $cmd = json_decode($command);

    $endpoints = $cmd->endpoints;

    if(isset($endpoints))
    {
        foreach($endpoints as $endpoint)
        {
            if($endpoint->type == 'socket')
            {
                processSocket($endpoint);
            }
            elseif($endpoint->type == 'http' || $endpoint->type == 'https')
            {
                processHttp($endpoint);
            }
            else{
                echo('Unknown endpoint type');
                exit(1);
            }
        }
    }
?>