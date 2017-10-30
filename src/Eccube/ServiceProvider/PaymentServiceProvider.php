<?php

namespace Eccube\ServiceProvider;

use Doctrine\Common\Collections\ArrayCollection;
use Eccube\Repository\DeliveryRepository;
use Eccube\Service\PurchaseFlow\Processor\DisplayStatusValidator;
use Eccube\Service\PurchaseFlow\Processor\StockValidator;
use Eccube\Service\PurchaseFlow\PurchaseFlow;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class PaymentServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['payment.method'] = $app->protect(function ($class, $form) use ($app) {
            $PaymentMethod = new $class;
            $PaymentMethod->setApplication($app);
            $PaymentMethod->setFormType($form);
            return $PaymentMethod;
        });

        $app['payment.method.request'] = $app->protect(function ($class, $form, $request) use ($app) {
            $PaymentMethod = new $class;
            $PaymentMethod->setApplication($app);
            $PaymentMethod->setFormType($form);
            $PaymentMethod->setRequest($request);
            return $PaymentMethod;
        });

        $app['eccube.service.payment'] = $app->protect(function ($class) use ($app) {
            $Service = new $class($app['request_stack']);

            return $Service;
        });
    }
}