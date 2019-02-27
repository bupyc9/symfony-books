<?php

namespace App\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author Afanasyev Pavel <bupyc9@gmail.com>
 */
class MenuBuilder
{
    /**
     * @var FactoryInterface
     */
    private $factory;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(FactoryInterface $factory, TranslatorInterface $translator)
    {
        $this->factory = $factory;
        $this->translator = $translator;
    }

    /**
     * @param array $options
     * @return ItemInterface
     * @throws \InvalidArgumentException
     */
    public function createMainMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttribute('class', 'navbar-nav');

        $menu->addChild($this->translator->trans('menu.authors'), ['route' => 'authors']);
        $menu->addChild($this->translator->trans('menu.books'), ['route' => 'books']);

        /** @var ItemInterface $child */
        foreach ($menu as $child) {
            $child
                ->setLinkAttribute('class', 'nav-link')
                ->setAttribute('class', 'nav-item');
        }

        return $menu;
    }
}