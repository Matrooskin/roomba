<?php

class roomba
{
    /**
     * @var Serial
     */
    protected $connection;

    public function run()
    {
        $this->connect();
        $this->initialize();


        $time = time();
        $endTime = $time + 15;
        $response = '';

        $sum = 0;
        while ($time < $endTime) {
            $this->connection->send_data([142, 100]);

            $line = $this->connection->get_binary(80);

            for ($i = 0; $i < strlen($line); $i++) {
                $response .= ord($line[$i]) . ' ';
                $sum++;
                if ((($i + 1) % 16 == 0) || ($i + 1 == strlen($line))) {
                    $response .= PHP_EOL;
                }
            }

            cOut($response);
            $response = '';

            $time = time();
            usleep(500000);
        }

        cOut($sum);




        $this->connection->close();
    }

    protected function initialize()
    {
        //initialize Roomba interface
        $this->connection->send_data(128);
    }

    protected function connect()
    {
        $this->connection = new serial('/dev/ttyS1', 19200);
        $this->connection->open();
    }
}
