<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\LdapBundle\Listener;

use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Observe;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Claroline\CoreBundle\Menu\ConfigureMenuEvent;

/**
 * @Service()
 */
class LdapListener
{
    private $request;
    private $httpKernel;

    /**
     * @InjectParams({
     *     "request"    = @Inject("request_stack"),
     *     "httpKernel" = @Inject("http_kernel")
     * })
     */
    public function __construct(RequestStack $request, HttpKernelInterface $httpKernel)
    {
        $this->request = $request->getMasterRequest();
        $this->httpKernel = $httpKernel;
    }

    /**
     * @Observe("administration_tool_LDAP")
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(OpenAdministrationToolEvent $event)
    {
        $params = array();
        $params['_controller'] = 'ClarolineLdapBundle:Ldap:menu';
        $this->redirect($params, $event);
    }

    /**
     * @Observe("claroline_external_authentication_menu_configure")
     *
     * @param \Claroline\CoreBundle\Menu\ConfigureMenuEvent $event
     * @return \Knp\Menu\ItemInterface $menu
     */
    public function onTopBarLeftMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();
        $menu->addChild(
            'LDAP',
            array('route' => 'claro_admin_ldap')
        )->setExtra('name', 'LDAP');

        return $menu;
    }

    private function redirect($params, $event)
    {
        $subRequest = $this->request->duplicate(array(), null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }
}
