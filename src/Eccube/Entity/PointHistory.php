<?php

namespace Eccube\Entity;

use Doctrine\ORM\Mapping as ORM;

if (!class_exists('\Eccube\Entity\PointHistory')) {
    /**
     * PointHistory
     *
     * @ORM\Table(name="dtb_point_history")
     * @ORM\InheritanceType("SINGLE_TABLE")
     * @ORM\HasLifecycleCallbacks()
     * @ORM\Entity(repositoryClass="Eccube\Repository\PointHistoryRepository")
     */
    class PointHistory extends \Eccube\Entity\AbstractEntity
    {
        /**
         * @return string
         */
        public function __toString()
        {
            return (string) $this->name;
        }

        /**
         * @var int
         *
         * @ORM\Column(name="id", type="integer", options={"unsigned":true})
         * @ORM\Id
         * @ORM\GeneratedValue(strategy="IDENTITY")
         */
        private $id;

        /**
         * @var int
         *
         * @ORM\Column(name="point", type="integer")
         */
        private $point = 0;
        
        /**
         * @var \Eccube\Entity\Customer
         *
         * @ORM\ManyToOne(targetEntity="Eccube\Entity\Customer", inversedBy="Orders")
         * @ORM\JoinColumns({
         *   @ORM\JoinColumn(name="customer_id", referencedColumnName="id")
         * })
         */
        private $Customer;
        
        /**
         * @var \Eccube\Entity\Order
         *
         * @ORM\ManyToOne(targetEntity="Eccube\Entity\Order", inversedBy="MailHistories")
         * @ORM\JoinColumns({
         *   @ORM\JoinColumn(name="order_id", referencedColumnName="id")
         * })
         */
        private $Order;

        /**
         * @var \DateTime
         *
         * @ORM\Column(name="create_date", type="datetimetz")
         */
        private $create_date;

        /**
         * Constructor
         */
        public function __construct()
        {
            // $this->PaymentOptions = new \Doctrine\Common\Collections\ArrayCollection();
            // $this->PointHistoryFees = new \Doctrine\Common\Collections\ArrayCollection();
            // $this->PointHistoryTimes = new \Doctrine\Common\Collections\ArrayCollection();
        }

        /**
         * Get id.
         *
         * @return int
         */
        public function getId()
        {
            return $this->id;
        }

        /**
         * Set point.
         *
         * @param int $point
         *
         * @return PointHistory
         */
        public function setPoint($point)
        {
            $this->point = $point;
    
            return $this;
        }
    
        /**
         * Get point.
         *
         * @return int
         */
        public function getPoint()
        {
            return $this->point;
        }
        
        /**
         * Set createDate.
         *
         * @param \DateTime $createDate
         *
         * @return Product
         */
        public function setCreateDate($createDate)
        {
            $this->create_date = $createDate;

            return $this;
        }

        /**
         * Get createDate.
         *
         * @return \DateTime
         */
        public function getCreateDate()
        {
            return $this->create_date;
        }
        
        /**
         * Set customer.
         *
         * @param \Eccube\Entity\Customer|null $customer
         *
         * @return Order
         */
        public function setCustomer(\Eccube\Entity\Customer $customer = null)
        {
            $this->Customer = $customer;

            return $this;
        }

        /**
         * Get customer.
         *
         * @return \Eccube\Entity\Customer|null
         */
        public function getCustomer()
        {
            return $this->Customer;
        }
        
        /**
         * Set order.
         *
         * @param \Eccube\Entity\Order|null $order
         *
         * @return MailHistory
         */
        public function setOrder(\Eccube\Entity\Order $order = null)
        {
            $this->Order = $order;

            return $this;
        }

        /**
         * Get order.
         *
         * @return \Eccube\Entity\Order|null
         */
        public function getOrder()
        {
            return $this->Order;
        }
    }
}
