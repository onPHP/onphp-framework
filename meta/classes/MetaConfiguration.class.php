<?php
/***************************************************************************
 *   Copyright (C) 2006-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup MetaBase
	**/
	final class MetaConfiguration extends Singleton implements Instantiatable
	{
		/** @var MetaOutput */
		private $out = null;
        /** @var bool  */
		private $forcedGeneration	= false;
        /** @var bool  */
		private $dryRun				= false;

        /** @var MetaConfigurationCorePlugin */
        private $corePlugin;
        /** @var MetaConfigurationPluginInterface[] */
        private $externalPlugins = [];

		/**
		 * @return MetaConfiguration
		**/
		public static function me()
		{
			return Singleton::getInstance('MetaConfiguration');
		}

		/**
		 * @return MetaOutput
		**/
		public static function out()
		{
			return self::me()->getOutput();
		}

		/**
		 * @param $orly bool
		 * @return MetaConfiguration
		**/
		public function setForcedGeneration($orly)
		{
			$this->forcedGeneration = $orly;

			return $this;
		}

		public function isForcedGeneration()
		{
			return $this->forcedGeneration;
		}

		/**
		 * @param $dry bool
		 * @return MetaConfiguration
		**/
		public function setDryRun($dry)
		{
			$this->dryRun = $dry;

			return $this;
		}

		public function isDryRun()
		{
			return $this->dryRun;
		}

        /**
         * @return MetaConfigurationCorePlugin
         */
        public function getCorePlugin()
        {
            if (!isset($this->corePlugin)) {
                $this->corePlugin = new MetaConfigurationCorePlugin($this);
            }
            return $this->corePlugin;
        }

        /**
         * @param $name
         * @return MetaConfigurationPluginInterface
         * @throws MissingElementException
         */
        public function getExternalPlugin($name) {
            if (!isset($this->externalPlugins[$name])) {
                throw new MissingElementException('no plugin for "<' . $name . '>"');
            }
            return $this->externalPlugins[$name];
        }

        /**
         * @return MetaConfigurationPluginInterface[]
         */
        public function getPlugins()
        {
            return [ $this->getCorePlugin() ] + $this->externalPlugins;
        }


		/**
		 * @param      $metafile
		 * @param bool $generate
		 * @return MetaConfiguration
		 * @throws MissingElementException
		 * @throws UnsupportedMethodException
		 * @throws WrongArgumentException
		 */
		public function load($metafile, $generate = true)
		{
			$this->loadXml($metafile, $generate);

            foreach ($this->getPlugins() as $plugin) {
                $plugin->checkConfig();
            }

			return $this;
		}

        public function buildFiles()
        {
            foreach ($this->getPlugins() as $plugin) {
                $plugin->buildFiles();
            }
        }

		/**
		 * @return MetaConfiguration
		**/
		public function checkIntegrity()
		{
            foreach ($this->getPlugins() as $plugin) {
                $plugin->checkIntegrity();
            }
		}

		/**
		 * @param $drop bool
		 * @return MetaConfiguration
		**/
		public function checkForStaleFiles($drop = false)
		{
			foreach ($this->getPlugins() as $plugin) {
                $plugin->checkForStaleFiles($drop);
            }
		}

		/**
		 * @param $out MetaOutput
		 * @return MetaConfiguration
		**/
		public function setOutput(MetaOutput $out)
		{
			$this->out = $out;

			return $this;
		}

		/**
		 * @return MetaOutput
		**/
		public function getOutput()
		{
			return $this->out;
		}

		/**
		 * @param SimpleXMLElement $xml
		 * @param string $metafile
		 * @return MetaConfiguration
		**/
		private function processIncludes(SimpleXMLElement $xml, $metafile)
		{
			foreach ($xml->include as $include) {
				$file = (string) $include['file'];
				$path = dirname($metafile).'/'.$file;

				Assert::isTrue(
					is_readable($path),
					'can not include '.$file
				);

				$this->getOutput()->
					infoLine('Including "'.$path.'".')->
					newLine();

				$this->loadXml($path, !((string) $include['generate'] == 'false'));
			}

			return $this;
		}

		/**
		 * @param SimpleXMLElement $xml
		 * @return MetaConfiguration
		**/
		private function processPlugins(SimpleXMLElement $xml)
		{
			foreach ($xml->plugin as $plugin) {
				$class = (string) $plugin['class'];
				$namespace = (string) $plugin['name'];
				Assert::classExists($class,
                    'could not load plugin class ' . $class
                );
				Assert::isInstance($class, MetaConfigurationPluginInterface::class,
                    $class . ' is not a meta configuration plugin'
                );
                Assert::isFalse(isset($this->externalPlugins[$namespace]),
                    'another plugin is already defined for <' . $namespace . '>'
                );

                /** @var MetaConfigurationPluginInterface $plugin */
                $plugin = new $class($this);

				$this->externalPlugins[$namespace] = $plugin;

				$this->getOutput()
                    ->infoLine('Using plugin "' . $class . '" for <' . $namespace . '>.')
                    ->newLine();
			}

			return $this;
		}

		private function loadXml($metafile, $generate)
		{
			$contents = file_get_contents($metafile);

            foreach ($this->getPlugins() as $plugin) {
                // to validate XML properly, we need to specify full path to DTD file instead of just name
                foreach ($plugin->getDtdMapping() as $dtdFilename => $dtdFullFilePath) {
                    $contents = str_replace('"' . $dtdFilename . '"', '"' . $dtdFullFilePath . '"', $contents);
                }
            }

			$doc = new DOMDocument('1.0');
			$doc->loadXML($contents);

			try {
				$doc->validate();
			} catch (BaseException $e) {
				$error = libxml_get_last_error();
				throw new WrongArgumentException(
					$error->message.' in node placed on line '
					.$error->line.' in file '.$metafile
				);
			}

			$xml = simplexml_import_dom($doc);

            $rootName = $xml->getName();

            if ($rootName === 'metaconfiguration') {
                if (isset($xml->plugin)) {
                    $this->processPlugins($xml);
                }

                if (isset($xml->include)) {
                    $this->processIncludes($xml, $metafile);
                }

                $plugin = $this->getCorePlugin();

            } else {
                $plugin = $this->getExternalPlugin($rootName);
            }

            $plugin->loadConfig($xml, $metafile, $generate);

			return $this;
		}

	}
