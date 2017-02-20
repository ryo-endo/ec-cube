<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */


namespace Eccube\Controller;

use Eccube\Application;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ContactController
{
    /**
     * お問い合わせ画面.
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function index(Application $app, Request $request)
    {
        $builder = $app['form.factory']->createBuilder('contact');

        if ($app->isGranted('ROLE_USER')) {
            $this->createContact($app, $builder);
        }

        // FRONT_CONTACT_INDEX_INITIALIZE
        $event = new EventArgs(
            array(
                'builder' => $builder,
            ),
            $request
        );
        $app['eccube.event.dispatcher']->dispatch(EccubeEvents::FRONT_CONTACT_INDEX_INITIALIZE, $event);

        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            switch ($request->get('mode')) {
                case 'confirm':
                    $data = $form->getData();
                    $app['session']->set('contact', $data);

                    return $app->redirect($app->url('contact_confirm'));
                    break;

                case 'complete':
                    $data = $form->getData();

                    $event = new EventArgs(
                        array(
                            'form' => $form,
                            'data' => $data,
                        ),
                        $request
                    );
                    $app['eccube.event.dispatcher']->dispatch(EccubeEvents::FRONT_CONTACT_INDEX_COMPLETE, $event);

                    $data = $event->getArgument('data');

                    // メール送信
                    $app['eccube.service.mail']->sendContactMail($data);

                    return $app->redirect($app->url('contact_complete'));
                    break;
            }
        }

        return $app->render('Contact/index.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @param Application $app
     * @param Request     $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function confirm(Application $app, Request $request)
    {
        /** @var $builder FormBuilder*/
        $builder = $app['form.factory']->createBuilder('contact');

        if ($app->isGranted('ROLE_USER')) {
            $this->createContact($app, $builder);
        }

        $builder->setAttribute('freeze', true);
        /** @var $form FormInterface */
        $form = $builder->getForm();
        if (!$app['session']->has('contact')) {
            throw new NotFoundHttpException();
        }
        $data = $app['session']->get('contact');
        // Error choice from entity
        if (is_object($data['pref'])) {
            $id = $data['pref']->getId();
            $data['pref'] = $app['eccube.repository.master.pref']->find($id);
        }
        $form->setData($data);

        $app['session']->remove('contact');

        return $app->render('Contact/confirm.twig', array(
            'form' => $form->createView(),
        ));
    }
    /**
     * お問い合わせ完了画面.
     *
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function complete(Application $app)
    {
        return $app->render('Contact/complete.twig');
    }

    /**
     * @param Application $app
     * @param $builder
     */
    private function createContact(Application $app, &$builder)
    {
        $user = $app['user'];
        $builder->setData(
            array(
                'name01' => $user->getName01(),
                'name02' => $user->getName02(),
                'kana01' => $user->getKana01(),
                'kana02' => $user->getKana02(),
                'zip01' => $user->getZip01(),
                'zip02' => $user->getZip02(),
                'pref' => $user->getPref(),
                'addr01' => $user->getAddr01(),
                'addr02' => $user->getAddr02(),
                'tel01' => $user->getTel01(),
                'tel02' => $user->getTel02(),
                'tel03' => $user->getTel03(),
                'email' => $user->getEmail(),
            )
        );
    }
}
