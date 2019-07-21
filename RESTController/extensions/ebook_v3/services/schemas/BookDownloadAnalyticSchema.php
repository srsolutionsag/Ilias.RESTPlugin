<?php
$schema = <<<'JSON'
{
	"definitions": {},
	"$schema": "http://json-schema.org/draft-07/schema#",
	"$id": "http://example.com/root.json",
	"type": "object",
	"title": "The Root Schema",
	"required": [
	  "book_ref_id",
	  "hardware_id"
	],
	"properties": {
	  "book_ref_id": {
	    "$id": "#/properties/book_ref_id",
	    "type": "integer",
	    "title": "The Book_ref_id Schema",
	    "default": 0,
	    "examples": [
	      287
	    ],
	    "exclusiveMinimum": 0.0
	  },
	  "hardware_id": {
	    "$id": "#/properties/hardware_id",
	    "type": "string",
	    "title": "The Hardware_id Schema",
	    "default": "",
	    "examples": [
	      "asdfadsfadfda"
	    ],
	    "pattern": "^(.+)$"
	  }
	}
}
JSON;
