<?php

class serial
{
    /**
     * @var resource
     */
    protected $portHandler;

    /**
     * @var string
     */
    protected $port;

    /**
     * @var int
     */
    protected $speed;

    /**
     * @param $port string
     * @param $speed int
     */
    public function __construct($port, $speed)
    {
        $this->port = $port;
        $this->speed = $speed;
    }

    /**
     * Connect to serial port
     */
    public function open()
    {
        $this->portHandler = dio_open($this->port, O_RDWR | O_NOCTTY | O_NONBLOCK);

        if ($this->portHandler === false) {
            throw new RuntimeException("Can't connect to serial port: " . $this->port);
        }

        dio_fcntl($this->portHandler, F_SETFL, O_NONBLOCK);
        dio_tcsetattr(
            $this->portHandler,
            array(
                'baud' => $this->speed,
                'bits' => 8,
                'stop' => 1,
                'parity' => 0
            )
        );
    }

    /**
     * Close serial port
     */
    public function close()
    {
        dio_close($this->portHandler);
    }

    /**
     * Sends data to the device
     *
     * @var int|array $data
     * @throws LogicException
     */
    function send_data($data)
    {
        $string = $this->prepareData($data);
        $bytes = dio_write($this->portHandler, $string);

        if ($bytes !== strlen($string)) {
            throw new LogicException("Sended {$bytes} bytes of " . strlen($string));
        }
    }

    /**
     * Convert data to string
     * @param int|array $data
     * @return string
     */
    protected function prepareData($data)
    {
        if (!is_array($data)) {
            $data = [$data];
        }

        $data = array_map('chr', $data);
        $string = implode('', $data);

        return $string;
    }

    /**
     * Read data from device
     *
     * @param $minBufferSize
     * @param int $timeOut
     *
     * @return string
     */
    function get_binary($minBufferSize, $timeOut = 1)
    {
        $data = '';
        $endTime = time() + $timeOut;
        do {
            $data .= dio_read($this->portHandler);
            $size = strlen($data);
            $time = time();
        } while ($size < $minBufferSize && ($time < $endTime));

        return $data;
    }
}