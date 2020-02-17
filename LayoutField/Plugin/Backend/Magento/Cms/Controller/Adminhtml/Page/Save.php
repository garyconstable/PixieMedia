<?php

/**
 * Copyright 2020 Gary Constable
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace PixieMedia\LayoutField\Plugin\Backend\Magento\Cms\Controller\Adminhtml\Page;

/**
 * Class Save
 *
 * @package PixieMedia\LayoutField\Plugin\Backend\Magento\Cms\Controller\Adminhtml\Page
 */
class Save
{
    private $logger;

    /**
     * Save constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct
    (
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->logger = $logger;
        $this->_objectManager = $objectManager;
    }

    /**
     * After Execution - Update Model with posts data.
     *
     * @param \Magento\Cms\Controller\Adminhtml\Page\Save $subject
     * @param $result
     * @return mixed
     */
    public function afterExecute(
        \Magento\Cms\Controller\Adminhtml\Page\Save $subject,
        $result
    ) {
        if(isset($_POST['layout_update_xml']) && $_POST['page_id']) {
            try{
                $repo = $this->_objectManager->get(\Magento\Cms\Api\PageRepositoryInterface::class);
                $model = $repo->getById($_POST['page_id']);
                $model->setData('layout_update_xml', $_POST['layout_update_xml']);
                $model->save();
            } catch (\Exception $ex) {
                $this->logger->critical('--> PixieMedia Layout Field - No Save', [
                    'page_id' => $_POST['page_id'],
                    'layout_update_xml' => $_POST['layout_update_xml']
                ]);
            }
        }
        return $result;
    }
}

