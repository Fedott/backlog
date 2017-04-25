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
        $this->taskExec('redis-server --port 25325 --timeout 3')->background()->run();
        $this->taskExec('bin/aerys -c app/aerys-config.php -d')
            ->env(['REDIS_URI' => 'tcp://localhost:25325?database=1'])
            ->dir(__DIR__ . '/../amphp')
            ->background()
            ->run()
        ;

        $result = $this->taskCodecept('bin/codecept')
            ->run()
        ;

        if ($result->wasSuccessful()) {
            $this->say('Tests passed');
        } else {
            $this->say('Tests failed');
        }
    }
}
