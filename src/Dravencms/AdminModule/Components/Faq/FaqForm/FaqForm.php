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

namespace Dravencms\AdminModule\Components\Faq;

use Dravencms\Components\BaseFormFactory;
use App\Model\Faq\Entities\Faq;
use App\Model\Faq\Repository\FaqRepository;
use App\Model\Locale\Repository\LocaleRepository;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

/**
 * Description of FaqForm
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class FaqForm extends Control
{
    /** @var BaseFormFactory */
    private $baseFormFactory;

    /** @var EntityManager */
    private $entityManager;

    /** @var FaqRepository */
    private $faqRepository;

    /** @var LocaleRepository */
    private $localeRepository;

    /** @var Faq|null */
    private $faq = null;

    /** @var array */
    public $onSuccess = [];

    /**
     * FaqForm constructor.
     * @param BaseFormFactory $baseFormFactory
     * @param EntityManager $entityManager
     * @param FaqRepository $faqRepository
     * @param LocaleRepository $localeRepository
     * @param Faq|null $faq
     */
    public function __construct(
        BaseFormFactory $baseFormFactory,
        EntityManager $entityManager,
        FaqRepository $faqRepository,
        LocaleRepository $localeRepository,
        Faq $faq = null
    ) {
        parent::__construct();

        $this->faq = $faq;

        $this->baseFormFactory = $baseFormFactory;
        $this->entityManager = $entityManager;
        $this->faqRepository = $faqRepository;
        $this->localeRepository = $localeRepository;


        if ($this->faq) {
            $defaults = [
                'q' => $this->faq->getQ(),
                'a' => $this->faq->getA(),
                'position' => $this->faq->getPosition(),
                'isActive' => $this->faq->isActive(),
            ];


            $repository = $this->entityManager->getRepository('Gedmo\Translatable\Entity\Translation');
            $defaults += $repository->findTranslations($this->faq);

            $defaultLocale = $this->localeRepository->getDefault();
            if ($defaultLocale) {
                $defaults[$defaultLocale->getLanguageCode()]['q'] = $this->faq->getQ();
                $defaults[$defaultLocale->getLanguageCode()]['a'] = $this->faq->getA();
            }
        }
        else{
            $defaults = [
                'isActive' => true
            ];
        }

        $this['form']->setDefaults($defaults);
    }

    /**
     * @return \Dravencms\Components\BaseForm
     */
    protected function createComponentForm()
    {
        $form = $this->baseFormFactory->create();

        foreach ($this->localeRepository->getActive() as $activeLocale) {
            $container = $form->addContainer($activeLocale->getLanguageCode());
            $container->addTextArea('q')
                ->setRequired(true)
                ->addRule(Form::MAX_LENGTH, 'Question is too long.', 6000);

            $container->addTextArea('a')
                ->setRequired(true)
                ->addRule(Form::MAX_LENGTH, 'Answer is too long.', 6000);
        }
        
        $form->addText('position')
            ->setDisabled(is_null($this->faq));

        $form->addCheckbox('isActive');

        $form->addSubmit('send');

        $form->onValidate[] = [$this, 'editFormValidate'];
        $form->onSuccess[] = [$this, 'editFormSucceeded'];

        return $form;
    }

    /**
     * @param Form $form
     */
    public function editFormValidate(Form $form)
    {
        $values = $form->getValues();

        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            if (!$this->faqRepository->isQFree($values->{$activeLocale->getLanguageCode()}->q, $activeLocale, $this->faq)) {
                $form->addError('Tento q je již zabrán.');
            }
        }

        if (!$this->presenter->isAllowed('faq', 'edit')) {
            $form->addError('Nemáte oprávění editovat faq.');
        }
    }

    /**
     * @param Form $form
     * @throws \Exception
     */
    public function editFormSucceeded(Form $form)
    {
        $values = $form->getValues();
        
        if ($this->faq) {
            $faq = $this->faq;
            /*$faq->setA($values->a);
            $faq->setQ($values->q);*/
            $faq->setPosition($values->position);
            $faq->setIsActive($values->isActive);
        } else {
            $defaultLocale = $this->localeRepository->getDefault();
            $faq = new Faq($values->{$defaultLocale->getLanguageCode()}->q, $values->{$defaultLocale->getLanguageCode()}->a, $values->isActive);
        }

        $repository = $this->entityManager->getRepository('Gedmo\\Translatable\\Entity\\Translation');

        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            $repository->translate($faq, 'a', $activeLocale->getLanguageCode(), $values->{$activeLocale->getLanguageCode()}->a)
                ->translate($faq, 'q', $activeLocale->getLanguageCode(), $values->{$activeLocale->getLanguageCode()}->q);
        }

        $this->entityManager->persist($faq);

        $this->entityManager->flush();

        $this->onSuccess();
    }


    public function render()
    {
        $template = $this->template;
        $template->activeLocales = $this->localeRepository->getActive();
        $template->setFile(__DIR__ . '/FaqForm.latte');
        $template->render();
    }
}