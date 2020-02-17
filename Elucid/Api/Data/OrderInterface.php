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

namespace PixieMedia\Elucid\Api\Data;

/**
 * Interface OrderInterface
 *
 * @package PixieMedia\Elucid\Api\Data
 */
interface OrderInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const ELUCID_ORDER_NUMBER = 'elucid_order_number';
    const ORDER_ID = 'order_id';
    const INTEGRATION_COUNT = 'integration_count';
    const NOTES = 'notes';
    const MAGENTO_ID = 'magento_id';
    const UPDATED_AT = 'updated_at';
    const ELUCID_LOCATION = 'elucid_location';
    const INTEGRATION_STATUS = 'integration_status';
    const CREATED_AT = 'created_at';
    const MAGENTO_STATUS = 'magento_status';

    /**
     * Get order_id
     * @return string|null
     */
    public function getOrderId();

    /**
     * Set order_id
     * @param string $orderId
     * @return \PixieMedia\Elucid\Api\Data\OrderInterface
     */
    public function setOrderId($orderId);

    /**
     * Get magento_id
     * @return string|null
     */
    public function getMagentoOrderId();

    /**
     * Set magento_id
     * @param string $magentoOrderId
     * @return \PixieMedia\Elucid\Api\Data\OrderInterface
     */
    public function setMagentoOrderId($magentoOrderId);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \PixieMedia\Elucid\Api\Data\OrderExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \PixieMedia\Elucid\Api\Data\OrderExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \PixieMedia\Elucid\Api\Data\OrderExtensionInterface $extensionAttributes
    );

    /**
     * Get magento_status
     * @return string|null
     */
    public function getMagentoStatus();

    /**
     * Set magento_status
     * @param string $magentoStatus
     * @return \PixieMedia\Elucid\Api\Data\OrderInterface
     */
    public function setMagentoStatus($magentoStatus);

    /**
     * Get elucid_order_number
     * @return string|null
     */
    public function getElucidOrderNumber();

    /**
     * Set elucid_order_number
     * @param string $elucidOrderNumber
     * @return \PixieMedia\Elucid\Api\Data\OrderInterface
     */
    public function setElucidOrderNumber($elucidOrderNumber);

    /**
     * Get integration_status
     * @return string|null
     */
    public function getIntegrationStatus();

    /**
     * Set integration_status
     * @param string $integrationStatus
     * @return \PixieMedia\Elucid\Api\Data\OrderInterface
     */
    public function setIntegrationStatus($integrationStatus);

    /**
     * Get notes
     * @return string|null
     */
    public function getNotes();

    /**
     * Set notes
     * @param string $notes
     * @return \PixieMedia\Elucid\Api\Data\OrderInterface
     */
    public function setNotes($notes);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return \PixieMedia\Elucid\Api\Data\OrderInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated_at
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set updated_at
     * @param string $updatedAt
     * @return \PixieMedia\Elucid\Api\Data\OrderInterface
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Get integration_count
     * @return string|null
     */
    public function getIntegrationCount();

    /**
     * Set integration_count
     * @param string $integrationCount
     * @return \PixieMedia\Elucid\Api\Data\OrderInterface
     */
    public function setIntegrationCount($integrationCount);

    /**
     * Get elucid_location
     * @return string|null
     */
    public function getElucidLocation();

    /**
     * Set elucid_location
     * @param string $elucidLocation
     * @return \PixieMedia\Elucid\Api\Data\OrderInterface
     */
    public function setElucidLocation($elucidLocation);
}

