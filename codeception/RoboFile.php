<?php
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
        $this->taskExec('redis-server --port 25325 --timeout 3')->background()->run();

        $this->startAerys();

        sleep(1);

        $result = $this->taskCodecept('bin/codecept')
            ->debug()
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

    private function startChromeDriver(): void
    {
        $this->say('Running chromedriver');
        passthru('chromedriver --url-base=/wd/hub > /tmp/driver-log 2>/tmp/driver-log-err &');
        $this->say('[OK]');

        register_shutdown_function(
            function () {
                `pkill -f chromedriver`;
            }
        );
    }

    private function startAerys(): void
    {
        $this->say('Running aerys');
        chdir('../amphp');
        passthru('REDIS_URI=tcp://localhost:25325 bin/aerys -c app/aerys-config.php -d > /tmp/web-log 2>/tmp/web-log-err &');
        $this->say('[OK]');
        chdir(__DIR__);

        register_shutdown_function(
            function () {
                `pkill -f aerys`;
            }
        );
    }
}
