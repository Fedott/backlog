<?php declare(strict_types = 1);
namespace Fedot\Backlog\Request\Processor\User;

use Amp\Promisor;
use Fedot\Backlog\Model\User;
use Fedot\Backlog\Payload\ErrorPayload;
use Fedot\Backlog\Payload\UsernamePasswordPayload;
use Fedot\Backlog\Payload\UsernamePayload;
use Fedot\Backlog\Repository\UserRepository;
use Fedot\Backlog\Request\Processor\AbstractProcessor;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\ResponseInterface;

class Registration extends AbstractProcessor
{
    /**
     * @var UserRepository
     */
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    protected function execute(Promisor $promisor, RequestInterface $request, ResponseInterface $response)
    {
        /** @var UsernamePasswordPayload $payload */
        $payload = $request->getAttribute('payloadObject');

        $user = new User();
        $user->username = $payload->username;
        $user->password = password_hash($payload->password, PASSWORD_DEFAULT);

        $result = yield $this->userRepository->create($user);

        if ($result) {
            $response = $response->withType('user-registered');

            $responsePayload = new UsernamePayload();
            $responsePayload->username = $user->username;

            $response = $response->withPayload((array) $responsePayload);
        } else {
            $response = $response->withType('user-registration-error');
            $response = $response->withPayload((array) new ErrorPayload('Username busy'));
        }

        $promisor->succeed($response);
    }

    public function getSupportedType(): string
    {
        return 'user-registration';
    }

    public function getExpectedRequestPayload(): string
    {
        return UsernamePasswordPayload::class;
    }
}
