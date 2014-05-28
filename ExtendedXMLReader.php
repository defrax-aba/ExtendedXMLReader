<?php

/**
 * The MIT License (MIT)
 * 
 * Copyright (c) 2014 Bhavik Patel
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
use XMLReader;

/**
 * This source released as open source under MIT license.
 * 
 * 
 * @author      Bhavik Patel    <bhavik.patel@pralaygroup.com> 
 * @copyright   (c) 2014,       Bhavik Patel
 * @filesource
 */
class ExtendedXMLReader
{

    /**
     * XMLReader object
     * 
     * @var object 
     */
    private $xml;

    /**
     * XMLReader properties which can be accessed
     * 
     * @var array 
     */
    private $xml_property = array(
        'attributeCount', 'baseURI', 'depth', 'hasAttributes', 'hasValue', 'isDefault',
        'isEmptyElement', 'localName', 'name', 'namespaceURI', 'nodeType', 'prefix',
        'value', 'xmlLang'
    );

    /**
     * XML elements
     * 
     * @var array 
     */
    private $_elements;

    /**
     * File Path
     * 
     * @var string 
     */
    private $file;

    /**
     * 
     * @param type $file
     */
    public function __construct($file)
    {
        $this->file = $file;
        $this->xml = new XMLReader();
        $this->_open($this->file);
        $this->_elements = $this->process();
        $this->rewind();
    }

    /**
     * Open file
     * 
     * @param string $file
     */
    private function _open($file)
    {
        $this->xml->open($file);
    }

    /**
     * Returns attributes of processing node
     * 
     * @return type
     */
    private function getAttributes()
    {
        $attrib = array();
        while ($this->xml->moveToNextAttribute())
        {
            $attrib[$this->xml->name] = $this->xml->value;
        }

        return empty($attrib) ? FALSE : $attrib;
    }

    /**
     * Process XML file to parse
     * 
     * @return array
     */
    private function process()
    {
        $tree = array();

        while ($this->xml->read())
        {
            switch ($this->xml->nodeType)
            {
                case XMLReader::END_ELEMENT:
                    return $tree;
                case XMLReader::ELEMENT:

                    $node = array();
                    $node['tag'] = $node_name = $this->xml->name;
                    $node['attributes'] = $this->getAttributes();

                    if (!$this->xml->isEmptyElement)
                    {
                        $childs = $this->process();
                        $node['type'] = is_array($childs) ? 'element' : 'text';
                        $node['value'] = $childs;
                    }

                    if (array_key_exists($node_name, $tree))
                    {
                        if (!array_key_exists(0, $tree[$node_name]))
                        {
                            $temp = $tree[$node_name];
                            unset($tree[$node_name]);
                            $tree[$node_name][] = $temp;
                        }

                        $tree[$node_name][] = $node;
                    }
                    else
                    {
                        $tree[$node_name] = $node;
                    }

                case XMLReader::TEXT:
                    if (trim($this->xml->value))
                    {
                        $tree = trim($this->xml->value);
                    }

                default:
                    break;
            }
        }
        return $tree;
    }

    /**
     * Pointing to first element
     */
    public function rewind()
    {
        $this->xml->close();
        $this->_open($this->file);
    }

    /**
     * Returns XML elements as object
     * 
     * @return object
     */
    public function toObject()
    {
        return json_decode(json_encode($this->_elements));
    }

    /**
     * Returns XML elements as array
     * 
     * @return array
     */
    public function toArray()
    {
        return $this->_elements;
    }

    /**
     * Call method defined in XMLReader class
     * 
     * @param   string  $name
     * @param   array   $arguments
     * @return  mixed
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this->xml, $name))
        {
            return call_user_func_array(array($this->xml, $name), $arguments);
        }

        trigger_error($name . ' method does not exist in class ' . __CLASS__);
    }

    /**
     *  Call variable defined in XMLReader class
     * 
     * @param   string  $name
     * @return  string
     */
    public function __get($name)
    {
        if (in_array($name, $this->xml_property))
        {
            return $this->xml->{$name};
        }

        trigger_error($name . ' does not exist in class ' . __CLASS__);
    }

}
