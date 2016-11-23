<?php

namespace Dravencms\FrontModule\Components\Faq\Faq\Overview;

use Dravencms\Components\BaseControl;
use Dravencms\Model\Faq\Repository\FaqRepository;
use Salamek\Cms\ICmsActionOption;

/**
 * Base presenter for all application presenters.
 */
class Overview extends BaseControl
{
    /** @var ICmsActionOption */
    private $cmsActionOption;

    /** @var FaqRepository */
    private $faqRepository;

    public function __construct(ICmsActionOption $cmsActionOption, FaqRepository $faqRepository)
    {
        parent::__construct();
        $this->cmsActionOption = $cmsActionOption;
        $this->faqRepository = $faqRepository;
    }


    public function render()
    {
        $template = $this->template;
        $template->overview = $this->faqRepository->getByActive();

        $template->setFile(__DIR__.'/overview.latte');
        $template->render();
    }
}
