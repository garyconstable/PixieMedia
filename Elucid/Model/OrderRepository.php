<?php
/**
 * Elucid Integration
 * Copyright (C) 2020 PixieMedia
 * 
 * This file is part of PixieMedia/Elucid.
 * 
 * PixieMedia/Elucid is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace PixieMedia\Elucid\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Exception\CouldNotSaveException;
use PixieMedia\Elucid\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Store\Model\StoreManagerInterface;
use PixieMedia\Elucid\Api\Data\OrderInterfaceFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use PixieMedia\Elucid\Api\Data\OrderSearchResultsInterfaceFactory;
use Magento\Framework\Reflection\DataObjectProcessor;
use PixieMedia\Elucid\Api\OrderRepositoryInterface;
use PixieMedia\Elucid\Model\ResourceModel\Order as ResourceOrder;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;

/**
 * Class OrderRepository
 *
 * @package PixieMedia\Elucid\Model
 */
class OrderRepository implements OrderRepositoryInterface
{

    protected $searchResultsFactory;

    protected $dataObjectHelper;

    protected $dataObjectProcessor;

    protected $extensionAttributesJoinProcessor;

    protected $orderFactory;

    private $collectionProcessor;

    protected $resource;

    protected $dataOrderFactory;

    private $storeManager;

    protected $extensibleDataObjectConverter;
    protected $orderCollectionFactory;


    /**
     * @param ResourceOrder $resource
     * @param OrderFactory $orderFactory
     * @param OrderInterfaceFactory $dataOrderFactory
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param OrderSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceOrder $resource,
        OrderFactory $orderFactory,
        OrderInterfaceFactory $dataOrderFactory,
        OrderCollectionFactory $orderCollectionFactory,
        OrderSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->orderFactory = $orderFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataOrderFactory = $dataOrderFactory;
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
        \PixieMedia\Elucid\Api\Data\OrderInterface $order
    ) {
        /* if (empty($order->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $order->setStoreId($storeId);
        } */
        
        $orderData = $this->extensibleDataObjectConverter->toNestedArray(
            $order,
            [],
            \PixieMedia\Elucid\Api\Data\OrderInterface::class
        );
        
        $orderModel = $this->orderFactory->create()->setData($orderData);
        
        try {
            $this->resource->save($orderModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the order: %1',
                $exception->getMessage()
            ));
        }
        return $orderModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function get($orderId)
    {
        $order = $this->orderFactory->create();
        $this->resource->load($order, $orderId);
        if (!$order->getId()) {
            throw new NoSuchEntityException(__('order with id "%1" does not exist.', $orderId));
        }
        return $order->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->orderCollectionFactory->create();
        
        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \PixieMedia\Elucid\Api\Data\OrderInterface::class
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
        \PixieMedia\Elucid\Api\Data\OrderInterface $order
    ) {
        try {
            $orderModel = $this->orderFactory->create();
            $this->resource->load($orderModel, $order->getOrderId());
            $this->resource->delete($orderModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the order: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($orderId)
    {
        return $this->delete($this->get($orderId));
    }
}

