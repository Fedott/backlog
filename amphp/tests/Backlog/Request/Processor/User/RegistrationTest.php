<?php declare(strict_types = 1);
namespace Tests\Fedot\Backlog\Request\Processor\User;

use Amp\Success;
use Fedot\Backlog\Model\User;
use Fedot\Backlog\Payload\UsernamePasswordPayload;
use Fedot\Backlog\Repository\UserRepository;
use Fedot\Backlog\Request\Processor\ProcessorInterface;
use Fedot\Backlog\Request\Processor\User\Registration;
use PHPUnit_Framework_MockObject_MockObject;
use Tests\Fedot\Backlog\RequestProcessorTestCase;

class RegistrationTest extends RequestProcessorTestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|UserRepository
     */
    protected $userRepositoryMock;

    protected function getProcessorInstance(): ProcessorInterface
    {
        $this->userRepositoryMock = $this->createMock(UserRepository::class);

        return new Registration($this->userRepositoryMock);
    }

    protected function getExpectedValidRequestType(): string
    {
        return 'user-registration';
    }

    public function testProcessPositive()
    {
        $processor = $this->getProcessorInstance();

        $payload = new UsernamePasswordPayload();
        $payload->username = 'TestUserNew';
        $payload->password = 'testPassword';

        $request = $this->makeRequest(123, 555, 'user-registration', $payload);
        $response = $this->makeResponse($request);

        $user = new User();
        $user->username = 'TestUserNew';
        $user->password = 'testPassword';

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('create')
            ->with($this->callback(function (User $user) {
                $this->assertEquals('TestUserNew', $user->username);
                $this->assertContains('$2y$10$', $user->password);

                return true;
            }))
            ->willReturn(new Success(true))
        ;

        $actualResponse = \Amp\wait($processor->process($request, $response));

        $this->assertResponseBasic($actualResponse, 123, 555, 'user-registered');
        $this->assertEquals('TestUserNew', $actualResponse->getPayload()['username']);
    }

    public function testProcessNegative()
    {
        $processor = $this->getProcessorInstance();

        $payload = new UsernamePasswordPayload();
        $payload->username = 'TestUserNew';
        $payload->password = 'testPassword';

        $request = $this->makeRequest(123, 555, 'user-registration', $payload);
        $response = $this->makeResponse($request);

        $user = new User();
        $user->username = 'TestUserNew';
        $user->password = 'testPassword';

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('create')
            ->with($this->callback(function (User $user) {
                $this->assertEquals('TestUserNew', $user->username);
                $this->assertContains('$2y$10$', $user->password);

                return true;
            }))
            ->willReturn(new Success(false))
        ;

        $actualResponse = \Amp\wait($processor->process($request, $response));

        $this->assertResponseBasic($actualResponse, 123, 555, 'user-registration-error');
        $this->assertEquals('Username busy', $actualResponse->getPayload()['message']);
    }
}
