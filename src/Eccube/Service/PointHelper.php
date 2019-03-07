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

namespace Eccube\Service;

use Doctrine\ORM\EntityManagerInterface;
use Eccube\Common\EccubeConfig;
use Eccube\Entity\ItemHolderInterface;
use Eccube\Entity\Master\OrderItemType;
use Eccube\Entity\Master\TaxDisplayType;
use Eccube\Entity\Master\TaxType;
use Eccube\Entity\OrderItem;
use Eccube\Entity\PointHistory;
use Eccube\Repository\BaseInfoRepository;
use Eccube\Repository\PointHistoryRepository;
use Eccube\Service\PurchaseFlow\Processor\PointProcessor;

class PointHelper
{
    /**
     * @var BaseInfoRepository
     */
    protected $baseInfoRepository;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;
    
    /**
     * @var PointHistoryRepository
     */
    protected $pointHitoryRepository;
    
    /**
     * @var EccubeConfig
     */
    private $eccubeConfig;

    /**
     * PointHelper constructor.
     *
     * @param BaseInfoRepository $baseInfoRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(BaseInfoRepository $baseInfoRepository, EntityManagerInterface $entityManager, PointHistoryRepository $pointHistoryRepository, EccubeConfig $eccubeConfig)
    {
        $this->baseInfoRepository = $baseInfoRepository;
        $this->entityManager = $entityManager;
        $this->pointHistoryRepository = $pointHistoryRepository;
        $this->eccubeConfig = $eccubeConfig;
    }

    /**
     * ポイント設定が有効かどうか.
     *
     * @return bool
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isPointEnabled()
    {
        $BaseInfo = $this->baseInfoRepository->get();

        return $BaseInfo->isOptionPoint();
    }

    /**
     * ポイントを金額に変換する.
     *
     * @param $point ポイント
     *
     * @return float|int 金額
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function pointToPrice($point)
    {
        $BaseInfo = $this->baseInfoRepository->get();

        return intval($point * $BaseInfo->getPointConversionRate());
    }

    /**
     * ポイントを値引き額に変換する. マイナス値を返す.
     *
     * @param $point ポイント
     *
     * @return float|int 金額
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function pointToDiscount($point)
    {
        return $this->pointToPrice($point) * -1;
    }

    /**
     * 金額をポイントに変換する.
     *
     * @param $price
     *
     * @return float ポイント
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function priceToPoint($price)
    {
        $BaseInfo = $this->baseInfoRepository->get();

        return floor($price / $BaseInfo->getPointConversionRate());
    }

    /**
     * 明細追加処理.
     *
     * @param ItemHolderInterface $itemHolder
     * @param integer $discount
     */
    public function addPointDiscountItem(ItemHolderInterface $itemHolder, $discount)
    {
        $DiscountType = $this->entityManager->find(OrderItemType::class, OrderItemType::POINT);
        $TaxInclude = $this->entityManager->find(TaxDisplayType::class, TaxDisplayType::INCLUDED);
        $Taxation = $this->entityManager->find(TaxType::class, TaxType::NON_TAXABLE);

        $OrderItem = new OrderItem();
        $OrderItem->setProductName($DiscountType->getName())
            ->setPrice($discount)
            ->setQuantity(1)
            ->setTax(0)
            ->setTaxRate(0)
            ->setTaxRuleId(null)
            ->setRoundingType(null)
            ->setOrderItemType($DiscountType)
            ->setTaxDisplayType($TaxInclude)
            ->setTaxType($Taxation)
            ->setOrder($itemHolder)
            ->setProcessorName(PointProcessor::class);
        $itemHolder->addItem($OrderItem);
    }

    /**
     * 既存のポイント明細を削除する.
     *
     * @param ItemHolderInterface $itemHolder
     */
    public function removePointDiscountItem(ItemHolderInterface $itemHolder)
    {
        foreach ($itemHolder->getItems() as $item) {
            if ($item->getProcessorName() == PointProcessor::class) {
                $itemHolder->removeOrderItem($item);
                $this->entityManager->remove($item);
            }
        }
    }

