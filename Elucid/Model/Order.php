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

use Magento\Framework\Api\DataObjectHelper;
use PixieMedia\Elucid\Api\Data\OrderInterfaceFactory;
use PixieMedia\Elucid\Api\Data\OrderInterface;

/**
 * Class Order
 *
 * @package PixieMedia\Elucid\Model
 */
class Order extends \Magento\Framework\Model\AbstractModel
{

    protected $_eventPrefix = 'pixiemedia_elucid_order';
    protected $orderDataFactory;

    protected $dataObjectHelper;


    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param OrderInterfaceFactory $orderDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \PixieMedia\Elucid\Model\ResourceModel\Order $resource
     * @param \PixieMedia\Elucid\Model\ResourceModel\Order\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        OrderInterfaceFactory $orderDataFactory,
        DataObjectHelper $dataObjectHelper,
        \PixieMedia\Elucid\Model\ResourceModel\Order $resource,
        \PixieMedia\Elucid\Model\ResourceModel\Order\Collection $resourceCollection,
        array $data = []
    ) {
        $this->orderDataFactory = $orderDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve order model with order data
     * @return OrderInterface
     */
    public function getDataModel()
    {
        $orderData = $this->getData();
        
        $orderDataObject = $this->orderDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $orderDataObject,
            $orderData,
            OrderInterface::class
        );
        
        return $orderDataObject;
    }
}

