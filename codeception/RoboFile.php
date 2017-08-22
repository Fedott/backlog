<?php

use Fedot\ProcessRunner\Runner;

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends \Robo\Tasks
{
    public function test()
    {
        $this->startChromeDriver();
        $this->startRedisServer();
        $this->startAerys();

        sleep(1);

        $result = $this->taskCodecept('bin/codecept')
            ->run()
        ;

        if ($result->wasSuccessful()) {
            $this->say('Tests passed');

            return 0;
        } else {
            $this->say('Tests failed');

            return 1;
        }
    }

    private function startRedisServer()
    {
        $runner = new Runner(
            'redis-server --port 25325 --timeout 3 --pidfile /tmp/amp-redis.pid',
            'Ready to accept connections',
            3
        );

        $this->say('Running redis');
        $runner->startAndWait();
        $this->say('[OK]');

        register_shutdown_function(
            function () {
                `pkill -f 'redis-server \*:25325'`;
            }
        );
    }

    private function startChromeDriver(): void
    {
        $runner = new Runner(
            'chromedriver --url-base=/wd/hub',
            'Only local connections are allowed',
            3
        );
        $this->say('Running chromedriver');
        $runner->startAndWait();
        $this->say('[OK]');

        register_shutdown_function(
            function () {
                `pkill -f chromedriver`;
            }
        );
    }

    private function startAerys(): void
    {
        $runner = new Runner(
            'sh -c "REDIS_URI=tcp://localhost:25325 bin/aerys -c app/aerys-config.php -d"',
            'started',
            3,
            realpath(__DIR__ . '/../amphp')
        );

        $this->say('Running aerys');
        $runner->startAndWait();
        $this->say('[OK]');

        register_shutdown_function(
            function () {
                `pkill -f aerys`;
            }
        );
    }
}
