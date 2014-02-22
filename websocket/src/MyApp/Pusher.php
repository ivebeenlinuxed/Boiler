<?php
namespace MyApp;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;

class Pusher implements WampServerInterface {
    public $channelSubscriptions = array();

    public function onSubscribe(ConnectionInterface $conn, $topic) {
	$id = $topic->getId();
	if (!array_key_exists($id, $this->channelSubscriptions)) {
		$this->channelSubscriptions[$id] = $topic;
	}
    }

    public function onUnSubscribe(ConnectionInterface $conn, $topic) {
    }
    public function onOpen(ConnectionInterface $conn) {
    }
    public function onClose(ConnectionInterface $conn) {
    }
    public function onCall(ConnectionInterface $conn, $id, $topic, array $params) {
	if (strpos($topic->getId(), "/lock/") === 0) {
		$ch = curl_init("http://localhost/api/user/".$params[1].".json");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$tr = json_decode(curl_exec($ch));
		$topic->broadcast(array("lock_id"=>$params[0], "user"=>$tr->data));
		return;
	}

        // In this application if clients send data it's because the user hacked around in console
        $conn->callError($id, $topic, 'You are not allowed to make calls')->close();
    }
    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible) {
        // In this application if clients send data it's because the user hacked around in console
        $conn->close();
    }

    public function onServerPush($data) {
	$data = json_decode($data, true);
	var_dump("Server Connection", $data);
	if (array_key_exists($data['channel'], $this->channelSubscriptions)) {
		var_dump("Broadcasting to: {$data['channel']}");
		$this->channelSubscriptions[$data['channel']]->broadcast($data['data']);
	} else {
		var_dump("No-one subscribed to {$data['channel']}");
	}
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
    }
}
