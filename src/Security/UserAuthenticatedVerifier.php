<?php

namespace App\Security;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;

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

    public function getHasAccessInCurrentRoute(array $roleRoute) : ?Response
    {
        $userRoles = $this->getUserRolesAuthenticated();
        $haveRoleRouteAuthenticated = (count(array_intersect($userRoles, $roleRoute)) > 0);
        if (!$haveRoleRouteAuthenticated) {
            return new Response(
                json_encode(['status' => '401', 'message' => 'Usuário não permitido para essa sessão.']),
                401,
                ['Content-Type' => 'application/json; charset=utf-8']);
        }
        return null;
    }
}