<?php

declare(strict_types=1);

namespace oxidprojects\DI\Tests\Makaira\Connect;

use Makaira\Connect\Core\Autosuggester;
use Makaira\Connect\Event\AutoSuggesterResponseEvent;
use Makaira\Connect\Event\ModifierQueryRequestEvent;
use Makaira\Connect\Event\SearchResponseEvent;
use Makaira\Connect\IntegrationTest;
use Makaira\Connect\Utils\OperationalIntelligence;
use Makaira\Query;
use Makaira\Result;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ApiEventTest extends IntegrationTest
{
    public function testQueryAutosuggesterEvent()
    {
        //Arrange
        $container = $this->getContainer();
        $event = $container->get(EventDispatcherInterface::class);
        $autosuggester = $container->get(Autosuggester::class);
        $query = new Query(['searchPhrase' => 'Tisch']);

        //Act
        $event->addListener(ModifierQueryRequestEvent::NAME_AUTOSUGGESTER, [$this, 'listenerModifierQueryRequest']);

        $autosuggester->modifyRequest($query);
        $accept = $query->searchPhrase;

        //Assert
        $this->assertEquals('Bett', $accept);
    }

    public function testQuerySearchEvent()
    {
        //Arrange
        $container = $this->getContainer();
        $event = $container->get(EventDispatcherInterface::class);
        $makaira_connect_request_handler = new \makaira_connect_request_handler();
        $reflectionClass = new \ReflectionClass(\makaira_connect_request_handler::class);
        $methodModifyRequest = $reflectionClass->getMethod('modifyRequest');
        $methodModifyRequest->setAccessible(true);

        $query = new Query(['searchPhrase' => 'Tische']);

        //Act
        $event->addListener(ModifierQueryRequestEvent::NAME_SEARCH, [$this, 'listenerModifierQueryRequest']);

        $methodModifyRequest->invoke($makaira_connect_request_handler, $query);
        $accept = $query->searchPhrase;

        //Assert
        $this->assertEquals('Bett', $accept);
    }

    public function testResponseSearchEvent()
    {
        //Arrange
        $container = $this->getContainer();
        $event = $container->get(EventDispatcherInterface::class);
        $makaira_connect_request_handler = new \makaira_connect_request_handler();

        $productIds = ['1234'];
        $expect = ['1234', 'Extra ID'];

        //Act
        $event->addListener(SearchResponseEvent::NAME, [$this, 'listenerSearchResponse']);
        $makaira_connect_request_handler->afterSearchRequest($productIds);

        //Assert
        $this->assertEquals($expect, $productIds);
    }

    public function testResponseAutoSuggesterResponseEvent()
    {
        //Arrange
        $container = $this->getContainer();
        $event = $container->get(EventDispatcherInterface::class);
        $autosuggester = $container->get(Autosuggester::class);

        $result = ['count' => 33];

        //Act
        $event->addListener(AutoSuggesterResponseEvent::NAME, [$this, 'listenerResponseAutoSuggester']);
        $autosuggester->afterSearchRequest($result);

        //Assert
        $this->assertEquals(66, $result['count']);
    }

    public function listenerResponseAutoSuggester(AutoSuggesterResponseEvent $event) {
        $result = $event->getResult();
        $this->assertInstanceOf(\ArrayObject::class, $result);
        $result['count'] = 66;
    }

    public function listenerModifierQueryRequest(ModifierQueryRequestEvent $event) {
        $event->getQuery()->searchPhrase = 'Bett';
    }

    public function listenerSearchResponse(SearchResponseEvent $event)
    {
        $productIds = $event->getProductIds();
        $productIds[] = 'Extra ID';
    }

    protected function setUp(): void
    {
        $container = $this->getContainer();
        $event = $container->get(EventDispatcherInterface::class);
        $event->removeListener(ModifierQueryRequestEvent::NAME_AUTOSUGGESTER, [$this, 'listenerModifierQueryRequest']);
        $event->removeListener(ModifierQueryRequestEvent::NAME_SEARCH, [$this, 'listenerModifierQueryRequest']);
        $event->removeListener(SearchResponseEvent::NAME, [$this, 'listenerSearchResponse']);
        $event->removeListener(AutoSuggesterResponseEvent::NAME, [$this, 'listenerResponseAutoSuggester']);
    }
}
