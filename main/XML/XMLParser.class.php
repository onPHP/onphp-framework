<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Scheglov K.                                *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	class XMLParserException extends BaseException {}
	
	abstract class XMLParser
	{
		const BUFFER_SIZE		= 4096;
		
		private $parser			= null;
		private $filePointer	= null;

		private $handler = array(
			'character_data_handler'			=> 'cdataHandler',
			'default_handler'					=> 'defaultHandler',
			'processing_instruction_handler'	=> 'piHandler',
			'unparsed_entity_decl_handler'		=> 'unparsedHandler',
			'notation_decl_handler'				=> 'notationHandler',
			'external_entity_ref_handler'		=> 'entityrefHandler'
		);
	
		abstract public function startHandler($parser, $name, &$attrib);
		abstract public function endHandler($parser, $name);

		public function setHandler()
		{
			foreach ($this->handler as $xml_func => $method) {
				if (method_exists($this, $method)) {
					$xml_func = 'xml_set_' . $xml_func;
					$xml_func($this->parser, $method);
				}
			}

			xml_set_object($this->parser, $this);
			xml_set_element_handler($this->parser, 'startHandler', 'endHandler');
			xml_set_character_data_handler($this->parser, "cdataHandler");

			return $this;
		}

		public function setParser()
		{
			$this->parser = xml_parser_create("UTF-8");

			xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, true);

			return $this;
		}

		public function setFilePointer($file)
		{
			try {
				$this->filePointer = fopen($file, 'rb');
			} catch (BaseException $e) {
				throw new XMLParserException(
					"file '{$file}' could not be opened"
				);
			}
	
			return $this;
		}
	
		public function parse()
		{
			$content = '';

			while ($data = fread($this->filePointer, self::BUFFER_SIZE))
				$content .= $data;
			
			xml_parse($this->parser, $content);
	
			$this->free();
				
			return $this;
		}

		public function free()
		{
			if (isset($this->parser) && is_resource($this->parser)) {
				xml_parser_free($this->parser);
				unset($this->parser);
			}

			if (isset($this->filePointer) && is_resource($this->filePointer))
				fclose($this->filePointer);
			
			unset($this->filePointer);

			return null;
		}
	}
?>