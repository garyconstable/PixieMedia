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

use PixieMedia\ChuckNorris\Api\FactsRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use PixieMedia\ChuckNorris\Model\ResourceModel\Facts as ResourceFacts;
use Magento\Framework\Exception\CouldNotDeleteException;
use PixieMedia\ChuckNorris\Api\Data\FactsSearchResultsInterfaceFactory;
use PixieMedia\ChuckNorris\Api\Data\FactsInterfaceFactory;
use Magento\Framework\Reflection\DataObjectProcessor;
use PixieMedia\ChuckNorris\Model\ResourceModel\Facts\CollectionFactory as FactsCollectionFactory;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Exception\NoSuchEntityException;

class FactsRepository implements FactsRepositoryInterface
{

    protected $dataObjectHelper;

    protected $dataFactsFactory;

    private $storeManager;

    protected $searchResultsFactory;

    protected $dataObjectProcessor;

    protected $extensionAttributesJoinProcessor;

    protected $factsFactory;

    private $collectionProcessor;

    protected $resource;

    protected $extensibleDataObjectConverter;
    protected $factsCollectionFactory;


    /**
     * @param ResourceFacts $resource
     * @param FactsFactory $factsFactory
     * @param FactsInterfaceFactory $dataFactsFactory
     * @param FactsCollectionFactory $factsCollectionFactory
     * @param FactsSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceFacts $resource,
        FactsFactory $factsFactory,
        FactsInterfaceFactory $dataFactsFactory,
        FactsCollectionFactory $factsCollectionFactory,
        FactsSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->factsFactory = $factsFactory;
        $this->factsCollectionFactory = $factsCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataFactsFactory = $dataFactsFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \PixieMedia\ChuckNorris\Api\Data\FactsInterface $facts
    ) {
        /* if (empty($facts->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $facts->setStoreId($storeId);
        } */
        
        $factsData = $this->extensibleDataObjectConverter->toNestedArray(
            $facts,
            [],
            \PixieMedia\ChuckNorris\Api\Data\FactsInterface::class
        );
        
        $factsModel = $this->factsFactory->create()->setData($factsData);
        
        try {
            $this->resource->save($factsModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the facts: %1',
                $exception->getMessage()
            ));
        }
        return $factsModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function get($factsId)
    {
        $facts = $this->factsFactory->create();
        $this->resource->load($facts, $factsId);
        if (!$facts->getId()) {
            throw new NoSuchEntityException(__('Facts with id "%1" does not exist.', $factsId));
        }
        return $facts->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->factsCollectionFactory->create();
        
        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \PixieMedia\ChuckNorris\Api\Data\FactsInterface::class
        );
        
        $this->collectionProcessor->process($criteria, $collection);
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        
        $items = [];
        foreach ($collection as $model) {
            $items[] = $model->getDataModel();
        }
        
        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \PixieMedia\ChuckNorris\Api\Data\FactsInterface $facts
    ) {
        try {
            $factsModel = $this->factsFactory->create();
            $this->resource->load($factsModel, $facts->getFactsId());
            $this->resource->delete($factsModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Facts: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($factsId)
    {
        return $this->delete($this->get($factsId));
    }
}
