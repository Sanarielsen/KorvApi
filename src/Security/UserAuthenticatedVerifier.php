<?php

namespace App\Security;

use Symfony\Bundle\SecurityBundle\Security;

class UserAuthenticatedVerifier {

    private Security $security;

    public function __construct(Security $security
    ){
        $this->security = $security;
    }

    public function getUserRolesAuthenticated() : array
    {
        $currentUser = $this->security->getUser();
        if ( !$currentUser ) {
            return ['PUBLIC_ACCESS'];
        }
        return $currentUser->getRoles();
    }

    public function getHasAccessInCurrentRoute(array $roleRoute) : bool
    {
        $userRoles = $this->getUserRolesAuthenticated();
        $haveRoleRouteAuthenticated = (count(array_intersect($userRoles, $roleRoute)) > 0);
        if (!$haveRoleRouteAuthenticated) {
            return false;
        }
        return true;
    }
}