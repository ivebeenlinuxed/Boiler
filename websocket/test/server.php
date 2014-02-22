<?php
$entryData = array();
$entryData['channel'] = "testChannel";
$entryData['data'] = "HELLO WORLD";

$context = new ZMQContext();
$socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
$socket->connect("tcp://localhost:5555");

$socket->send(json_encode($entryData));
