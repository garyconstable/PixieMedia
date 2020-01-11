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

namespace PixieMedia\ChuckNorris\Cron;

use DateTime;
use Exception;

class ChuckNorrisFacts
{
    /**
     * Facts endpoint url.
     *
     * @var string
     */
    private $endPoint = 'https://api.chucknorris.io/jokes/search?query=beard';

    /**
     * Logger.
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Object manager.
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Constructor
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->logger = $logger;
        $this->_objectManager = $objectManager;
    }

    /**
     * Execute the cron
     *
     * @return void
     * @throws Exception
     */
    public function execute()
    {
        $facts = $this->getFacts();

        if (!empty($facts)) {
            foreach ($facts as $fact) {
                $this->addFact($fact);
            }
            $this->logger->addInfo("Cron job: ChucK Norris Facts is executed.");
        }
    }

    /**
     * Get facts from endpoint.
     *
     * @return array
     */
    private function getFacts()
    {
        try {
            $json = json_decode(file_get_contents($this->endPoint), true);
            return isset($json['result']) ? $json['result'] : [];
        } catch (Exception $ex) {
            return [];
        }
    }

    /**
     * Add or update a fact.
     *
     * @param array $fact
     * @throws Exception
     */
    private function addFact($fact = [])
    {
        $model = $this->_objectManager->create(\PixieMedia\ChuckNorris\Model\Facts::class)
            ->load($fact['id'], 'fact_id');

        foreach($fact as $key => $value){
            switch($key){
                case 'value':
                    $arrKey = 'fact';
                    break;
                case 'id':
                    $arrKey = 'fact_id';
                    break;
                default:
                    $arrKey = $key;
                    break;
            }

            switch($value){
                case 'created_at':
                case 'updated_at':
                    $arrVal = new DateTime($value);
                    break;
                default:
                    $arrVal = $value;
                    break;
            }

            $model->setData($arrKey, $arrVal);
        }

        $model->save();
    }
}
