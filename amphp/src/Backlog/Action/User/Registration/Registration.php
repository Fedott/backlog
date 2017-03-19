<?php declare(strict_types=1);
namespace Fedot\Backlog\Action\User\Registration;

use Amp\Deferred as Promisor;
use Fedot\Backlog\Action\AbstractAction;
use Fedot\Backlog\Action\ErrorPayload;
use Fedot\Backlog\Action\User\Login\UsernamePasswordPayload;
use Fedot\Backlog\Model\User;
use Fedot\Backlog\Repository\UserRepository;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\ResponseInterface;

class Registration extends AbstractAction
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

        $user = new User(
            $payload->username,
            password_hash($payload->password, PASSWORD_DEFAULT)
        );

        $result = yield $this->userRepository->create($user);

        if ($result) {
            $response = $response->withType('user-registered');

            $responsePayload = new UsernamePayload();
            $responsePayload->username = $user->getUsername();

            $response = $response->withPayload((array) $responsePayload);
        } else {
            $response = $response->withType('user-registration-error');
            $response = $response->withPayload((array) new ErrorPayload('Username busy'));
        }

        $promisor->resolve($response);
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
