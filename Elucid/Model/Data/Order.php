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

namespace PixieMedia\Elucid\Model\Data;

use PixieMedia\Elucid\Api\Data\OrderInterface;

/**
 * Class Order
 *
 * @package PixieMedia\Elucid\Model\Data
 */
class Order extends \Magento\Framework\Api\AbstractExtensibleObject implements OrderInterface
{

    /**
     * Get order_id
     * @return string|null
     */
    public function getOrderId()
    {
        return $this->_get(self::ORDER_ID);
    }

    /**
     * Set order_id
     * @param string $orderId
     * @return \PixieMedia\Elucid\Api\Data\OrderInterface
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * Get magento_id
     * @return string|null
     */
    public function getMagentoOrderId()
    {
        return $this->_get(self::MAGENTO_ID);
    }

    /**
     * Set magento_id
     * @param string $magentoOrderId
     * @return \PixieMedia\Elucid\Api\Data\OrderInterface
     */
    public function setMagentoOrderId($magentoOrderId)
    {
        return $this->setData(self::MAGENTO_ID, $magentoOrderId);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \PixieMedia\Elucid\Api\Data\OrderExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \PixieMedia\Elucid\Api\Data\OrderExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \PixieMedia\Elucid\Api\Data\OrderExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get magento_status
     * @return string|null
     */
    public function getMagentoStatus()
    {
        return $this->_get(self::MAGENTO_STATUS);
    }

    /**
     * Set magento_status
     * @param string $magentoStatus
     * @return \PixieMedia\Elucid\Api\Data\OrderInterface
     */
    public function setMagentoStatus($magentoStatus)
    {
        return $this->setData(self::MAGENTO_STATUS, $magentoStatus);
    }

    /**
     * Get elucid_order_number
     * @return string|null
     */
    public function getElucidOrderNumber()
    {
        return $this->_get(self::ELUCID_ORDER_NUMBER);
    }

    /**
     * Set elucid_order_number
     * @param string $elucidOrderNumber
     * @return \PixieMedia\Elucid\Api\Data\OrderInterface
     */
    public function setElucidOrderNumber($elucidOrderNumber)
    {
        return $this->setData(self::ELUCID_ORDER_NUMBER, $elucidOrderNumber);
    }

    /**
     * Get integration_status
     * @return string|null
     */
    public function getIntegrationStatus()
    {
        return $this->_get(self::INTEGRATION_STATUS);
    }

    /**
     * Set integration_status
     * @param string $integrationStatus
     * @return \PixieMedia\Elucid\Api\Data\OrderInterface
     */
    public function setIntegrationStatus($integrationStatus)
    {
        return $this->setData(self::INTEGRATION_STATUS, $integrationStatus);
    }

    /**
     * Get notes
     * @return string|null
     */
    public function getNotes()
    {
        return $this->_get(self::NOTES);
    }

    /**
     * Set notes
     * @param string $notes
     * @return \PixieMedia\Elucid\Api\Data\OrderInterface
     */
    public function setNotes($notes)
    {
        return $this->setData(self::NOTES, $notes);
    }

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->_get(self::CREATED_AT);
    }

    /**
     * Set created_at
     * @param string $createdAt
     * @return \PixieMedia\Elucid\Api\Data\OrderInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get updated_at
     * @return string|null
     */
    public function getUpdatedAt()
    {
        return $this->_get(self::UPDATED_AT);
    }

    /**
     * Set updated_at
     * @param string $updatedAt
     * @return \PixieMedia\Elucid\Api\Data\OrderInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * Get integration_count
     * @return string|null
     */
    public function getIntegrationCount()
    {
        return $this->_get(self::INTEGRATION_COUNT);
    }

    /**
     * Set integration_count
     * @param string $integrationCount
     * @return \PixieMedia\Elucid\Api\Data\OrderInterface
     */
    public function setIntegrationCount($integrationCount)
    {
        return $this->setData(self::INTEGRATION_COUNT, $integrationCount);
    }

    /**
     * Get elucid_location
     * @return string|null
     */
    public function getElucidLocation()
    {
        return $this->_get(self::ELUCID_LOCATION);
    }

    /**
     * Set elucid_location
     * @param string $elucidLocation
     * @return \PixieMedia\Elucid\Api\Data\OrderInterface
     */
    public function setElucidLocation($elucidLocation)
    {
        return $this->setData(self::ELUCID_LOCATION, $elucidLocation);
    }
}

