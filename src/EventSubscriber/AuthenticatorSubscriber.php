<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Http\Event\DeauthenticatedEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * @property RequestStack requestStack
 * @property LoggerInterface securityLogger
 */
class AuthenticatorSubscriber implements EventSubscriberInterface
{

    public function __construct(LoggerInterface $securityLogger,
                                RequestStack $requestStack)
    {
        $this->securityLogger = $securityLogger;
        $this->requestStack = $requestStack;

    }

    /** @return array<string> */
    public static function getSubscribedEvents()
    {
        return [
            AuthenticationEvents::AUTHENTICATION_FAILURE        => 'onSecurityAuthenticationFailure',
            AuthenticationEvents::AUTHENTICATION_SUCCESS        => 'onSecurityAuthenticationSuccess',
            SecurityEvents::INTERACTIVE_LOGIN                   => 'onSecurityInteractiveLogin',
            'Symfony\Component\Security\Http\Event\LogoutEvent' => 'onSecurityLogout',
            'security.logout_on_change'                         => 'onSecurityLogoutOnChange',
            SecurityEvents::SWITCH_USER                         => 'onSecuritySwitchUser'

        ];
    }

    public function onSecurityAuthenticationFailure(AuthenticationFailureEvent $event): void
    {
        ['user_IP'=> $userIP] = $this->getRouteNameAndUserIp();

        /** @var  TokenInterface $securityToken */
        $securityToken = $event->getAuthenticationToken();

        ['username' => $usernameEntered] = $securityToken-> getCredentials();

        $this->securityLogger->info("Un utilisateur ayant l'adresse IP '{$userIP}' a tenté de s'authentifier sans succès avec  l'username suivant: '{$usernameEntered}'");
    }

    public function onSecurityAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {

        [
            'route_name' => $routeName,
            'user_IP'    => $userIP
        ]  = $this->getRouteNameAndUserIp();

        if (empty($event->getAuthenticationToken()->getRoleNames())){
            $this->securityLogger->info("Oh, un utilisateur anonyme ayant  l'adresse IP '{$userIP}'  est apparu  sur la route: '{$routeName}' :) . ");
        } else {
            /** @var TokenInterface $securityToken */
            $securityToken = $event->getAuthenticationToken();
            
            $username = $this->getUsername($securityToken);

            $this->securityLogger->info("un utilisateur anonyme ayant  l'adresse IP '{$userIP}' a évolué en entité User avec username'{$username}' :) .");
        }

    }


    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
    {
        ['user_IP'=> $userIP] = $this->getRouteNameAndUserIp();

        /** @var  TokenInterface $securityToken */
        $securityToken = $event->getAuthenticationToken();

        $username = $this->getUsername($securityToken);

        $this->securityLogger->info("Un utilisateur ayant l'adresse IP '{$userIP}' a évolué en entité User avec username'{$username}' :) .");



    }

    public function onSecurityLogout(LogoutEvent $event): void
    {
        /** @var RedirectResponse|null $response */
        $response = $event->getResponse();

        /** @var  TokenInterface|null $securityToken */
        $securityToken = $event->getToken();

        if(!$response || !$securityToken){
            return;
        }

        ['user_IP'=> $userIP] = $this->getRouteNameAndUserIp();

        $username = $this->getUsername($securityToken);

        $targetUrl = $response->getTargetUrl();

        $this->securityLogger->info("Un utilisateur ayant l'adresse IP '{$userIP}' et  l'username'{$username}'
        s'est déconnecté et a été redirigé vers l'url suivante: '{$targetUrl}' :) .");

    }

    public function onSecurityLogoutOnChange(DeauthenticatedEvent $event): void
    {

    }

    public function onSecuritySwitchUser(SwitchUserEvent $event): void
    {

    }

    /**
     * Return the user Ip and the name of the route where the user has arrived
     *
     * @return array{user_IP: string|null, route_name: mixed}
     */
    private function getRouteNameAndUserIp():array
    {
        $request = $this->requestStack->getCurrentRequest();

        if(!$request){
            return [
                'user_IP'   =>'Inconnue',
                'route_name'=>'Inconnue'
            ];
        }

        return [
            'user_IP'   => $request->getClientIp() ?? 'Inconnue',
            'route_name'=> $request->get('_route')
        ];
    }

    private function getUsername(TokenInterface $securityToken): string
    {
        /** @var User $user */
        $user = $securityToken->getUser();

        return $user->getUsername();
    }
}
