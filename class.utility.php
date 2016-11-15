<?php

abstract class Printable implements JsonSerializable
{
    /**
     * @return array[String=>mixed]
     */
    public abstract function getData();

    /**
     * @return string
     */
    public abstract function getClassType();

    /* Functional stuff */
    /**
     * General __toString() override
     * @return string
     */
    public function __toString()
    {
        return $this->getClassType() . ':' . json_encode($this->getData());
    }

    /**
     * General method to serialize to json
     * @return array
     */
    public function jsonSerialize()
    {
        return array("type" => $this->getClassType(), "data" => $this->getData());
    }

}

?>