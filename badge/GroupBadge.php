<?php
class GroupBadge
{
    private $_code;
    private $_parts;

    public function __construct($code)
    {
        $this->_code = $code;
        $this->_parts = [];
    }

    public function code(): string
    {
        return $this->_code;
    }

    public function parts()
    {
        return $this->_parts;
    }

    public function appendPart($part)
    {
        $this->_parts[] = $part;
    }
}
