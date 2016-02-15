<?php
/*
 * This file is part of the CeepsUserBundle.
 *
 * (c) Sergio Gómez <sergio@uco.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ceeps\UserBundle\Security\User;


use FOS\UserBundle\Model\UserManagerInterface;
use PDias\SamlBundle\Saml\SamlAuth;
use PDias\SamlBundle\Security\User\SamlUser;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class FosBackendSamlUserProvider implements UserProviderInterface
{
    /**
     * @var SamlAuth
     */
    protected $samlAuth;
    /**
     * @var UserManagerInterface
     */
    private $userManager;

    public function __construct(SamlAuth $samlAuth, UserManagerInterface $userManager)
    {
        $this->samlAuth = $samlAuth;
        $this->userManager = $userManager;
    }

    public function loadUserByUsername($username)
    {
        if ($this->samlAuth->isAuthenticated()) {
            $user = $this->findUserBySamlId($this->samlAuth->getUsername());

            if (!$user) {
                throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
            }

            return $user;
        }

        throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof SamlUser) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        $userClass = $this->userManager->getClass();

        return $userClass === $class || is_subclass_of($class, $userClass);
    }

    public function findUserBySamlId($samlId)
    {
        preg_match('#(?<email>(?<username>[^/]+)@(?<organization>[^/]+))#', $samlId, $matches);

        return $this->userManager->findUserByUsername($matches['username']);
    }
}