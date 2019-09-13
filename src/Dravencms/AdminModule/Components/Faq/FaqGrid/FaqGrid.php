<?php

/*
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301  USA
 */

namespace Dravencms\AdminModule\Components\Faq\FaqGrid;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Components\BaseGrid\BaseGridFactory;
use Dravencms\Locale\CurrentLocaleResolver;
use Dravencms\Model\Faq\Repository\FaqRepository;
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Kdyby\Doctrine\EntityManager;

/**
 * Description of MapGrid
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class FaqGrid extends BaseControl
{

    /** @var BaseGridFactory */
    private $baseGridFactory;

    /** @var FaqRepository */
    private $faqRepository;

    /** @var EntityManager */
    private $entityManager;

    /** @var LocaleRepository */
    private $localeRepository;

    /** @var \Dravencms\Model\Locale\Entities\Locale|null */
    private $currentLocale;

    /**
     * @var array
     */
    public $onDelete = [];

    /**
     * FaqGrid constructor.
     * @param FaqRepository $faqRepository
     * @param BaseGridFactory $baseGridFactory
     * @param EntityManager $entityManager
     * @param LocaleRepository $localeRepository
     */
    public function __construct(
        FaqRepository $faqRepository,
        BaseGridFactory $baseGridFactory,
        EntityManager $entityManager,
        LocaleRepository $localeRepository,
        CurrentLocaleResolver $currentLocaleResolver
    )
    {
        parent::__construct();

        $this->baseGridFactory = $baseGridFactory;
        $this->faqRepository = $faqRepository;
        $this->entityManager = $entityManager;
        $this->localeRepository = $localeRepository;
        $this->currentLocale = $currentLocaleResolver->getCurrentLocale();
    }


    /**
     * @param $name
     * @return \Dravencms\Components\BaseGrid
     */
    public function createComponentGrid($name)
    {
        $grid = $this->baseGridFactory->create($this, $name);

        $grid->setDataSource($this->faqRepository->getFaqQueryBuilder());

        $grid->addColumnText('q', 'Q')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnDateTime('updatedAt', 'Last edit')
            ->setFormat($this->currentLocale->getDateTimeFormat())
            ->setAlign('center')
            ->setSortable()
            ->setFilterDate();

        $grid->addColumnBoolean('isActive', 'Active');


        if ($this->presenter->isAllowed('faq', 'edit')) {

            $grid->addAction('edit', 'Upravit')
                ->setIcon('pencil')
                ->setTitle('Upravit')
                ->setClass('btn btn-xs btn-primary');
        }

        if ($this->presenter->isAllowed('faq', 'delete')) {
            $grid->addAction('delete', '', 'delete!')
                ->setIcon('trash')
                ->setTitle('Smazat')
                ->setClass('btn btn-xs btn-danger ajax')
                ->setConfirm('Do you really want to delete row %s?', 'identifier');
            $grid->addGroupAction('Smazat')->onSelect[] = [$this, 'handleDelete'];
        }
        $grid->addExportCsvFiltered('Csv export (filtered)', 'articles_filtered.csv')
            ->setTitle('Csv export (filtered)');
        $grid->addExportCsv('Csv export', 'articlesall.csv')
            ->setTitle('Csv export');

        return $grid;
    }

    /**
     * @param $id
     * @throws \Exception
     */
    public function handleDelete($id)
    {
        $faqs = $this->faqRepository->getById($id);
        foreach ($faqs AS $faq)
        {
            $this->entityManager->remove($faq);
        }

        $this->entityManager->flush();

        $this->onDelete();
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/FaqGrid.latte');
        $template->render();
    }
}
