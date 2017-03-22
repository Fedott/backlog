<?php declare(strict_types = 1);
namespace Tests\Fedot\Backlog\Action\User;

use Amp\Success;
use Fedot\Backlog\Action\ActionInterface;
use Fedot\Backlog\Action\User\Login\UsernamePasswordPayload;
use Fedot\Backlog\Action\User\Registration\Registration;
use Fedot\Backlog\Model\User;
use Fedot\Backlog\Repository\UserRepository;
use PHPUnit_Framework_MockObject_MockObject;
use Tests\Fedot\Backlog\ActionTestCase;

class RegistrationTest extends ActionTestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|UserRepository
     */
    protected $userRepositoryMock;

    protected function getProcessorInstance(): ActionInterface
    {
        $this->userRepositoryMock = $this->createMock(UserRepository::class);

        return new Registration($this->userRepositoryMock);
    }

    protected function getExpectedValidRequestType(): string
    {
        return 'user-registration';
    }

    protected function getExpectedPayloadType(): ?string
    {
        return UsernamePasswordPayload::class;
    }

    public function testProcessPositive()
    {
        $processor = $this->getProcessorInstance();

        $payload = new UsernamePasswordPayload();
        $payload->username = 'TestUserNew';
        $payload->password = 'testPassword';

        $request = $this->makeRequest(123, 555, 'user-registration', $payload);
        $response = $this->makeResponse($request);

        $user = new User(
            'TestUserNew',
            'testPassword'
        );

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('create')
            ->with($this->callback(function (User $user) {
                $this->assertEquals('TestUserNew', $user->getUsername());
                $this->assertContains('$2y$10$', $user->getPasswordHash());

                return true;
            }))
            ->willReturn(new Success(true))
        ;

        $actualResponse = \Amp\Promise\wait($processor->process($request, $response));

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

        $user = new User('TestUserNew', 'testPassword');

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('create')
            ->with($this->callback(function (User $user) {
                $this->assertEquals('TestUserNew', $user->getUsername());
                $this->assertContains('$2y$10$', $user->getPasswordHash());

                return true;
            }))
            ->willReturn(new Success(false))
        ;

        $actualResponse = \Amp\Promise\wait($processor->process($request, $response));

        $this->assertResponseBasic($actualResponse, 123, 555, 'user-registration-error');
        $this->assertEquals('Username busy', $actualResponse->getPayload()['message']);
    }
}
