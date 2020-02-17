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

namespace PixieMedia\Elucid\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface OrderRepositoryInterface
 *
 * @package PixieMedia\Elucid\Api
 */
interface OrderRepositoryInterface
{

    /**
     * Save order
     * @param \PixieMedia\Elucid\Api\Data\OrderInterface $order
     * @return \PixieMedia\Elucid\Api\Data\OrderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \PixieMedia\Elucid\Api\Data\OrderInterface $order
    );

    /**
     * Retrieve order
     * @param string $orderId
     * @return \PixieMedia\Elucid\Api\Data\OrderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($orderId);

    /**
     * Retrieve order matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \PixieMedia\Elucid\Api\Data\OrderSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete order
     * @param \PixieMedia\Elucid\Api\Data\OrderInterface $order
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \PixieMedia\Elucid\Api\Data\OrderInterface $order
    );

    /**
     * Delete order by ID
     * @param string $orderId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($orderId);
}

