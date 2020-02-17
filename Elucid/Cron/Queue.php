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

namespace PixieMedia\Elucid\Cron;

use PixieMedia\Elucid\Helper\Elucid;
use Psr\Log\LoggerInterface;

/**
 * Class Queue
 *
 * @package PixieMedia\Elucid\Cron
 */
class Queue
{
    protected $logger;

    protected $eHelper;

    /**
     * Constructor
     *
     * @param LoggerInterface $logger
     * @param Elucid $eHelper
     */
    public function __construct(
        LoggerInterface $logger,
        Elucid $eHelper
    ) {
        $this->eHelper = $eHelper;
        $this->logger = $logger;
    }

    /**
     * Execute the cron
     *
     * @return void
     */
    public function execute()
    {
        $this->logger->addInfo("Cronjob Elucid Queue is started.");

        $this->eHelper->logThis("Started queue process");
        $processQueue = $this->eHelper->processQueue();
        if ($processQueue) {
            $this->eHelper->logThis("Queue successful");
        } else {
            $this->eHelper->logThis("Queue problem");
        }

        $this->logger->addInfo("Cronjob Elucid Queue is executed.");
    }
}
