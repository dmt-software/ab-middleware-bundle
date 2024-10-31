<?php

namespace DMT\AbMiddlewareBundle\EventListener;

use DateMalformedStringException;
use DateTime;
use DMT\AbMiddleware\AbService;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: RequestEvent::class, method: 'onKernelRequest')]
#[AsEventListener(event: ResponseEvent::class, method: 'onKernelResponse')]
class AbMiddlewareSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected AbService $abService,
        protected string $cookieName = 'ab-uid',
        protected string $cookieExpires = '+1 month',
        protected ?string $cookieDomain = null,
        protected ?string $cookiePath = null,
        protected ?bool $cookieSecure = null,
        protected bool $cookieHttpOnly = true,
        protected ?string $cookieSameSite = 'Lax',
        protected string $overrideQueryParameter = 'ab-variant',
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 20],
            KernelEvents::RESPONSE => ['onKernelResponse', 20],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if ($request->query->has($this->overrideQueryParameter)) {
            $this->abService->setUid($request->query->get($this->overrideQueryParameter));
        } elseif ($request->cookies->has($this->cookieName)) {
            $this->abService->setUid($event->getRequest()->cookies->get($this->cookieName));
        }

        $uid = $this->abService->getUid();

        $request->attributes->set('ab-service', $this->abService);
        $request->attributes->set('ab-uid', $uid);
    }

    /**
     * @throws DateMalformedStringException
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();

        $cookie = new Cookie(
            name: $this->cookieName,
            value: $this->abService->getUid(),
            expire: new DateTime($this->cookieExpires),
            path: $this->cookiePath,
            domain: $this->cookieDomain,
            secure: $this->cookieSecure,
            httpOnly: $this->cookieHttpOnly,
            raw: false,
            sameSite: $this->cookieSameSite
        );

        $response->headers->setCookie($cookie);
    }
}
