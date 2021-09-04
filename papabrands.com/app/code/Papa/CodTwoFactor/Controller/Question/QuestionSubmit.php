<?php

namespace Papa\CodTwoFactor\Controller\Question;

class QuestionSubmit extends \Magezon\FAQ\Controller\Question\QuestionSubmit
{
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $collection = $this->questionCollection->create();
        $resultJson = $this->resultJsonFactory->create();

        if ($data) {
            try {
                $model = $this->_objectManager->create(\Magezon\FAQ\Model\Question::class);
                $array = ["\\", "\"", ".", ",", "/", "--", "'", "[", "]", "{", "}", "~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "+", "=", "|", "<", ">", "?", ";", ":"];
                $identifiers = [];
                $identifier = str_replace(" ", "-", strtolower($data['question']));
                $identifier = str_replace($array, "", $identifier);

                foreach ($collection as $key => $question) {
                    $identifiers[] = $question->getIdentifier();
                }
                
                if (in_array($identifier, $identifiers)) {
                    $identifier = $this->checkUnique($identifiers, $identifier, 1);                
                }

                $model->setTitle($data['question'])->setAuthorName($data['name'])->setAuthorEmail($data['email'])->setCustomerOrderid($data['order'])->setIdentifier($identifier)->setIsActive(0);
                if (isset($data['category'])) {
                    $model['category_ids'] = $data['category'];      
                }
                $model['store_id'] = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
                $groups = $this->customerGroup->getList($this->searchCriteriaBuilder->create());
                $customerGroups = [];
                foreach ($groups->getItems() as $group) {
                    $customerGroups[] = $group->getId();
                }
                $model['customer_group_id'] = $customerGroups;
                $model->save();

                $email = $this->emailFactory->create();
                $email->sendNewSubmit($model, $data['name'], $data['email']);
                $text = ['success' => $this->dataHelper->getSuccessMessage()];
            } catch(\Exception $e) {
                $text = ['error' => $this->dataHelper->getErrorMessage()];
            }
        }

        return $resultJson->setData($text);
    }
}
