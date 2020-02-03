<?php
namespace Magento\CustomerStorefrontService\Model\Data;

class ValidationResults extends \Magento\Framework\Api\AbstractSimpleObject implements
    \Magento\CustomerStorefrontServiceApi\Api\Data\ValidationResultsInterface
{
    /**
     * {@inheritdoc}
     */
    public function isValid()
    {
        return $this->_get(self::VALID);
    }

    /**
     * {@inheritdoc}
     */
    public function getMessages()
    {
        return $this->_get(self::MESSAGES);
    }

    /**
     * Set if the provided data is valid.
     *
     * @param bool $isValid
     * @return $this
     */
    public function setIsValid($isValid)
    {
        return $this->setData(self::VALID, $isValid);
    }

    /**
     * Set error messages as array in case of validation failure.
     *
     * @param string[] $messages
     * @return $this
     */
    public function setMessages(array $messages)
    {
        return $this->setData(self::MESSAGES, $messages);
    }

}
