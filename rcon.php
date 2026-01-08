<?php
class Rcon {
    private $socket;
    private $requestId = 1;

    public function connect($host, $port, $password) {
        $this->socket = fsockopen($host, $port, $errno, $errstr, 10);
        if (!$this->socket) return false;

        if (!$this->auth($password)) {
            fclose($this->socket);
            return false;
        }
        return true;
    }

    private function auth($password) {
        $this->write(3, $password);
        $response = $this->read();
        return $response['requestId'] != -1;
    }

    public function command($command) {
        $this->write(2, $command);
        return $this->read()['body'];
    }

    private function write($type, $body) {
        $packet = pack('VV', $this->requestId++, $type) . $body . "\x00\x00";
        $packet = pack('V', strlen($packet)) . $packet;
        fwrite($this->socket, $packet);
    }

    private function read() {
        $size = fread($this->socket, 4);
        if (strlen($size) < 4) return false;
        $size = unpack('V', $size)[1];

        $data = fread($this->socket, $size);
        $packet = unpack('VrequestId/Vtype/a*body', $data);
        return $packet;
    }

    public function disconnect() {
        if ($this->socket) fclose($this->socket);
    }
}
?>
