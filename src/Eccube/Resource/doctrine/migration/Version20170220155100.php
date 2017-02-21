<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Eccube\Application;
use Eccube\Entity\PageLayout;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170220155100 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $app = Application::getInstance();
        /** @var EntityManager $em */
        $em = $app["orm.em"];

        $DeviceType = $app['eccube.repository.master.device_type']->find(10);

        $PageLayout = new PageLayout();
        $PageLayout
            ->setDeviceType($DeviceType)
            ->setName('お問い合わせ(確認ページ)')
            ->setUrl('contact_confirm')
            ->setFileName('Contact/confirm')
            ->setEditFlg(2)
            ->setMetaRobots('noindex');
        $em->persist($PageLayout);
        $em->flush();
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
