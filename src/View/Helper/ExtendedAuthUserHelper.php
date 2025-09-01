<?php

namespace App\View\Helper;

use Exception;
use TinyAuth\View\Helper\AuthUserHelper;

/**
 * AuthUser helper
 */
class ExtendedAuthUserHelper extends AuthUserHelper
{
    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);
    }

    /**
     * Get the full name of the User
     *
     * @return string
     */
    public function getFulName(): string
    {
        return $this->user('first_name') . " " . $this->user('last_name');
    }


}
