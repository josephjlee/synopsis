<?php

namespace TheIconic\Synopsis;

use TheIconic\Synopsis\Exception\TraceSynopsis;

/**
 * represents and exception
 *
 * @package TheIconic\Synopsis
 */
class ExceptionSynopsis extends ObjectSynopsis
{

    /**
     * @var string the file
     */
    protected $file;

    /**
     * @var int the line
     */
    protected $line;

    /**
     * @see parent::process()
     * @param $value
     * @param $depth
     */
    public function process($value, $depth)
    {
        parent::process($value, $depth);

        $this->length = count($value->getTrace());

        $this->value = $value->getMessage();

        $this->line = $value->getLine();

        $this->file = $value->getFile();

        // we omit the $depth check here on purpose
        foreach ($value->getTrace() as $k => $trace) {
            $this->addChild(new TraceSynopsis($trace, $depth), '#' . $k);
        }
    }
}
