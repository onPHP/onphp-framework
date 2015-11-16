<?php
/**
 * @author Mikhail Kulakovskiy <m@klkvsk.ru>
 * @date 2015-11-11
 */

interface MetaConfigurationPluginInterface
{
    /**
     * IMetaConfigurationPlugin constructor.
     * @param MetaConfiguration $metaConfiguration
     */
    public function __construct(MetaConfiguration $metaConfiguration);

    /**
     * Returns array of replacements for correcting dtd-schema file location
     * @return string[]
     */
    public function getDtdMapping();

    /**
     * Parses a part of configuration this plugin is responsible for.
     * Called multiple times for multiple files.
     * @param SimpleXMLElement $config
     * @param $metafile
     * @param $generate
     * @return mixed
     */
    public function loadConfig(SimpleXMLElement $config, $metafile, $generate);

    /**
     * Performs checks after all configuration is loaded.
     * @return void
     */
    public function checkConfig();

    /**
     * Generates resulting code
     * @return void
     */
    public function buildFiles();

    /**
     * Performs checks on generated code.
     * @return void
     */
    public function checkIntegrity();

    /**
     * Checks if some existing files are no more referenced in meta configuration.
     * @param bool $drop remove unreferenced files
     * @return void
     */
    public function checkForStaleFiles($drop);

}