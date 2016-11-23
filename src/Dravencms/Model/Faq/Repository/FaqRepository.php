<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Faq\Repository;

use Dravencms\Model\Faq\Entities\Faq;
use Gedmo\Translatable\TranslatableListener;
use Kdyby\Doctrine\EntityManager;
use Nette;
use Salamek\Cms\CmsActionOption;
use Salamek\Cms\ICmsActionOption;
use Salamek\Cms\ICmsComponentRepository;
use Salamek\Cms\Models\ILocale;

/**
 * Class FaqRepository
 * @package App\Model\Faq\Repository
 */
class FaqRepository implements ICmsComponentRepository
{
    /** @var \Kdyby\Doctrine\EntityRepository */
    private $faqRepository;

    /** @var EntityManager */
    private $entityManager;

    /**
     * FaqRepository constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->faqRepository = $entityManager->getRepository(Faq::class);
    }

    /**
     * @param $id
     * @return mixed|null|Faq
     */
    public function getOneById($id)
    {
        return $this->faqRepository->find($id);
    }

    /**
     * @param $id
     * @return Faq[]
     */
    public function getById($id)
    {
        return $this->faqRepository->findBy(['id' => $id]);
    }

    /**
     * @return \Kdyby\Doctrine\QueryBuilder
     */
    public function getFaqQueryBuilder()
    {
        $qb = $this->faqRepository->createQueryBuilder('f')
            ->select('f');
        return $qb;
    }

    /**
     * @param $q
     * @param ILocale $locale
     * @param Faq|null $faqIgnore
     * @return boolean
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isQFree($q, ILocale $locale, Faq $faqIgnore = null)
    {
        $qb = $this->faqRepository->createQueryBuilder('f')
            ->select('f')
            ->where('f.q = :q')
            ->setParameters([
                'q' => $q
            ]);

        if ($faqIgnore)
        {
            $qb->andWhere('f != :faqIgnore')
                ->setParameter('faqIgnore', $faqIgnore);
        }

        $query = $qb->getQuery();
        $query->setHint(TranslatableListener::HINT_TRANSLATABLE_LOCALE, $locale->getLanguageCode());

        return (is_null($query->getOneOrNullResult()));
    }

    /**
     * @param bool $isActive
     * @return array
     */
    public function getByActive($isActive = true)
    {
        return $this->faqRepository->findBy(['isActive' => $isActive]);
    }

    /**
     * @param string $componentAction
     * @return ICmsActionOption[]
     */
    public function getActionOptions($componentAction)
    {
        return null;
    }

    /**
     * @param string $componentAction
     * @param array $parameters
     * @param ILocale $locale
     * @return null|CmsActionOption
     */
    public function getActionOption($componentAction, array $parameters, ILocale $locale)
    {
        return new CmsActionOption('FAQ');
    }
}