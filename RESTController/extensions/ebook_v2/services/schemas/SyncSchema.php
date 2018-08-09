
<?php
$schema = <<<'JSON'
{
  "$id": "http://example.com/example.json",
  "type": "object",
  "definitions": {},
  "$schema": "http://json-schema.org/draft-07/schema#",
  "required": [
	"sync",
	"notes",
	"bookmarks",
	"drawings",
	"highlights"
  ],
  "properties": {
    "lastSyncTime": {
      "$id": "/properties/lastSyncTime",
      "type": "integer",
      "title": "The Lastsynctime Schema ",
      "default": 0,
      "examples": [
        112222644
      ]
    },
    "sync": {
      "$id": "/properties/sync",
      "type": "array",
      "items": {
        "$id": "/properties/sync/items",
        "type": "object",
          "required": [
			"itemId",
			"state",
			"type",
			"updateTime"
		  ],
        "properties": {
          "itemId": {
            "$id": "/properties/sync/items/properties/itemId",
            "type": "string",
            "title": "The Itemid Schema ",
            "default": "",
            "examples": [
              "dafead5b-b6cd-41e7-a1e3-fcfff6d63381"
            ]
          },
          "state": {
            "$id": "/properties/sync/items/properties/state",
            "type": "string",
            "title": "The State Schema ",
            "default": "",
            "examples": [
              "STORE"
            ]
          },
          "type": {
            "$id": "/properties/sync/items/properties/type",
            "type": "string",
            "title": "The Type Schema ",
            "default": "",
            "examples": [
              "NOTE"
            ]
          },
          "updateTime": {
            "$id": "/properties/sync/items/properties/updateTime",
            "type": "integer",
            "title": "The Updatetime Schema ",
            "default": 0,
            "examples": [
              112222646
            ]
          }
        }
      }
    },
    "notes": {
      "$id": "/properties/notes",
      "type": "array",
      "items": {
        "$id": "/properties/notes/items",
        "type": "object",
        "required": [
			"id",
			"content",
			"page",
			"bookId"
		  ],
        "properties": {
          "id": {
            "$id": "/properties/notes/items/properties/id",
            "type": "string",
            "title": "The Id Schema ",
            "default": "",
            "examples": [
              "dafead5b-b6cd-41e7-a1e3-fcfff6d63381"
            ]
          },
          "content": {
            "$id": "/properties/notes/items/properties/content",
            "type": "string",
            "title": "The Content Schema ",
            "default": "",
            "examples": [
              "This is a note."
            ]
          },
          "page": {
            "$id": "/properties/notes/items/properties/page",
            "type": "integer",
            "title": "The Page Schema ",
            "default": 0,
            "examples": [
              1
            ]
          },
          "bookId": {
            "$id": "/properties/notes/items/properties/bookId",
            "type": "integer",
            "title": "The Bookid Schema ",
            "default": 0,
            "examples": [
              245
            ]
          }
        }
      }
    },
    "bookmarks": {
      "$id": "/properties/bookmarks",
      "type": "array",
      "items": {
        "$id": "/properties/bookmarks/items",
        "type": "object",
        "required": [
			"id",
			"name",
			"page",
			"bookId"
		  ],
        "properties": {
          "id": {
            "$id": "/properties/bookmarks/items/properties/id",
            "type": "string",
            "title": "The Id Schema ",
            "default": "",
            "examples": [
              "dafead5b-b6cd-41e7-a1e3-fcfff6d63383"
            ]
          },
          "name": {
            "$id": "/properties/bookmarks/items/properties/name",
            "type": "string",
            "title": "The Name Schema ",
            "default": "",
            "examples": [
              "The name of the bookmark"
            ]
          },
          "page": {
            "$id": "/properties/bookmarks/items/properties/page",
            "type": "integer",
            "title": "The Page Schema ",
            "default": 0,
            "examples": [
              1
            ]
          },
          "bookId": {
            "$id": "/properties/bookmarks/items/properties/bookId",
            "type": "integer",
            "title": "The Bookid Schema ",
            "default": 0,
            "examples": [
              245
            ]
          }
        }
      }
    },
    "drawings": {
      "$id": "/properties/drawings",
      "type": "array",
      "items": {
        "$id": "/properties/drawings/items",
        "type": "object",
        "required": [
			"id",
			"elements",
			"page",
			"bookId"
		  ],
        "properties": {
          "id": {
            "$id": "/properties/drawings/items/properties/id",
            "type": "string",
            "title": "The Id Schema ",
            "default": "",
            "examples": [
              "dafead5b-b6cd-41e7-a1e3-fcfff6d63384"
            ]
          },
          "bookId": {
            "$id": "/properties/drawings/items/properties/bookId",
            "type": "integer",
            "title": "The Bookid Schema ",
            "default": 0,
            "examples": [
              245
            ]
          },
          "page": {
            "$id": "/properties/drawings/items/properties/page",
            "type": "integer",
            "title": "The Page Schema ",
            "default": 0,
            "examples": [
              1
            ]
          },
          "elements": {
            "$id": "/properties/drawings/items/properties/elements",
            "type": "array",
            "items": {
              "$id": "/properties/drawings/items/properties/elements/items",
              "type": "object",
              "required": [
              		"id",
					"coordinates",
					"borderColor",
					"borderWidth"
		        ],
              "properties": {
                "id": {
                  "$id": "/properties/drawings/items/properties/elements/items/properties/id",
                  "type": "string",
                  "title": "The Id Schema ",
                  "default": "",
                  "examples": [
                    "svg23435236"
                  ]
                },
                "coordinates": {
                  "$id": "/properties/drawings/items/properties/elements/items/properties/coordinates",
                  "type": "array",
                  "items": {
                    "$id": "/properties/drawings/items/properties/elements/items/properties/coordinates/items",
                    "type": "object",
                    "required": [
	                    "x",
						"y"
			        ],
                    "properties": {
                      "x": {
                        "$id": "/properties/drawings/items/properties/elements/items/properties/coordinates/items/properties/x",
                        "type": "integer",
                        "title": "The X Schema ",
                        "default": 0,
                        "examples": [
                          12
                        ]
                      },
                      "y": {
                        "$id": "/properties/drawings/items/properties/elements/items/properties/coordinates/items/properties/y",
                        "type": "integer",
                        "title": "The Y Schema ",
                        "default": 0,
                        "examples": [
                          13
                        ]
                      }
                    }
                  }
                },
                "borderColor": {
                  "$id": "/properties/drawings/items/properties/elements/items/properties/borderColor",
                  "type": "string",
                  "title": "The Bordercolor Schema ",
                  "default": "",
                  "examples": [
                    "#ffffffff"
                  ]
                },
                "borderWidth": {
                  "$id": "/properties/drawings/items/properties/elements/items/properties/borderWidth",
                  "type": "integer",
                  "title": "The Borderwidth Schema ",
                  "default": 0,
                  "examples": [
                    1
                  ]
                }
              }
            }
          }
        }
      }
    },
    "highlights": {
      "$id": "/properties/highlights",
      "type": "array",
      "items": {
        "$id": "/properties/highlights/items",
        "type": "object",
        "required": [
			"id",
			"elements",
			"page",
			"bookId"
		  ],
        "properties": {
          "id": {
            "$id": "/properties/highlights/items/properties/id",
            "type": "string",
            "title": "The Id Schema ",
            "default": "",
            "examples": [
              "dafead5b-b6cd-41e7-a1e3-fcfff6d63385"
            ]
          },
          "bookId": {
            "$id": "/properties/highlights/items/properties/bookId",
            "type": "integer",
            "title": "The Bookid Schema ",
            "default": 0,
            "examples": [
              245
            ]
          },
          "page": {
            "$id": "/properties/highlights/items/properties/page",
            "type": "integer",
            "title": "The Page Schema ",
            "default": 0,
            "examples": [
              245
            ]
          },
          "elements": {
            "$id": "/properties/highlights/items/properties/elements",
            "type": "array",
            "items": {
              "$id": "/properties/highlights/items/properties/elements/items",
              "type": "object",
              "required": [
				"id",
				"color",
				"width",
				"height",
				"x",
				"y"
			  ],
              "properties": {
                "id": {
                  "$id": "/properties/highlights/items/properties/elements/items/properties/id",
                  "type": "string",
                  "title": "The Id Schema ",
                  "default": "",
                  "examples": [
                    "dafead5b-b6cd-41e7-a1e3-fcfff6d63386"
                  ]
                },
                "color": {
                  "$id": "/properties/highlights/items/properties/elements/items/properties/color",
                  "type": "string",
                  "title": "The Color Schema ",
                  "default": "",
                  "examples": [
                    "#ffffff00"
                  ]
                },
                "width": {
                  "$id": "/properties/highlights/items/properties/elements/items/properties/width",
                  "type": "integer",
                  "title": "The Width Schema ",
                  "default": 0,
                  "examples": [
                    67
                  ]
                },
                "height": {
                  "$id": "/properties/highlights/items/properties/elements/items/properties/height",
                  "type": "integer",
                  "title": "The Height Schema ",
                  "default": 0,
                  "examples": [
                    87
                  ]
                },
                "x": {
                  "$id": "/properties/highlights/items/properties/elements/items/properties/x",
                  "type": "integer",
                  "title": "The X Schema ",
                  "default": 0,
                  "examples": [
                    245
                  ]
                },
                "y": {
                  "$id": "/properties/highlights/items/properties/elements/items/properties/y",
                  "type": "integer",
                  "title": "The Y Schema ",
                  "default": 0,
                  "examples": [
                    676
                  ]
                }
              }
            }
          }
        }
      }
    }
  }
}
JSON;
