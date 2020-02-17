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
 * Interface OrderSearchResultsInterface
 *
 * @package PixieMedia\Elucid\Api\Data
 */
interface OrderSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get order list.
     * @return \PixieMedia\Elucid\Api\Data\OrderInterface[]
     */
    public function getItems();

    /**
     * Set magento_id list.
     * @param \PixieMedia\Elucid\Api\Data\OrderInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

