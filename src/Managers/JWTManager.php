<?php


namespace Cerpus\Helper\Managers;


use Cerpus\AuthCore\JWTService;
use Cerpus\Helper\Exceptions\InvalidJWTException;
use Closure;
use Exception;
use Psr\Http\Message\RequestInterface;

class JWTManager
{
    private $authService;

    public function __construct(JWTService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @param callable $handler
     * @return Closure
     * @throws Exception
     */
    public function __invoke(callable $handler)
    {
        $jwt = $this->authService->getJwt();
        if( empty($jwt)){
            throw new InvalidJWTException("Unable to retrieve valid JWT");
        }

        return function (RequestInterface $request, array $options) use ($handler, $jwt) {
            return $handler($request->withHeader(
                'Authorization',
                sprintf('Bearer %s', $jwt)
            ), $options);
        };
    }
}