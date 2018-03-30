<?php

namespace IgraalOSB\StatsTableBundle\Configuration;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationAnnotation;

/**
 * @Annotation
 */
class StatsTableResult extends ConfigurationAnnotation
{
    private $_format;
    private $_options = [];

    public function __construct($options)
    {
        $this->_options = $options;
    }

    /**
     * @param string $format
     */
    public function setFormat($format)
    {
        $this->_format = $format;
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->_format;
    }

    /**
     * Returns the alias name for an annotated configuration.
     *
     * @return string
     */
    function getAliasName()
    {
        return 'statstable';
    }

    /**
     * Returns whether multiple annotations of this type are allowed
     *
     * @return Boolean
     */
    function allowArray()
    {
        return false;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }
}
