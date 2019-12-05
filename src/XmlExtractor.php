<?php

namespace CFGit;

class XmlExtractor
{
    protected $branch = [];
    protected $dataTree = [];
    protected $link = null;

    public function parse($file)
    {
//        $stream = fopen(__DIR__."/test.xml", 'r');
        $stream = fopen($file, 'r');
        $parser = xml_parser_create();

        xml_set_object($parser, $this);
        xml_set_character_data_handler($parser, 'cdata');
        xml_set_element_handler($parser, 'eOpen', 'eClose');

        $this->branch = [];
        $this->dataTree = ["tagName" => "XML"];
        $this->link = &$this->dataTree;
        while (($data = fread($stream, 16384))) {
            xml_parse($parser, $data);
        }
        xml_parse($parser, '', true);
        xml_parser_free($parser);
        fclose($stream);
        return $this->dataTree;
    }

    protected function cdata($parser, $value)
    {
        $value = trim($value);
        if ($value) {
            $this->link["value"] = ($this->link["value"] ?? "").$value;
        }
    }

    protected function eOpen($parser, $eName, $eAttrs)
    {
        $newNode = ["parent" => &$this->link, "tagName" => strtoupper($eName)];
        if ($eAttrs) {
            $newNode['attributes'] = $eAttrs;
        }
        $this->link["nodes"] = $this->link["nodes"] ?? [];
        $this->link["nodes"][] = $newNode;
        $this->link = &$this->link["nodes"][count($this->link["nodes"]) - 1];
        array_push($this->branch, $eName);
    }

    protected function eClose($parser, $eName)
    {
        $parent = &$this->link['parent'];
        unset($this->link['parent']);
        $this->link = &$parent;
        array_pop($this->branch);
    }
}
