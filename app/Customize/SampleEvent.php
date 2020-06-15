<?php
namespace Customize;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Eccube\Event\TemplateEvent;

class SampleEvent implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'index.twig' => ['onTemplateProductDetail', 10],
        ];
    }

    /**
     * Append JS to display maker
     *
     * @param TemplateEvent $templateEvent
     */
    public function onTemplateProductDetail(TemplateEvent $templateEvent)
    {

        $templateEvent->addAsset('sample.js');
        $templateEvent->addAsset('sample.css');
        $templateEvent->addAsset('custom_style.css');
        $templateEvent->addSnippet('sample.twig');
    }
}
