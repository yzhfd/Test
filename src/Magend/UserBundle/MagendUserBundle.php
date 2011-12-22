<?php

namespace Magend\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class MagendUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
