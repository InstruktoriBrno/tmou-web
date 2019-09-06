<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\Organizators;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Enums\OrganizatorRole;
use InstruktoriBrno\TMOU\Model\Organizator;
use InstruktoriBrno\TMOU\OAuth2\Client\Provider\Keycloak;
use InstruktoriBrno\TMOU\Services\Organizators\FindOrganizatorByKeycloakKeyService;
use League\OAuth2\Client\Token\AccessToken;
use Nette\Application\LinkGenerator;
use Nette\Http\Request;
use Nette\Http\Response;
use Nette\Http\Session;
use Nette\Http\SessionSection;
use Nette\Security\User;
use Ramsey\Uuid\Uuid;

class LoginOrganizatorViaKeycloakFacade
{
    private const KEYCLOAK = 'keycloak';

    /** @var User */
    private $user;

    /** @var Request */
    private $request;

    /** @var Keycloak */
    private $keycloak;

    /** @var SessionSection */
    private $sessionSection;

    /** @var FindOrganizatorByKeycloakKeyService */
    private $findOrganizatorByKeycloakKeyService;

    /** @var Response */
    private $response;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var LinkGenerator */
    private $linkGenerator;

    public function __construct(
        User $user,
        Request $request,
        Response $response,
        Keycloak $keycloak,
        Session $session,
        FindOrganizatorByKeycloakKeyService $findOrganizatorByKeycloakKeyService,
        EntityManagerInterface $entityManager,
        LinkGenerator $linkGenerator
    ) {
        $this->user = $user;
        $this->response = $response;
        $this->request = $request;
        $this->keycloak = $keycloak;
        $this->sessionSection = $session->getSection(static::KEYCLOAK);
        $this->findOrganizatorByKeycloakKeyService = $findOrganizatorByKeycloakKeyService;
        $this->entityManager = $entityManager;
        $this->linkGenerator = $linkGenerator;
    }

    public function __invoke(): void
    {
        if ($this->user->isLoggedIn()) {
            throw new \InstruktoriBrno\TMOU\Facades\Organizators\Exceptions\AlreadyLoggedException();
        }

        $code = $this->request->getQuery('code');

        // Check if we have a code, if not, we start the authentication request
        if ($code === null) {
            $authUrl = $this->keycloak->getAuthorizationUrl();
            $this->sessionSection->offsetSet('state', $this->keycloak->getState()); // Store value for CSRF validation
            $this->response->redirect($authUrl);
            exit();
        }
        // We have a code so we can proceed to validation
        $state = $this->request->getQuery('state');
        if ($state === null || !$this->sessionSection->offsetExists('state') || $state !== $this->sessionSection->offsetGet('state')) {
            $this->sessionSection->offsetUnset('state');
            throw new \InstruktoriBrno\TMOU\Facades\Organizators\Exceptions\InvalidLoginRequestException(
                'This login request is invalid, either there is problem session storage, session expiration or this is attempt for CSRF attack.'
            );
        }

        // Now we have a validated everything so we can proceed to querying data and querying user profile
        try {
            /** @var AccessToken $token */
            $token = $this->keycloak->getAccessToken(
                'authorization_code',
                [
                    'code' => $code,
                ]
            );
            $user = $this->keycloak->getResourceOwner($token);

            $rawData = $user->toArray();
            $uuid = Uuid::fromString($rawData['sub']);
            if (! $uuid instanceof Uuid) {
                throw new \InstruktoriBrno\TMOU\Facades\Organizators\Exceptions\UnexpectedKeycloakKeyException;
            }
            $userEntity = ($this->findOrganizatorByKeycloakKeyService)($uuid);
            $existedBefore = false;
            if ($userEntity === null) {
                $userEntity = new Organizator(
                    $rawData['given_name'],
                    $rawData['family_name'],
                    $rawData['preferred_username'],
                    $rawData['email'],
                    $uuid,
                    $this->determineRoleFromGroups($rawData['groups'] ?? null)
                );
                $this->entityManager->persist($userEntity);
            } else {
                $existedBefore = true;
                $userEntity->updateDetails(
                    $rawData['given_name'],
                    $rawData['family_name'],
                    $rawData['preferred_username'],
                    $rawData['email'],
                    $this->determineRoleFromGroups($rawData['groups'] ?? null)
                );
                $this->entityManager->persist($userEntity);
            }
            $userEntity->touchLastLogin();

            if ($userEntity->getRole() !== null || $existedBefore) {
                $this->entityManager->flush();
                if ($userEntity->getRole() !== null) {
                    $this->user->login($userEntity->toIdentity());
                } else {
                    throw new \InstruktoriBrno\TMOU\Facades\Organizators\Exceptions\NotAllowedToLoginException();
                }
            } else {
                throw new \InstruktoriBrno\TMOU\Facades\Organizators\Exceptions\NotAllowedToLoginException();
            }

            // Logout (~ terminate keycloak session)
            // Due to missing docs the logout via REST API is not working: https://issues.jboss.org/browse/KEYCLOAK-2476
            $this->response->redirect($this->keycloak->getLogoutUrl([
                'redirect_uri' => $this->linkGenerator->link('Admin:postLogin'),
            ]));
            exit();
        } catch (\InstruktoriBrno\TMOU\Facades\Organizators\Exceptions\NotAllowedToLoginException $e) {
            $this->response->redirect($this->keycloak->getLogoutUrl([
                'redirect_uri' => $this->linkGenerator->link('Admin:postLogin', ['not_allowed' => 1]),
            ]));
            exit();
        } catch (\Exception $e) {
            if ($e instanceof \Nette\Application\AbortException) {
                throw $e;
            }
            $this->response->redirect($this->keycloak->getLogoutUrl([
                'redirect_uri' => $this->linkGenerator->link('Admin:postLogin', ['failed' => 1]),
            ]));
            exit();
        }
    }

    private function determineRoleFromGroups(?array $groups): ?OrganizatorRole
    {
        if ($groups === null || count($groups) === 0) {
            return null;
        }
        $roles = [];
        foreach ($groups as $group) {
            $roles[] = OrganizatorRole::mapFromGroup($group);
        }
        $roles = array_filter($roles);
        if (count($roles) > 0) {
            return reset($roles);
        }
        return null;
    }
}
