<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eccube\Repository;

use Eccube\Entity\DeliveryTime;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * DelivTimeRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DeliveryTimeRepository extends AbstractRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DeliveryTime::class);
    }

    /**
     * @deprecated since 3.0.0, to be removed in 3.1
     */
    public function findOrCreate(array $conditions)
    {
        $DeliveryTime = $this->findOneBy($conditions);

        if ($DeliveryTime instanceof \Eccube\Entity\DeliveryTime) {
            return $DeliveryTime;
        }

        $DeliveryTime = new \Eccube\Entity\DeliveryTime();
        $DeliveryTime->setDelivery($conditions['Delivery']);

        return $DeliveryTime;
    }
}
