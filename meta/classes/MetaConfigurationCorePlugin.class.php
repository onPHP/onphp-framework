<?php

/**
 * @author Mikhail Kulakovskiy <m@klkvsk.ru>
 * @date 2015-11-11
 */
class MetaConfigurationCorePlugin implements MetaConfigurationPluginInterface
{
    /** @var MetaConfiguration */
    protected $meta;

    /** @var MetaClass[] */
    protected $classes = array();
    /** @var bool[] (string)name => (bool)isDefault */
    protected $sources = array();
    /** @var null|string */
    protected $defaultSource = null;
    /** string[] (string)className => (string)parentClassName */
    protected $liaisons = array();
    /** @var string[][] (string)className => (string[])propertyClassNames */
    protected $references = array();
    /** @var bool  */
    protected $checkEnumerationRefIntegrity = false;
    /** @var bool  */
    protected $buildClasses         = true;
    /** @var bool  */
    protected $buildContainers      = true;
    /** @var bool  */
    protected $buildSchema          = true;
    /** @var bool  */
    protected $buildSchemaChanges   = true;

    /**
     * @return string[] (string)dtd-name => (string)dtd-filepath
     */
    public function getDtdMapping()
    {
        return [
            'meta.dtd' => ONPHP_META_PATH . 'dtd/meta.dtd'
        ];
    }

    /**
     * MetaConfigurationCorePlugin constructor.
     * @param MetaConfiguration $metaConfiguration
     */
    public function __construct(MetaConfiguration $metaConfiguration)
    {
        $this->meta = $metaConfiguration;
    }

    /**
     * @param SimpleXMLElement $xml
     * @param $metafile
     * @param $generate
     * @return void
     * @throws MissingElementException
     * @throws UnimplementedFeatureException
     * @throws WrongArgumentException
     */
    public function loadConfig(SimpleXMLElement $xml, $metafile, $generate)
    {
        // populate sources (if any)
        if (isset($xml->sources[0])) {
            foreach ($xml->sources[0] as $source) {
                $this->addSource($source);
            }
        }

        if (isset($xml->classes[0])) {
            $this->processClasses($xml, $metafile, $generate);
        }
    }

    /**
     * @throws MissingElementException
     * @throws UnsupportedMethodException
     * @throws WrongArgumentException
     */
    public function checkConfig()
    {
        // check sources
        foreach ($this->classes as $name => $class) {
            $sourceLink = $class->getSourceLink();
            if (isset($sourceLink)) {
                Assert::isTrue(
                    isset($this->sources[$sourceLink]),
                    "unknown source '{$sourceLink}' specified "
                    . "for class '{$name}'"
                );
            } elseif ($this->defaultSource) {
                $class->setSourceLink($this->defaultSource);
            }
        }

        foreach ($this->liaisons as $class => $parent) {
            if (isset($this->classes[$parent])) {

                Assert::isFalse(
                    $this->classes[$parent]->getTypeId()
                    == MetaClassType::CLASS_FINAL,

                    "'{$parent}' is final, thus can not have childs"
                );

                if (
                    $this->classes[$class]->getPattern()
                    instanceof DictionaryClassPattern
                )
                    throw new UnsupportedMethodException(
                        'DictionaryClass pattern does '
                        . 'not support inheritance'
                    );

                $this->classes[$class]->setParent(
                    $this->classes[$parent]
                );
            } else
                throw new MissingElementException(
                    "unknown parent class '{$parent}'"
                );
        }

        // search for referencing classes
        foreach ($this->references as $className => $list) {
            $class = $this->getClassByName($className);

            if (
                (
                    $class->getPattern() instanceof ValueObjectPattern
                ) || (
                    $class->getPattern() instanceof InternalClassPattern
                ) || (
                    $class->getPattern() instanceof AbstractClassPattern
                )
            ) {
                continue;
            }

            foreach ($list as $refer) {
                $remote = $this->getClassByName($refer);
                if (
                    (
                        $remote->getPattern() instanceof ValueObjectPattern
                    ) && (
                    isset($this->references[$refer])
                    )
                ) {
                    foreach ($this->references[$refer] as $holder) {
                        $this->classes[$className]
                            ->setReferencingClass($holder);
                    }
                } elseif (
                    (!$remote->getPattern() instanceof AbstractClassPattern)
                    && (!$remote->getPattern() instanceof InternalClassPattern)
                    && ($remote->getTypeId() <> MetaClassType::CLASS_ABSTRACT)
                ) {
                    $this->classes[$className]->setReferencingClass($refer);
                }
            }
        }

        // final sanity checking
        foreach ($this->classes as $name => $class) {
            $this->checkSanity($class);
        }

        // check for recursion in relations and spooked properties
        foreach ($this->classes as $name => $class) {
            foreach ($class->getProperties() as $property) {
                if ($property->getRelationId() == MetaRelation::ONE_TO_ONE) {
                    if (
                        (
                            (
                                $property->getType()->getClass()->getPattern()
                                instanceof SpookedClassPattern
                            ) || (
                                $property->getType()->getClass()->getPattern()
                                instanceof SpookedEnumerationPattern
                            ) || (
                                $property->getType()->getClass()->getPattern()
                                instanceof SpookedEnumPattern
                            ) || (
                                $property->getType()->getClass()->getPattern()
                                instanceof SpookedRegistryPattern
                            )
                        ) && (
                            $property->getFetchStrategy()
                            && (
                                $property->getFetchStrategy()->getId()
                                != FetchStrategy::LAZY
                            )
                        )
                    ) {
                        $property->setFetchStrategy(FetchStrategy::cascade());
                    } else {
                        $this->checkRecursion($property, $class);
                    }
                }
            }
        }
    }

