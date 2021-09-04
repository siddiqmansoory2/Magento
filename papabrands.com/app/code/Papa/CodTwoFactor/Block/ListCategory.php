<?php
namespace Papa\CodTwoFactor\Block;

class ListCategory extends \Magezon\FAQ\Block\ListCategory
{
    public function getNews() {
        //get values of current page
       $page=($this->getRequest()->getParam('p'))? $this->getRequest()->getParam('p') : 1;
        //get values of current limit
        $question = $this->dataHelper->getConfig('tag_page/questions_per_page');
       $pageSize=($this->getRequest()->getParam('limit'))? $this->getRequest()->getParam('limit') : $question;
       $newsCollection = $this->questionCollectionFactory->create();
       $newsCollection->addFieldToFilter('is_active', \Magezon\FAQ\Model\Category::STATUS_ENABLED)->setPageSize($pageSize);
       $newsCollection->setCurPage($page);
       return $newsCollection;
    }

    protected function _prepareLayout() {

        $this->_addBreadcrumbs();
        parent::_prepareLayout();
        
        $title = $this->dataHelper->getFaqTitle();
        $metaTitle = $this->dataHelper->getConfig('latest_page/meta_title');
        $metaKeywords = $this->dataHelper->getConfig('latest_page/meta_keywords');
        $metaDescription = $this->dataHelper->getConfig('latest_page/meta_description');
        $this->pageConfig->getTitle()->set($metaTitle ? $metaTitle : $title);
        $this->pageConfig->setKeywords($metaKeywords);
        $this->pageConfig->setDescription($metaDescription);

        if ($this->getNews()) {
            $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'faq.news')->setAvailableLimit(array(10=>10,15=>15))->setShowPerPage(true)->setCollection($this->getNews());
            $this->setChild('pager', $pager);
            $this->getNews()->load();
        }
        return $this;
    }

    public function getPagerHtml() {
        return $this->getChildHtml('pager');
    }
}