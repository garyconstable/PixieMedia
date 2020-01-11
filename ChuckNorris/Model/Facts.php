<?php
/**
 * Copyright (c) 2019 2020 
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace PixieMedia\ChuckNorris\Model;

use PixieMedia\ChuckNorris\Api\Data\FactsInterfaceFactory;
use PixieMedia\ChuckNorris\Api\Data\FactsInterface;
use Magento\Framework\Api\DataObjectHelper;

class Facts extends \Magento\Framework\Model\AbstractModel
{

    protected $dataObjectHelper;

    protected $_eventPrefix = 'pixiemedia_chucknorris_facts';
    protected $factsDataFactory;


    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param FactsInterfaceFactory $factsDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \PixieMedia\ChuckNorris\Model\ResourceModel\Facts $resource
     * @param \PixieMedia\ChuckNorris\Model\ResourceModel\Facts\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        FactsInterfaceFactory $factsDataFactory,
        DataObjectHelper $dataObjectHelper,
        \PixieMedia\ChuckNorris\Model\ResourceModel\Facts $resource,
        \PixieMedia\ChuckNorris\Model\ResourceModel\Facts\Collection $resourceCollection,
        array $data = []
    ) {
        $this->factsDataFactory = $factsDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve facts model with facts data
     * @return FactsInterface
     */
    public function getDataModel()
    {
        $factsData = $this->getData();
        
        $factsDataObject = $this->factsDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $factsDataObject,
            $factsData,
            FactsInterface::class
        );
        
        return $factsDataObject;
    }
}
