<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dravencms\AdminModule\FaqModule;


use Dravencms\AdminModule\Components\Faq\FaqFormFactory;
use Dravencms\AdminModule\Components\Faq\FaqGridFactory;
use Dravencms\AdminModule\SecuredPresenter;
use App\Model\Faq\Entities\Faq;
use App\Model\Faq\Repository\FaqRepository;

/**
 * Description of FaqPresenter
 *
 * @author Adam Schubert
 */
class FaqPresenter extends SecuredPresenter
{
    /** @var FaqRepository @inject */
    public $faqRepository;

    /** @var FaqFormFactory @inject */
    public $faqFormFactory;

    /** @var FaqGridFactory @inject */
    public $faqGridFactory;

    /** @var null|Faq  */
    private $faq = null;

    /**
     * @isAllowed(faq,edit)
     */
    public function renderDefault()
    {
        $this->template->h1 = 'FAQ';
    }

    /**
     * @isAllowed(faq,edit)
     * @param integer $id
     */
    public function actionEdit($id)
    {
        if ($id) {
            $faq = $this->faqRepository->getOneById($id);

            if (!$faq) {
                $this->error();
            }

            $this->faq = $faq;
            $this->template->h1 = sprintf('Edit faq „%s“', $faq->getQ());
        } else {
            $this->template->h1 = 'New faq';
        }
    }

    /**
     * @return \AdminModule\Components\Faq\FaqForm
     */
    protected function createComponentFormFaq()
    {
        $control = $this->faqFormFactory->create($this->faq);
        $control->onSuccess[] = function()
        {
            $this->flashMessage('Faq has been successfully saved', 'alert-success');
            $this->redirect('Faq:');
        };
        return $control;
    }

    /**
     * @return \AdminModule\Components\Faq\FaqGrid
     */
    public function createComponentGridFaq()
    {
        $control = $this->faqGridFactory->create();
        $control->onDelete[] = function()
        {
            $this->flashMessage('Faq has been successfully deleted', 'alert-success');
            $this->redirect('Faq:');
        };
        return $control;
    }
}
