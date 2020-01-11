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

namespace PixieMedia\ChuckNorris\Model\Data;

use PixieMedia\ChuckNorris\Api\Data\FactsInterface;

class Facts extends \Magento\Framework\Api\AbstractExtensibleObject implements FactsInterface
{

    /**
     * Get facts_id
     * @return string|null
     */
    public function getFactsId()
    {
        return $this->_get(self::FACTS_ID);
    }

    /**
     * Set facts_id
     * @param string $factsId
     * @return \PixieMedia\ChuckNorris\Api\Data\FactsInterface
     */
    public function setFactsId($factsId)
    {
        return $this->setData(self::FACTS_ID, $factsId);
    }

    /**
     * Get fact
     * @return string|null
     */
    public function getFact()
    {
        return $this->_get(self::FACT);
    }

    /**
     * Set fact
     * @param string $fact
     * @return \PixieMedia\ChuckNorris\Api\Data\FactsInterface
     */
    public function setFact($fact)
    {
        return $this->setData(self::FACT, $fact);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \PixieMedia\ChuckNorris\Api\Data\FactsExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \PixieMedia\ChuckNorris\Api\Data\FactsExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \PixieMedia\ChuckNorris\Api\Data\FactsExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get url
     * @return string|null
     */
    public function getUrl()
    {
        return $this->_get(self::URL);
    }

    /**
     * Set url
     * @param string $url
     * @return \PixieMedia\ChuckNorris\Api\Data\FactsInterface
     */
    public function setUrl($url)
    {
        return $this->setData(self::URL, $url);
    }

    /**
     * Get icon_url
     * @return string|null
     */
    public function getIconUrl()
    {
        return $this->_get(self::ICON_URL);
    }

    /**
     * Set icon_url
     * @param string $iconUrl
     * @return \PixieMedia\ChuckNorris\Api\Data\FactsInterface
     */
    public function setIconUrl($iconUrl)
    {
        return $this->setData(self::ICON_URL, $iconUrl);
    }

    /**
     * Get fact_id
     * @return string|null
     */
    public function getFactId()
    {
        return $this->_get(self::FACT_ID);
    }

    /**
     * Set fact_id
     * @param string $factId
     * @return \PixieMedia\ChuckNorris\Api\Data\FactsInterface
     */
    public function setFactId($factId)
    {
        return $this->setData(self::FACT_ID, $factId);
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
     * @return \PixieMedia\ChuckNorris\Api\Data\FactsInterface
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
     * @return \PixieMedia\ChuckNorris\Api\Data\FactsInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
