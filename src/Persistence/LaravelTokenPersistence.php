<?php


namespace Cerpus\Helper\Persistence;


use Illuminate\Contracts\Cache\Repository;
use kamermans\OAuth2\Persistence\TokenPersistenceInterface;
use kamermans\OAuth2\Token\TokenInterface;

class LaravelTokenPersistence implements TokenPersistenceInterface
{
    /**
     * @var Repository
     */
    private $cache;

    /**
     * @var string
     */
    private $cacheKey;

    private $ttl = 3600;

    public function __construct(Repository $cache, $cacheKey, $defaultTTL = null)
    {
        $this->cache = $cache;
        $this->ttl = $defaultTTL ?? $this->ttl;
        $this->cacheKey = $cacheKey ?? $this->cacheKey;
    }

    public function restoreToken(TokenInterface $token)
    {
        $data = $this->cache->get($this->cacheKey);

        if (!is_array($data)) {
            return null;
        }

        return $token->unserialize($data);
    }

    public function saveToken(TokenInterface $token)
    {
        $expireAt = $token->getExpiresAt() ?? $this->ttl;
        $this->cache->set($this->cacheKey, $token->serialize(), $expireAt);
    }

    public function deleteToken()
    {
        $this->cache->forget($this->cacheKey);
    }

    public function hasToken()
    {
        return $this->cache->has($this->cacheKey);
    }
}
