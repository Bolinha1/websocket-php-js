<?php
    
$address = '0.0.0.0';
$port = 3031;

$server = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

socket_set_option($server, SOL_SOCKET, SO_REUSEADDR, 1);

socket_bind($server, $address, $port); 

socket_listen($server);

$client = socket_accept($server);

$request = socket_read($client, 5000);
preg_match('#Sec-WebSocket-Key: (.*)\r\n#', $request, $matches);
$key = base64_encode(pack(
    'H*',
    sha1($matches[1] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')
));

$headers = "HTTP/1.1 101 Switching Protocols\r\n";
    
$headers .= "Upgrade: websocket\r\n";

$headers .= "Connection: Upgrade\r\n";

$headers .= "Sec-WebSocket-Version: 13\r\n";

$headers .= "Sec-WebSocket-Accept: $key\r\n\r\n";

socket_write($client, $headers, strlen($headers));

$dataFileName = 'data.txt'; 
while(true)
{
    $requestedTimestamp = 1605039458;     
    $modifiedAt = filemtime( $dataFileName );
    if ( $modifiedAt > $requestedTimestamp )
    {
        $data = file_get_contents($dataFileName);
        sleep(1);
        $content = 'Content: '.$data;
        $response = chr(129) . chr(strlen($content)) . $content;
        socket_write($client, $response);
    }
}