    public function buildFiles()
    {
        if ($this->isBuildClasses()) {
            $this->buildClasses();
        }

        if ($this->isBuildContainers()) {
            $this->buildContainers();
        }

        if ($this->isBuildSchema()) {
            $this->buildSchema();
        }

        if ($this->isBuildSchemaChanges()) {
            $this->buildSchemaChanges();
        }
    }

    public function checkIntegrity()
    {
        $out = $this->getOutput();
        $out
            ->newLine()
            ->infoLine('Checking sanity of generated files: ')
            ->newLine();

        set_include_path(
            get_include_path() . PATH_SEPARATOR
            . ONPHP_META_BUSINESS_DIR . PATH_SEPARATOR
            . ONPHP_META_DAO_DIR . PATH_SEPARATOR
            . ONPHP_META_PROTO_DIR . PATH_SEPARATOR
            . ONPHP_META_AUTO_BUSINESS_DIR . PATH_SEPARATOR
            . ONPHP_META_AUTO_DAO_DIR . PATH_SEPARATOR
            . ONPHP_META_AUTO_PROTO_DIR . PATH_SEPARATOR
        );

        $out->info("\t");

        $formErrors = array();

        foreach ($this->classes as $name => $class) {
            /** @var $class MetaClass */
            if (
                !(
                    $class->getPattern() instanceof SpookedClassPattern
                    || $class->getPattern() instanceof SpookedEnumerationPattern
                    || $class->getPattern() instanceof SpookedEnumPattern
                    || $class->getPattern() instanceof SpookedRegistryPattern
                    || $class->getPattern() instanceof InternalClassPattern
                ) && (
                class_exists($class->getName(), true)
                )
            ) {
                $out->info($name, true);

                $info = new ReflectionClass($name);

                $this->checkClassSanity($class, $info);

                if ($info->implementsInterface('Prototyped'))
                    $this->checkClassSanity(
                        $class,
                        new ReflectionClass('Proto' . $name)
                    );

                if ($info->implementsInterface('DAOConnected'))
                    $this->checkClassSanity(
                        $class,
                        new ReflectionClass($name . 'DAO')
                    );

                foreach ($class->getInterfaces() as $interface)
                    Assert::isTrue(
                        $info->implementsInterface($interface),

                        'class ' . $class->getName()
                        . ' expected to implement interface ' . $interface
                    );

                // special handling for Enumeration instances
                if (
                    $class->getPattern() instanceof EnumerationClassPattern
                    || $class->getPattern() instanceof EnumClassPattern
                    || $class->getPattern() instanceof RegistryClassPattern
                ) {
                    $object = new $name(call_user_func(array($name, 'getAnyId')));

                    Assert::isTrue(
                        unserialize(serialize($object)) == $object
                    );

                    $out->info(', ');

                    if ($this->checkEnumerationRefIntegrity) {
                        if (
                            $object instanceof Enumeration
                            || $object instanceof Enum
                            || $object instanceof RegistryClassPattern
                        )
                            $this->checkEnumerationReferentialIntegrity(
                                $object,
                                $class->getTableName()
                            );
                    }


                    continue;
                }

                if ($class->getPattern() instanceof AbstractClassPattern) {
                    $out->info(', ');
                    continue;
                }

                /** @var Prototyped|DAOConnected $object */
                $object = new $name;
                $proto = $object->proto();
                $form = $proto->makeForm();

                foreach ($class->getProperties() as $property) {
                    Assert::isTrue(
                        $property->toLightProperty($class)
                        == $proto->getPropertyByName($property->getName()),

                        'defined property does not match autogenerated one - '
                        . $class->getName() . '::' . $property->getName()
                    );
                }

                if (!$object instanceof DAOConnected) {
                    $out->info(', ');
                    continue;
                }

                $dao = $object->dao();

                if (!$dao instanceof GenericDAO) {
                    $out->info(', ');
                    continue;
                }

                Assert::isEqual(
                    $dao->getIdName(),
                    $class->getIdentifier()->getColumnName(),
                    'identifier name mismatch in ' . $class->getName() . ' class'
                );

                if ($class->getPattern() instanceof NoSqlClassPattern) {
                    try {
                        NoSqlPool::getByDao($dao);
                    } catch (MissingElementException $e) {
                        // skipping
                        $out->info(', ');
                        continue;
                    }

                    $out->info(', ');
                } else {
                    try {
                        DBPool::getByDao($dao);
                    } catch (MissingElementException $e) {
                        // skipping
                        $out->info(', ');
                        continue;
                    }

                    $query =
                        Criteria::create($dao)
                            ->setLimit(1)
                            ->add(Expression::notNull($class->getIdentifier()->getName()))
                            ->addOrder($class->getIdentifier()->getName())
                            ->toSelectQuery();

                    $out->warning(
                        ' ('
                        . $query->getFieldsCount()
                        . '/'
                        . $query->getTablesCount()
                        . '/'
                    );

                    $clone = clone $object;

                    if (serialize($clone) == serialize($object))
                        $out->info('C', true);
                    else {
                        $out->error('C', true);
                    }

                    $out->warning('/');

                    try {
                        $object = $dao->getByQuery($query);
                        $form = $object->proto()->makeForm();
                        FormUtils::object2form($object, $form);

                        if ($errors = $form->getErrors()) {
                            $formErrors[$class->getName()] = $errors;

                            $out->error('F', true);
                        } else
                            $out->info('F', true);
                    } catch (ObjectNotFoundException $e) {
                        $out->warning('F');
                    }

                    $out->warning('/');

                    if (
                        Criteria::create($dao)
                            ->setFetchStrategy(FetchStrategy::cascade())
                            ->toSelectQuery()
                        == $dao->makeSelectHead()
                    ) {
                        $out->info('H', true);
                    } else {
                        $out->error('H', true);
                    }

                    $out->warning('/');

                    // cloning once again
                    $clone = clone $object;

                    try {
                        FormUtils::object2form($object, $form);
                        FormUtils::form2object($form, $object);
                    } catch (ObjectNotFoundException $e) {
                        $clone = null;
                        $out->error('(' . $e->getMessage() . ')');
                    }

                    if ($object != $clone) {
                        $out->error('T', true);
                    } else {
                        $out->info('T', true);
                    }

                    $out->warning(')')->info(', ');
                }

            }
        }

        $out->infoLine('done.');

        if ($formErrors) {
            $out->newLine()->errorLine('Errors found:')->newLine();

            foreach ($formErrors as $className => $errors) {
                $out->errorLine("\t" . $className . ':', true);

                foreach ($errors as $propertyName => $error) {
                    $out->errorLine(
                        "\t\t$propertyName - "
                        . ($error == Form::WRONG ? 'wrong' : 'missing')
                    );
                }

                $out->newLine();
            }
        }

        return $this;
    }

