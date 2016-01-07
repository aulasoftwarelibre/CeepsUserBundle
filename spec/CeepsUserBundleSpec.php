<?php

namespace spec\Ceeps\UserBundle;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CeepsUserBundleSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Ceeps\UserBundle\CeepsUserBundle');
    }
    
    function it_is_children_of_fos_user_bundle()
    {
        $this->getParent()->shouldBe('FOSUserBundle');
    }
}