    public function prepare(ItemHolderInterface $itemHolder, $point)
    {
        $Customer = $itemHolder->getCustomer();
        $usePoint = $point;
        
        // まずは期間限定ポイントを優先して消化する
        {
            // 期間限定ポイントの一覧を取得する(有効期限が近い順)
            $expirationPoints = $this->pointHistoryRepository->getExpirationPoints($Customer);

            // 期間限定ポイントを１つずつ確認して、次に消費する期間限定ポイントのレコードを探す
            /** @var array $expirationPointList */
            foreach ($expirationPoints as $expirationPoint) {
                $expirationDate = $expirationPoint['date'];
                $offsetPoint = $expirationPoint['point'];

                if (0 >= $offsetPoint) {
                    continue;
                }

                // 期間限定の獲得ポイントに対応する、期間限定の利用ポイントとして登録する
                $val = 0;
                if ($usePoint >= $offsetPoint) {
                    $val = $offsetPoint;
                } else {
                    $val = $usePoint;
                }
                
                // ユーザの保有ポイントを減算する履歴を追加
                $obj = new PointHistory();
                $obj->setRecordType(PointHistory::TYPE_USE);
                $obj->setRecordEvent(PointHistory::EVENT_SHOPPING);
                $obj->setPoint(-$val);
                $obj->setCustomer($Customer);
                $obj->setOrder($itemHolder);
                $obj->setExpirationDate($expirationDate);
                $em = $this->entityManager;
                $em->persist($obj);
                $em->flush($obj);
                
                $usePoint -= $val;

                // 利用ポイントをすべて消化したら、これ以上はループ不要
                if (0 >= $usePoint) {
                    break;
                }
            }
        }
        
        // 残りを通常の利用ポイントとして登録する
        // ユーザの保有ポイントを減算する履歴を追加
        if ($usePoint > 0) {
            $obj = new PointHistory();
            $obj->setRecordType(PointHistory::TYPE_USE);
            $obj->setRecordEvent(PointHistory::EVENT_SHOPPING);
            $obj->setPoint(-$usePoint);
            $obj->setCustomer($Customer);
            $obj->setOrder($itemHolder);
            $em = $this->entityManager;
            $em->persist($obj);
            $em->flush($obj);
        }
        
        // 再集計
        $this->recount($Customer);
    }

    public function rollback(ItemHolderInterface $itemHolder, $point)
    {
        $Customer = $itemHolder->getCustomer();
        
        // 利用したポイントをユーザに戻す履歴を追加
        $obj = new PointHistory();
        $obj->setRecordType(PointHistory::TYPE_ADD);
        $obj->setRecordEvent(PointHistory::EVENT_SHOPPING);
        $obj->setPoint($point);
        $obj->setCustomer($Customer);
        $obj->setOrder($itemHolder);
        $em = $this->entityManager;
        $em->persist($obj);
        $em->flush($obj);
        
        // 再集計
        $this->recount($Customer);
    }
    
    public function recount(\Eccube\Entity\Customer $Customer) {
        $newPoint = $this->pointHistoryRepository->getCurrentPoint($Customer);
        $Customer->setPoint($newPoint);
        
        $this->entityManager->flush($Customer);
    }
    
    // 会員登録時ポイントを付与する
    public function addEntryPoint(\Eccube\Entity\Customer $Customer) {
        $point = $this->eccubeConfig['eccube_point_entry_point'];
        
        $now = new \DateTime();
        $expirationDate = $now->modify($this->eccubeConfig['eccube_point_entry_lifetime']);
        
        $obj = new PointHistory();
        $obj->setRecordType(PointHistory::TYPE_ADD);
        $obj->setRecordEvent(PointHistory::EVENT_ENTRY);
        $obj->setPoint($point);
        $obj->setCustomer($Customer);
        $obj->setExpirationDate($expirationDate);
        $em = $this->entityManager;
        $em->persist($obj);
        $em->flush($obj);
        
        // 再集計
        $this->recount($Customer);
    }
}