    /**
     * @param bool $drop
     * @return $this
     */
    public function checkForStaleFiles($drop = false)
    {
        $this->getOutput()
            ->newLine()
            ->infoLine('Checking for stale files: ');

        return $this
            ->checkDirectory(ONPHP_META_AUTO_BUSINESS_DIR, 'Auto', null, $drop)
            ->checkDirectory(ONPHP_META_AUTO_DAO_DIR, 'Auto', 'DAO', $drop)
            ->checkDirectory(ONPHP_META_AUTO_PROTO_DIR, 'AutoProto', null, $drop);
    }

    /**
     * @return boolean
     */
    public function isBuildClasses()
    {
        return $this->buildClasses;
    }

    /**
     * @param boolean $buildClasses
     * @return $this
     */
    public function setBuildClasses($buildClasses)
    {
        $this->buildClasses = $buildClasses;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isBuildContainers()
    {
        return $this->buildContainers;
    }

    /**
     * @param boolean $buildContainers
     * @return MetaConfigurationCorePlugin
     */
    public function setBuildContainers($buildContainers)
    {
        $this->buildContainers = $buildContainers;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isBuildSchema()
    {
        return $this->buildSchema;
    }

    /**
     * @param boolean $buildSchema
     * @return MetaConfigurationCorePlugin
     */
    public function setBuildSchema($buildSchema)
    {
        $this->buildSchema = $buildSchema;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isBuildSchemaChanges()
    {
        return $this->buildSchemaChanges;
    }

    /**
     * @param boolean $buildSchemaChanges
     * @return MetaConfigurationCorePlugin
     */
    public function setBuildSchemaChanges($buildSchemaChanges)
    {
        $this->buildSchemaChanges = $buildSchemaChanges;
        return $this;
    }

    public function buildClasses()
    {
        $out = $this->getOutput();

        $out->infoLine('Building classes:');

        foreach ($this->classes as $name => $class) {
            if (
                !$class->doBuild()
                || ($class->getPattern() instanceof InternalClassPattern)
            ) {
                continue;
            } else {
                $out->infoLine("\t" . $name . ':');
            }

            $class->dump();
            $out->newLine();
        }

        return $this;
    }

    public function buildSchema()
    {
        $out = $this->getOutput();
        $out->newLine()->infoLine('Building DB schema:');

        $schema = SchemaBuilder::getHead();

        $tables = array();

        foreach ($this->classes as $class) {
            if (
                (!$class->getParent() && !count($class->getProperties()))
                || !$class->getPattern()->tableExists()
            ) {
                continue;
            }

            foreach ($class->getAllProperties() as $property)
                $tables[$class->getTableName()][// just to sort out dupes, if any
                $property->getColumnName()] = $property;
        }

        foreach ($tables as $name => $propertyList)
            if ($propertyList)
                $schema .= SchemaBuilder::buildTable($name, $propertyList);

        foreach ($this->classes as $class) {
            if (!$class->getPattern()->tableExists()) {
                continue;
            }

            $schema .= SchemaBuilder::buildRelations($class);
        }

        $schema .= '?>';

        BasePattern::dumpFile(
            ONPHP_META_AUTO_DIR . 'schema.php',
            Format::indentize($schema)
        );

        return $this;
    }

    public function buildSchemaChanges()
    {
        $out = $this->getOutput();
        $out
            ->newLine()
            ->infoLine('Suggested DB-schema changes: ');

        /** @noinspection PhpIncludeInspection */
        require ONPHP_META_AUTO_DIR . 'schema.php';
        if (!isset($schema) || !($schema instanceof DBSchema)) {
            $out->errorLine('Could not import schema from schema.php');
            return $this;
        }

        /** @var $class MetaClass */
        foreach ($this->classes as $class) {
            if (
                $class->getTypeId() == MetaClassType::CLASS_ABSTRACT
                || $class->getPattern() instanceof EnumerationClassPattern
                || $class->getPattern() instanceof EnumClassPattern
                || $class->getPattern() instanceof RegistryClassPattern
            )
                continue;

            try {
                $target = $schema->getTableByName($class->getTableName());
            } catch (MissingElementException $e) {
                // dropped or tableless
                continue;
            }

            if ($class->getPattern() instanceof NoSqlClassPattern) {
                // checking NoSQL-DB
                try {
                    NoSqlPool::me()->getLink($class->getSourceLink());
                } catch (Exception $e) {
                    $out->errorLine(
                        'Can not connect using source link in \''
                        . $class->getName() . '\' class, skipping this step.'
                    );

                    break;
                }
            } else {
                // checking SQL-DB
                try {
                    $db = DBPool::me()->getLink($class->getSourceLink());
                } catch (Exception $e) {
                    $out->errorLine(
                        'Can not connect using source link in \''
                        . $class->getName() . '\' class, skipping this step.'
                    );

                    break;
                }

                try {
                    $source = $db->getTableInfo($class->getTableName());
                } catch (UnsupportedMethodException $e) {
                    $out->errorLine(
                        get_class($db)
                        . ' does not support tables introspection yet.',

                        true
                    );

                    break;
                } catch (ObjectNotFoundException $e) {
                    $out->remarkLine(
                        $target->toDialectString($db->getDialect()),
                        ConsoleMode::FG_BLUE,
                        true
                    );

                    continue;
                }

                $diff = DBTable::findDifferences(
                    $db->getDialect(),
                    $source,
                    $target
                );

                if ($diff) {
                    foreach ($diff as $line)
                        $out->warningLine($line);

                    $out->newLine();
                }

                $className = $class->getName();
                /** @var $property MetaClassProperty */
                foreach ($class->getProperties() as $property) {
                    if ($property->getRelationId() == MetaRelation::MANY_TO_MANY) {
                        try {
                            /** @var ManyToManyLinked $manyToManyClass */
                            $manyToManyClass = $property->toLightProperty($class)->getContainerName($className);

                            if (is_subclass_of($manyToManyClass, 'ManyToManyLinked')) {
                                /** @var ManyToManyLinked $manyToManyObject */
                                $manyToManyObject = new $manyToManyClass(new $className);

                                $target = $schema->getTableByName($manyToManyObject->getHelperTable());

                                // may throw ObjectNotFoundException
                                $db->getTableInfo($manyToManyObject->getHelperTable());
                            }
                        } catch (ObjectNotFoundException $e) {
                            $out->remarkLine(
                                $target->toDialectString($db->getDialect()),
                                ConsoleMode::FG_MAGENTA,
                                true
                            );
                        }
                    }
                }
            }

        }

        return $this;
    }

    public function buildContainers()
    {
        $force = $this->meta->isForcedGeneration();

        $out = $this->getOutput();
        $out
            ->infoLine('Building containers: ')
            ->newLine();

        foreach ($this->classes as $class) {
            foreach ($class->getProperties() as $property) {
                if (
                    $property->getRelation()
                    && ($property->getRelationId() != MetaRelation::ONE_TO_ONE)
                ) {
                    $className = $class->getName() . ucfirst($property->getName()) . 'DAO';
                    $fileName = ONPHP_META_DAO_DIR . $className . EXT_CLASS;

                    if ($force || !file_exists($fileName)) {
                        BasePattern::dumpFile(
                            $fileName,
                            Format::indentize(
                                ContainerClassBuilder::buildContainer(
                                    $class,
                                    $property
                                )
                            )
                        );
                    }

                    // check for old-style naming
                    $oldStyle =
                        ONPHP_META_DAO_DIR
                        . $class->getName()
                        . 'To'
                        . $property->getType()->getClassName()
                        . 'DAO'
                        . EXT_CLASS;

                    if (is_readable($oldStyle)) {
                        $out
                            ->newLine()
                            ->error('remove manually: ' . $oldStyle);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @param $orly bool
     * @return self
     **/
    public function setWithEnumerationRefIntegrityCheck($orly)
    {
        $this->checkEnumerationRefIntegrity = $orly;

        return $this;
    }

    public function isWithEnumerationRefIntegrityCheck()
    {
        return $this->checkEnumerationRefIntegrity;
    }

    protected function checkRecursion(MetaClassProperty $property, MetaClass $holder, $paths = array())
    {
        Assert::isTrue(
            $property->getRelationId()
            == MetaRelation::ONE_TO_ONE
        );

        if (
            $property->getFetchStrategy()
            && $property->getFetchStrategy()->getId() != FetchStrategy::JOIN
        ) {
            return false;
        }

        $remote = $property->getType()->getClass();

        if (isset($paths[$holder->getName()][$remote->getName()]))
            return true;
        else {
            $paths[$holder->getName()][$remote->getName()] = true;

            foreach ($remote->getProperties() as $remoteProperty) {
                if (
                    $remoteProperty->getRelationId()
                    == MetaRelation::ONE_TO_ONE
                ) {
                    if (
                    $this->checkRecursion(
                        $remoteProperty,
                        $holder,
                        $paths
                    )
                    ) {
                        $remoteProperty->setFetchStrategy(
                            FetchStrategy::cascade()
                        );
                    }
                }
            }
        }

        return false;
    }


    protected function checkClassSanity(MetaClass $class, ReflectionClass $info)
    {
        switch ($class->getTypeId()) {
            case null:
                break;

            case MetaClassType::CLASS_ABSTRACT:
                Assert::isTrue(
                    $info->isAbstract(),
                    'class ' . $info->getName() . ' expected to be abstract'
                );
                Assert::isTrue(
                    $class->getPattern() instanceof AbstractClassPattern,
                    'class ' . $info->getName() . ' must use AbstractClassPattern'
                );
                break;

            case MetaClassType::CLASS_FINAL:
                Assert::isTrue(
                    $info->isFinal(),
                    'class ' . $info->getName() . ' expected to be final'
                );
                break;

            case MetaClassType::CLASS_SPOOKED:
            default:
                Assert::isUnreachable();
                break;
        }

        if ($public = $info->getProperties(ReflectionProperty::IS_PUBLIC)) {
            Assert::isUnreachable(
                $class->getName()
                . ' contains properties with evil visibility:'
                . "\n"
                . print_r($public, true)
            );
        }

        return $this;
    }

    protected function checkEnumerationReferentialIntegrity($enumeration, $tableName)
    {
        Assert::isTrue(
            (
                $enumeration instanceof Enumeration
                || $enumeration instanceof Enum
                || $enumeration instanceof Registry
            ),
            'argument enumeation must be instacne of Enumeration, Enum or Registry! gived, "' . gettype($enumeration) . '"'
        );

        $updateQueries = null;

        $db = DBPool::me()->getLink();

        $class = get_class($enumeration);

        /** @var NamedObject[] $list */
        if ($enumeration instanceof Enumeration)
            $list = $enumeration::makeObjectList();
        elseif ($enumeration instanceof Enum)
            $list = ClassUtils::callStaticMethod($class . '::getList');
        elseif ($enumeration instanceof Registry)
            $list = ClassUtils::callStaticMethod($class . '::getList');
        else
            throw new WrongArgumentException('dont know how to get list of ' . get_class($enumeration));

        $ids = array();
        foreach ($list as $enumerationObject)
            $ids[$enumerationObject->getId()] = $enumerationObject->getName();

        $rows =
            $db->querySet(
                OSQL::select()->from($tableName)
                    ->multiGet('id', 'name')
            );

        echo "\n";

        foreach ($rows as $row) {
            if (!isset($ids[$row['id']]))
                echo "Class '{$class}', strange id: {$row['id']} found. \n";
            else {
                if ($ids[$row['id']] != $row['name']) {
                    echo "Class '{$class}',id: {$row['id']} sync names. \n";

                    $updateQueries .=
                        OSQL::update($tableName)
                            ->set('name', $ids[$row['id']])
                            ->where(Expression::eq('id', $row['id']))
                            ->toDialectString($db->getDialect())
                        . ";\n";
                }

                unset($ids[$row['id']]);
            }
        }

        foreach ($ids as $id => $name)
            echo "Class '{$class}', id: {$id} not present in database. \n";

        echo $updateQueries;

        return $this;
    }


    /**
     * @param $name string
     * @throws MissingElementException
     * @return MetaClass
     **/
    public function getClassByName($name)
    {
        if (isset($this->classes[$name]))
            return $this->classes[$name];

        throw new MissingElementException(
            "knows nothing about '{$name}' class"
        );
    }

    /**
     * @return MetaClass[]
     */
    public function getClassList()
    {
        return $this->classes;
    }

    /**
     * @param SimpleXMLElement $source
     * @return self
     **/
    protected function addSource(SimpleXMLElement $source)
    {
        $name = (string)$source['name'];

        $default =
            isset($source['default']) && (string)$source['default'] == 'true'
                ? true
                : false;

        Assert::isFalse(
            isset($this->sources[$name]),
            "duplicate source - '{$name}'"
        );

        Assert::isFalse(
            $default && $this->defaultSource !== null,
            'too many default sources'
        );

        $this->sources[$name] = $default;

        if ($default)
            $this->defaultSource = $name;

        return $this;
    }

    /**
     * @param SimpleXMLElement $xml
     * @param                  $metafile
     * @param                  $generate
     * @return $this
     * @throws MissingElementException
     * @throws UnimplementedFeatureException
     * @throws UnsupportedMethodException
     * @throws WrongArgumentException
     */
    protected function processClasses(SimpleXMLElement $xml, $metafile, $generate)
    {
        foreach ($xml->classes[0] as $xmlClass) {
            $name = (string)$xmlClass['name'];

            Assert::isFalse(
                isset($this->classes[$name]),
                'class name collision found for ' . $name
            );

            $class = new MetaClass($name);

            if (isset($xmlClass['source']))
                $class->setSourceLink((string)$xmlClass['source']);

            if (isset($xmlClass['table']))
                $class->setTableName((string)$xmlClass['table']);

            if (isset($xmlClass['type'])) {
                $type = (string)$xmlClass['type'];

                if ($type == 'spooked') {
                    $this->getOutput()
                        ->warning($class->getName())
                        ->warningLine(': uses obsoleted "spooked" type.')
                        ->newLine();
                }

                $class->setType(
                    new MetaClassType(
                        (string)$xmlClass['type']
                    )
                );
            }

            // lazy existence checking
            if (isset($xmlClass['extends']))
                $this->liaisons[$class->getName()] = (string)$xmlClass['extends'];

            // populate implemented interfaces
            foreach ($xmlClass->implement as $xmlImplement)
                $class->addInterface((string)$xmlImplement['interface']);

            if (isset($xmlClass->properties[0]->identifier)) {

                $id = $xmlClass->properties[0]->identifier;

                if (!isset($id['name']))
                    $name = 'id';
                else
                    $name = (string)$id['name'];

                if (!isset($id['type']))
                    $type = 'BigInteger';
                else
                    $type = (string)$id['type'];

                $property = $this->makeProperty(
                    $name,
                    $type,
                    $class,
                    // not casting to int because of Numeric possible size
                    (string)$id['size']
                );

                if (isset($id['column'])) {
                    $property->setColumnName(
                        (string)$id['column']
                    );
                } elseif (
                    $property->getType() instanceof ObjectType
                    && !$property->getType()->isGeneric()
                ) {
                    $property->setColumnName($property->getConvertedName() . '_id');
                } else {
                    $property->setColumnName($property->getConvertedName());
                }

                $property
                    ->setIdentifier(true)
                    ->required();

                $class->addProperty($property);

                unset($xmlClass->properties[0]->identifier);
            }

            $class->setPattern(
                $this->guessPattern((string)$xmlClass->pattern['name'])
            );

            if ((string)$xmlClass->pattern['fetch'] == 'cascade')
                $class->setFetchStrategy(FetchStrategy::cascade());

            if ($class->getPattern() instanceof InternalClassPattern) {
                Assert::isTrue(
                    $metafile === ONPHP_META_PATH . 'internal.xml',
                    'internal classes can be defined only in onPHP, sorry'
                );
            } elseif (
                (
                    $class->getPattern() instanceof SpookedClassPattern
                ) || (
                    $class->getPattern() instanceof SpookedEnumerationPattern
                ) || (
                    $class->getPattern() instanceof SpookedEnumPattern
                ) || (
                    $class->getPattern() instanceof SpookedRegistryPattern
                )
            ) {
                $class->setType(
                    new MetaClassType(
                        MetaClassType::CLASS_SPOOKED
                    )
                );
            }

            // populate properties
            foreach ($xmlClass->properties[0] as $xmlProperty) {

                $property = $this->makeProperty(
                    (string)$xmlProperty['name'],
                    (string)$xmlProperty['type'],
                    $class,
                    (string)$xmlProperty['size']
                );

                if (isset($xmlProperty['column'])) {
                    $property->setColumnName(
                        (string)$xmlProperty['column']
                    );
                } elseif (
                    $property->getType() instanceof ObjectType
                    && !$property->getType()->isGeneric()
                ) {
                    if (
                        isset(
                            $this->classes[$property->getType()->getClassName()]
                        ) && (
                            $property->getType()->getClass()->getPattern()
                            instanceof InternalClassPattern
                        )
                    ) {
                        throw new UnimplementedFeatureException(
                            'you can not use internal classes directly atm'
                        );
                    }

                    $property->setColumnName($property->getConvertedName() . '_id');
                } else if ($property->getType() instanceof ArrayOfEnumerationsType) {
                    $property->setColumnName($property->getConvertedName() . '_ids');
                } else {
                    $property->setColumnName($property->getConvertedName());
                }

                if ((string)$xmlProperty['required'] == 'true')
                    $property->required();

                if (isset($xmlProperty['identifier'])) {
                    throw new WrongArgumentException(
                        'obsoleted identifier description found in '
                        . "{$class->getName()} class;\n"
                        . 'you must use <identifier /> instead.'
                    );
                }

                if (!$property->getType()->isGeneric()) {

                    if (!isset($xmlProperty['relation']))
                        throw new MissingElementException(
                            'relation should be set for non-generic '
                            . "property '{$property->getName()}' type '"
                            . get_class($property->getType()) . "'"
                            . " of '{$class->getName()}' class"
                        );
                    else {
                        $property->setRelation(
                            MetaRelation::makeFromName(
                                (string)$xmlProperty['relation']
                            )
                        );

                        if ($fetch = (string)$xmlProperty['fetch']) {
                            Assert::isTrue(
                                $property->getRelationId()
                                == MetaRelation::ONE_TO_ONE,

                                'fetch mode can be specified
									only for OneToOne relations'
                            );

                            if ($fetch == 'lazy')
                                $property->setFetchStrategy(
                                    FetchStrategy::lazy()
                                );
                            elseif ($fetch == 'cascade')
                                $property->setFetchStrategy(
                                    FetchStrategy::cascade()
                                );
                            else
                                throw new WrongArgumentException(
                                    'strange fetch mode found - ' . $fetch
                                );
                        }

                        if (
                            (
                                (
                                    $property->getRelationId()
                                    == MetaRelation::ONE_TO_ONE
                                ) && (
                                    $property->getFetchStrategyId()
                                    != FetchStrategy::LAZY
                                )
                            ) && (
                                $property->getType()->getClassName()
                                <> $class->getName()
                            )
                        ) {
                            $this->references[$property->getType()->getClassName()][]
                                = $class->getName();
                        }

                        if ((string)$xmlProperty['reference'] == 'false') {
                            $property->skipReference();
                        } else {
                            $property->buildReference();
                        }

                    }
                }

                if (isset($xmlProperty['default'])) {
                    // will be correctly autocasted further down the code
                    $property->getType()->setDefault(
                        (string)$xmlProperty['default']
                    );
                }

                $class->addProperty($property);

                /**
                 * класс содержащий свойство с TranslatedStoreType
                 * должен реализовывать Translatable, иначе переводы не будут работать
                 * добавляем Translatable, если его нет
                 */
                if ($property->getType() instanceof TranslatedStoreType) {
                    if (!in_array('Translatable', $class->getInterfaces())) {
                        $class->addInterface('Translatable');
                    }
                }
            }

            $class->setBuild($generate);

            $this->classes[$class->getName()] = $class;
        }

        return $this;
    }

    /**
     * @param string $directory
     * @param string $preStrip
     * @param string $postStrip
     * @param bool $drop
     * @return $this
     */
    protected function checkDirectory($directory, $preStrip, $postStrip, $drop = false)
    {
        $out = $this->getOutput();

        foreach (
            glob($directory . '*.class.php', GLOB_NOSORT)
            as $filename
        ) {
            $name =
                substr(
                    basename($filename, $postStrip . EXT_CLASS),
                    strlen($preStrip)
                );

            if (!isset($this->classes[$name])) {
                $out->warning(
                    "\t"
                    . str_replace(
                        getcwd() . DIRECTORY_SEPARATOR,
                        null,
                        $filename
                    )
                );

                if ($drop) {
                    try {
                        unlink($filename);
                        $out->infoLine(' removed.');
                    } catch (Exception $e) {
                        $out->errorLine(' failed to remove.');
                    }
                } else {
                    $out->newLine();
                }
            }
        }

        return $this;
    }

    /**
     * @param string $name
     * @param string $type
     * @param MetaClass $class
     * @param           $size
     * @return MetaClassProperty
     * @throws WrongArgumentException
     */
    protected function makeProperty($name, $type, MetaClass $class, $size)
    {
        Assert::isFalse(
            strpos($name, '_'),
            'naming convention violation spotted'
        );

        $parameters = array();
        if (strpos($type, ':')) {
            list($type, $parameters) = explode(':', $type, 2);
            $parameters = explode(',', $parameters);
        }

        if (!$name || !$type)
            throw new WrongArgumentException(
                'strange name or type given: "' . $name . '" - "' . $type . '"'
            );

        if (is_readable(ONPHP_META_TYPES . $type . 'Type' . EXT_CLASS))
            $typeClass = $type . 'Type';
        else
            $typeClass = 'ObjectType';

        $property = new MetaClassProperty($name, new $typeClass($type, $parameters), $class);

        if ($size)
            $property->setSize($size);
        else {
            Assert::isTrue(
                (
                !$property->getType()
                    instanceof FixedLengthStringType
                ) && (
                !$property->getType()
                    instanceof NumericType
                ) && (
                !$property->getType()
                    instanceof HttpUrlType
                ),

                'size is required for "' . $property->getName() . '"'
            );
        }

        return $property;
    }

    /**
     * @param string $name
     * @throws MissingElementException
     * @return GenerationPattern
     **/
    protected function guessPattern($name)
    {
        $class = $name . 'Pattern';

        if (is_readable(ONPHP_META_PATTERNS . $class . EXT_CLASS))
            return Singleton::getInstance($class);

        throw new MissingElementException(
            "unknown pattern '{$name}'"
        );
    }

    /**
     * @param MetaClass $class
     * @return MetaConfiguration
     * @throws WrongArgumentException
     */
    protected function checkSanity(MetaClass $class)
    {
        if (
            (
                !$class->getParent()
                || (
                    $class->getFinalParent()->getPattern()
                    instanceof InternalClassPattern
                )
            )
            && (!$class->getPattern() instanceof ValueObjectPattern)
            && (!$class->getPattern() instanceof AbstractClassPattern)
            && (!$class->getPattern() instanceof InternalClassPattern)
        ) {
            Assert::isTrue(
                $class->getIdentifier() !== null,
                'only value objects can live without identifiers. '
                . 'do not use them anyway ('
                . $class->getName() . ')'
            );
        }

        if (
            $class->getType()
            && $class->getTypeId()
            == MetaClassType::CLASS_SPOOKED
        ) {
            Assert::isFalse(
                count($class->getProperties()) > 1,
                'spooked classes must have only identifier: '
                . $class->getName()
            );

            Assert::isTrue(
                ($class->getPattern() instanceof SpookedClassPattern
                    || $class->getPattern() instanceof SpookedEnumerationPattern
                    || $class->getPattern() instanceof SpookedEnumPattern
                    || $class->getPattern() instanceof SpookedRegistryPattern),
                'spooked classes must use spooked patterns only: '
                . $class->getName()
            );
        }

        foreach ($class->getProperties() as $property) {
            if (
                !$property->getType()->isGeneric()
                && $property->getType() instanceof ObjectType
                &&
                $property->getType()->getClass()->getPattern()
                instanceof ValueObjectPattern
            ) {
                Assert::isTrue(
                    $property->isRequired(),
                    'optional value object is not supported:'
                    . $property->getName() . ' @ ' . $class->getName()
                );

                Assert::isTrue(
                    $property->getRelationId() == MetaRelation::ONE_TO_ONE,
                    'value objects must have OneToOne relation: '
                    . $property->getName() . ' @ ' . $class->getName()

                );
            } elseif (
                ($property->getFetchStrategyId() == FetchStrategy::LAZY)
                && $property->getType()->isGeneric()
            ) {
                throw new WrongArgumentException(
                    'lazy one-to-one is supported only for '
                    . 'non-generic object types '
                    . '(' . $property->getName()
                    . ' @ ' . $class->getName()
                    . ')'
                );
            }
        }

        return $this;
    }

    /** @return MetaOutput */
    protected function getOutput() {
        return $this->meta->getOutput();
    }
}