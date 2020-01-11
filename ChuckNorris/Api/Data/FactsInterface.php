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

namespace PixieMedia\ChuckNorris\Api\Data;

interface FactsInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const ICON_URL = 'icon_url';
    const FACT = 'fact';
    const URL = 'url';
    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'created_at';
    const FACT_ID = 'fact_id';
    const FACTS_ID = 'facts_id';

    /**
     * Get facts_id
     * @return string|null
     */
    public function getFactsId();

    /**
     * Set facts_id
     * @param string $factsId
     * @return \PixieMedia\ChuckNorris\Api\Data\FactsInterface
     */
    public function setFactsId($factsId);

    /**
     * Get fact
     * @return string|null
     */
    public function getFact();

    /**
     * Set fact
     * @param string $fact
     * @return \PixieMedia\ChuckNorris\Api\Data\FactsInterface
     */
    public function setFact($fact);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \PixieMedia\ChuckNorris\Api\Data\FactsExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \PixieMedia\ChuckNorris\Api\Data\FactsExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \PixieMedia\ChuckNorris\Api\Data\FactsExtensionInterface $extensionAttributes
    );

    /**
     * Get url
     * @return string|null
     */
    public function getUrl();

    /**
     * Set url
     * @param string $url
     * @return \PixieMedia\ChuckNorris\Api\Data\FactsInterface
     */
    public function setUrl($url);

    /**
     * Get icon_url
     * @return string|null
     */
    public function getIconUrl();

    /**
     * Set icon_url
     * @param string $iconUrl
     * @return \PixieMedia\ChuckNorris\Api\Data\FactsInterface
     */
    public function setIconUrl($iconUrl);

    /**
     * Get fact_id
     * @return string|null
     */
    public function getFactId();

    /**
     * Set fact_id
     * @param string $factId
     * @return \PixieMedia\ChuckNorris\Api\Data\FactsInterface
     */
    public function setFactId($factId);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return \PixieMedia\ChuckNorris\Api\Data\FactsInterface
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
     * @return \PixieMedia\ChuckNorris\Api\Data\FactsInterface
     */
    public function setUpdatedAt($updatedAt);
}
