<?php

namespace DMT\AbMiddlewareBundle\Tests;

use DateMalformedStringException;
use DMT\AbMiddleware\AbService;
use DMT\AbMiddlewareBundle\AbMiddlewareBundle;
use DMT\AbMiddlewareBundle\EventListener\AbMiddlewareSubscriber;
use DMT\AbMiddlewareBundle\Tests\Util\App\Kernel;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

#[CoversClass(AbMiddlewareSubscriber::class)]
#[CoversClass(AbMiddlewareBundle::class)]
class AbMiddlewareEventSubscriberTest extends KernelTestCase
{
    private Container $container;
    private AbMiddlewareSubscriber $abMiddlewareListener;
    private AbService $abService;

    private string $cookieName = 'ab_test'; // see config.yml

    public static function getKernelClass(): string
    {
        return Kernel::class;
    }

    public function setUp(): void
    {
        parent::setUp();

        self::bootKernel();

        $this->container = static::getContainer();
        $this->abMiddlewareListener = $this->container->get(AbMiddlewareSubscriber::class);
        $this->abService = $this->container->get(AbService::class);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        restore_exception_handler();
    }

    public function testContainer(): void
    {
        $this->assertInstanceOf(Container::class, $this->container);
        $this->assertInstanceOf(AbMiddlewareSubscriber::class, $this->abMiddlewareListener);
        $this->assertInstanceOf(AbService::class, $this->abService);
    }

    protected function makeRequestEvent(): RequestEvent
    {
        $request = new Request();

        return new RequestEvent(
            static::$kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );
    }

    protected function makeResponseEvent(Request $request): ResponseEvent
    {
        return new ResponseEvent(
            static::$kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            new Response('hello world', 200)
        );
    }

    public function testRequestEvent(): void
    {
        $requestEvent = $this->makeRequestEvent();
        $request = $requestEvent->getRequest();

        $this->abMiddlewareListener->onKernelRequest($requestEvent);

        $this->assertSame($this->abService, $request->attributes->get('ab-service'));
        $this->assertNotEmpty($request->attributes->get('ab-uid'));
    }

    public function testSubsequentRequestEvent(): void
    {
        $requestEvent = $this->makeRequestEvent();
        $request = $requestEvent->getRequest();
        $request->cookies->set($this->cookieName, 'test-uid');

        $this->assertArrayHasKey($this->cookieName, $request->cookies->all());
        $this->assertEquals('test-uid', $request->cookies->get($this->cookieName));

        $this->abMiddlewareListener->onKernelRequest($requestEvent);

        $this->assertSame($this->abService, $request->attributes->get('ab-service'));
        $this->assertNotEmpty($request->attributes->get('ab-uid'));
        $this->assertEquals('test-uid', $request->attributes->get('ab-uid'));
    }

    /**
     * @throws DateMalformedStringException
     */
    public function testResponseEvent(): void
    {
        $requestEvent = $this->makeRequestEvent();
        $request = $requestEvent->getRequest();

        $this->abMiddlewareListener->onKernelRequest($requestEvent);

        $responseEvent = $this->makeResponseEvent($request);
        $response = $responseEvent->getResponse();

        $this->abMiddlewareListener->onKernelResponse($responseEvent);

        $cookies = $response->headers->getCookies();

        $this->assertNotEmpty($cookies);

        $cookie = $cookies[0];

        $this->assertEquals($this->cookieName, $cookie->getName());

        $this->assertNotEmpty($cookie->getValue());

        $this->assertEquals($request->attributes->get('ab-uid'), $cookie->getValue());
    }
}
