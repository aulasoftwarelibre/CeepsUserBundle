<?php

namespace spec\Ceeps\UserBundle\Security\User;

use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use PDias\SamlBundle\Saml\SamlAuth;
use PDias\SamlBundle\Security\User\SamlUser;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class FosBackendSamlUserProviderSpec extends ObjectBehavior
{
    const OPENID = 'http://open.id/user@domain.com/';
    const USERNAME = 'user@domain.com';
    const ROLES = ['ROLE_USER'];
    const ATTRIBUTES = [];

    function let(
        SamlAuth $auth,
        UserInterface $user,
        UserManagerInterface $userManager
    )
    {
        $this->beConstructedWith($auth, $userManager);

        $auth->getAttributes()->willReturn(self::ATTRIBUTES);
        $auth->getUsername()->willReturn(self::OPENID);
        $auth->isAuthenticated()->willReturn(true);

        $user->getUsername()->willReturn(self::USERNAME);
        $user->getRoles()->willReturn(self::ROLES);

        $userManager->findUserByUsername(self::USERNAME)->willReturn($user);
        $userManager->getClass()->willReturn(SamlUser::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Ceeps\UserBundle\Security\User\FosBackendSamlUserProvider');
    }
    
    function it_load_user_by_username(
        SamlAuth $auth,
        UserInterface $user,
        UserManagerInterface $userManager
    )
    {
        $auth->getAttributes()->shouldBeCalled();
        $auth->getUsername()->shouldBeCalled();
        $auth->isAuthenticated()->shouldBeCalled();

        $user->getUsername()->shouldBeCalled();
        $user->getRoles()->shouldBeCalled();

        $userManager->findUserByUsername(self::USERNAME)->shouldBeCalled();

        $samlUser = new SamlUser('user@domain.com', ['ROLE_USER'], []);

        $this->loadUserByUsername('user@domain.com')->shouldBeLike($samlUser);
    }

    function it_fails_when_user_is_not_authenticated(
        SamlAuth $auth,
        UserManagerInterface $userManager
    )
    {
        $username = 'unknown@domain.com';
        $auth->isAuthenticated()->shouldBeCalled()->willReturn(false);

        $this->shouldThrow(UsernameNotFoundException::class)->duringLoadUserByUsername($username);
    }
    
    function it_refresh_user_credentials(
        SamlUser $user
    )
    {
        $user->getUsername()->shouldBeCalled()->willReturn('user@domain.com');

        $samlUser = new SamlUser('user@domain.com', ['ROLE_USER'], []);

        $this->refreshUser($user)->shouldBeLike($samlUser);
    }
    
    function it_fails_when_refreshed_user_is_not_valid(
        UserInterface $user
    )
    {
        $this->shouldThrow(UnsupportedUserException::class)->duringRefreshUser($user);
    }

    function it_check_is_class_is_supported(
        UserManagerInterface $userManager
    )
    {
        $userManager->getClass()->shouldBeCalled();

        $this->supportsClass(SamlUser::class)->shouldBe(true);
    }

    function it_found_user_by_saml_id(
        UserInterface $user
    )
    {
        $openid = 'http://open.id/user@domain.com/';

        $this->findUserBySamlId($openid)->shouldBe($user);
    }
}
