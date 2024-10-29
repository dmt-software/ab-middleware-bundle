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
class AbMiddlewareListener implements EventSubscriberInterface
{
    public function __construct(
        protected AbService $abService,
        protected string $cookieName = 'ab-uid',
        protected string $cookieExpires = '+1 month',
        protected string $cookieDomain = '',
        protected string $cookiePath = '/',
        protected bool $cookieSecure = false,
        protected bool $cookieHttpOnly = true,
        protected string $cookieSameSite = 'Lax'
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

        if ($request->cookies->has($this->cookieName)) {
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
