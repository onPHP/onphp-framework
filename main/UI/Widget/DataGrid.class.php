<?php
/**
 * Виджет для отображения информации в виде таблицы или сводки
 * @author Михаил Кулаковский <m@klkvsk.ru>
 * @date 2012.03.19
 */
class DataGrid extends BaseWidget
{
    /** @var DataGrid для вложенных таблиц */
    protected $parent = null;

    /** @var int предел вложенности */
    protected static $maxNesting = 1;

    /** @var array список дочерних объектов */
    protected $objects = array();


    /** @var array список 'fieldID' => 'Field name' */
    protected $fields = array();

	/** @var array список локализованных полей */
	protected $localizedFields = null;

    /** @var array список полей, по которым можно сортировать.
     * Фактически, исключает поля, добавленные через addColumn */
    protected $sortingFields = array();

    /** @var array список 'fieldID' => callback() */
    protected $renderers = array();

    /** @var array массив строк таблицы */
    protected $rows = array();

    /** @var int ИД строки с "итого" */
    protected $totalId = null;


    /** @var array аттрибуты заголовков таблицы <th> */
    protected $fieldHtmlOptions = array();

    /** @var array аттрибуты <form> */
    protected $formHtmlOptions = array();

	/** @var bool показывать начальный тег */
	protected $showHeadTag = true;

	/** @var bool показывать заголовки или нет */
	protected $showHeader = true;

    /** @var bool показывать кнопки сортировки */
    protected $showSorting = true;

    /** @var bool делать редактируемые поля */
    protected $isEditor = false;

	/** @var Form для вывода ошибок в заполнении полей */
	protected $form = null;

	/** @var string текстовый вывод булевого типа */
	public $trueName = null;

	/** @var string текстовый вывод булевого типа */
	public $falseName = null;
    
    /** @var array аттрибуты строки таблицы */
    public $rowAttrs = array();

    /**
     * Создает таблицу вида сводки (заголовок слева)
     * @static
     * @return DataGrid
     */
    public static function details() {
        $self = self::create();
        $self->templateName = 'DataGrid_details';
        $self->showSorting = false;
        return $self;
    }

    /**
     * Создает обычную таблицу
     * @static
     * @return DataGrid
     */
    public static function table() {
        $self = self::create();
        $self->templateName = 'DataGrid_table';
        $self->showSorting = true;
        return $self;
    }

    /**
     * DataGrid::details() в виде редактируемой формы.
     * Следует добавлять только 1 объект.
     * @static
     * @return DataGrid
     */
    public static function editor($subfield = null) {
        $self = self::details();
        $self->isEditor = true;
        return $self;
    }

    /**
     * @static
     * @return DataGrid
     */
    private static function create() {
		$self = new self;
		$self->trueName  =  __('Да');
		$self->falseName =  __('Нет');
        return $self;
    }

    /**
     * Вызывает addRow для каждой из переданных строк
     * @param array $data
     * @return DataGrid
     * @throws WrongArgumentException
     */
    public function addRows(array $data) {
        foreach ($data as $key=>$row) {
            $this->addRow($row, $key);
        }
        return $this;
    }

    /**
     * Добавляет строку в таблицу, определяет тип и имена ее полей
     * @param $data
     * @return DataGrid
     * @throws WrongArgumentException
     */
    public function addRow($data, $key=null) {
        $rowId = count($this->rows); // id следующей строки
		if( isset($key) && $key=='total' ) {
			$this->totalId = $rowId;
		}

        // если это объект, то смотрим его поля в протипе
        // и через геттеры получаем все параметры, а если
        // это массив, то берем его как есть.

        if ($data instanceof Prototyped) {
			if (
				$data instanceof TranslatableFieldsObject
				&& is_null($this->localizedFields)
			) {
				/** @var $data TranslatableFieldsObject */
				$localizedFields = $data->getLocalizedFields();
				$this->localizedFields = array_merge(
					array_keys($localizedFields),
					array_values($localizedFields)
				);
			}
            /** @var $data Prototyped */
            $this->objects[$rowId] = $data;
            $fieldIds = array();
//			$this->rows[$rowId] = null;
            $row = array();
			/** @var $property LightMetaProperty */
            foreach ($data->proto()->getPropertyList() as $property) {
//                try {
//					$value = $this->getPropertyValue($rowId, $property);
//                } catch (BadMethodCallException $e) {
//                    continue;
//                } catch (ObjectNotFoundException $e) {
//					$value = null;
//				}
                $fieldIds[] = $property->getName();
                $row[$property->getName()] = null;
//				$value = null;
            }
        } else if (is_array($data)) {
            $fieldIds = array_keys($data);
            $row = $data;
        } else {
            throw new WrongArgumentException('$data should be either array or prototyped object');
        }
        // сохраним в список сортируемых полей
        foreach ($fieldIds as $fieldId) {
            if (!in_array($fieldId, $this->sortingFields)) {
                $this->sortingFields[] = $fieldId;
            }
        }

        // построим массив полей в виде 'имяПоля' => 'Имя поля'
        // ключ - имя параметра, значение - имя для отображения
        $fields = array();
        foreach($fieldIds as $fieldId) {
            $fieldName = self::beautifyFieldName($fieldId);
            $fields[$fieldId] = $fieldName;
        }
        // сливаем с существующим списком, чтобы ничего не потерять,
        // если например отработали не все геттеры, и поле пропущено
        $this->fields = array_merge($this->fields, $fields);

        // записываем данные
        foreach($row as $fieldId => $value) {
			$property = ($data instanceof Prototyped) ? $data->proto()->getPropertyByName($fieldId) : null;
            $this->setField($rowId, $fieldId, $value, $property);
        }

        return $this;
    }

	/**
	 * @param $rowId
	 * @param $property
	 * @return mixed
	 */
	protected function getPropertyValue($rowId, $property) {
		$getter = $property->getGetter();
		$value = $this->objects[$rowId]->$getter();
		return $value;
	}

	/**
     * Выставляет значение конкретного поля конкретной строки
     * @param $rowId
     * @param $fieldId
     * @param $value
     * @param $property LightMetaProperty если null, определится
     * @return DataGrid
     */
    private function setField($rowId, $fieldId, $value, $property = null) {
        $this->rows[$rowId][$fieldId] = $value;
        if (!isset($this->renderers[$fieldId])) {
            if ($this->isEditor) {
                if ($property == null) {
                    /** @var $object Prototyped */
                    $object = $this->objects[$rowId];
                    if ( !($object instanceof Prototyped) ) {
                        throw new WrongArgumentException;
                    }
                    $property = $object->proto()->getPropertyByName($fieldId);
                }

                $this->renderers[$fieldId] = $this->getEditRenderer($fieldId, $property);
            } else {
				if($property instanceof LightMetaProperty) {
					$this->renderers[$fieldId] = $this->getLazyViewRenderer($fieldId, $property);
				} elseif($value !== null) {
                    $this->renderers[$fieldId] = $this->getViewRenderer($value);
                }
            }
        }

        return $this;
    }


    /**
     * Дополнительная колонка
     * @param string $fieldName
     * @param Closure $renderer callback
     * @param string|null $fieldId
     * @return DataGrid
     */
    public function addColumn($fieldName, $renderer, $fieldId = null) {
        // если это поле не для данных (иконки действий, например)
        // можно сгенерить рандомное имя поля, т.к. оно не важно
        if ($fieldId === null) {
            $fieldId = md5($fieldName);
        } else {
            // Если поле указано явно, добавим сразу для него сортировку
            $this->sortingFields[] = $fieldId;
        }

        $this->fields[$fieldId] = $fieldName;
        $this->setRenderer($fieldId, $renderer);
        return $this;
    }

    /**
     * @param string $fieldId
     * @param LightMetaProperty $property
     * @return closure
     * @throws ClassNotFoundException
     */
    protected function getEditRenderer($fieldId, LightMetaProperty $property) {
        switch($property->getType()) {
            case 'integer':
            case 'float':
            case 'string':
                return function ($value) use ($fieldId, $property) {
                    if ($value instanceof Stringable) $value = $value->toString();
                    $value = htmlentities($value, ENT_COMPAT, 'UTF-8', false);
					if ($property->getType() == 'string' && !$property->getMax()) {
						return '<textarea rows="4" cols="50" name="'
							. $property->getName() . '">'
							. $value
							. '</textarea>';
                    } else {
						$styleWidth = $property->getType() == 'string' ? 250 : 80;
						$length = $property->getType() == 'string' ? $property->getMax() : 16;
						return '<input style="width:'.$styleWidth.'px" type="text" name="'. $fieldId
							.'" value="' . $value . '" length="' . $length . '" />';
                    }

                };

            case 'timestamp':
            case 'date':
                return function ($value) use ($fieldId, $property) {
					if ($value instanceof Date)
	                    $val = $value->toDate('-');
					else $val = '';

					return '<input type="text" name="'. $fieldId . '" value="' . $val . '"  />';
                };

            case 'boolean':
                return function ($value) use ($fieldId) {
                    return '<input type="checkbox" name="' . $fieldId . '"'
                        . ($value == true ? ' checked="checked"' : '') .'" />';
                };

            case 'enumeration':
                return function ($value) use ($fieldId, $property) {
                    $class = $property->getClassName();
                    if (!class_exists($class, true)) {
                        throw new ClassNotFoundException;
                    }
                    $list = $class::makeObjectList();
                    $html = '<select name="' . $fieldId . '">';
					if (!$property->isRequired()) {
						$html .= '<option value="">' . __('Нет') . '</option>';
					}
                    foreach ($list as $item) {
                        $checked = ($item == $value) ? ' selected="selected"' : '';
                        $html .= '<option value="' . $item->getId() . '"' . $checked . '>' . $item->getName() . '</option>';
                    }
                    $html .= '</select>';
                    return $html;
                };

            case 'integerIdentifier':
                return function ($value) use ($property) {
                    if ($value instanceof Identifiable) {
                        $value = $value->getId();
                    }
                    return $property->getClassName() . ' ID: ' . $value;
                };

            case 'identifierList':
                return function ($value) use ($property) {
                    //if (is_subclass_of($property->getClassName(), 'Enumeration')) {
                    //    return 'enum';
                    //} else {
                        return $property->getClassName();
                    //}
                };

            default:
                return function ($value) use ($property) {
                    // DEBUG
                    $props[] = 'name: ' . $property->getName();
                    $props[] = 'className: ' . $property->getClassName();
                    $props[] = 'type: ' . $property->getType();
                    $props[] = 'min: ' . $property->getMin();
                    $props[] = 'max: ' . $property->getMax();
                    $props[] = 'relation: ' . $property->getRelationId();
                    $props[] = 'fetch: ' . $property->getFetchStrategyId();
                    //$props[] = 'value: ' . $value;
                    return  implode(', ', $props);
                };
        }
    }

    /**
     * @param string $fieldId
     * @param LightMetaProperty $property
     * @return closure
     * @throws ClassNotFoundException
     */
    protected function getLazyViewRenderer($fieldId, LightMetaProperty $property) {
		// переменные для замыканий, т.к. они не биндятся к this
		$self = $this;
		$trueName = $this->trueName;
		$falseName = $this->falseName;

        switch($property->getType()) {

			// OneToOne
			case 'integerIdentifier':
			case 'scalarIdentifier': {
				if( $property->getClassName()=='InternationalString' ) {
					return function ($value) use ($property) {
							return $value->__toString();
					};
				} elseif( $property->isIdentifier() ) {
					return function ($value) use ($property) {
						return $value;
					};
				} elseif( is_subclass_of($property->getClassName(), 'Prototyped') ) {
					return function($value, $object) use ($self) {
						if ($self->hasParent($object)) {
							return get_class($value) . ' ID:' . $value->getId();
						} else {
							try {
								return
									'<b>' . get_class($value) . '</b><br>' .
									DataGrid::details()->setParent($self)->addRow($value)->ToString();
							} catch (Exception $e) {
								return $e->getMessage();
							}
						}
					};
				} else {
					return function ($value) {
						return 'Object is not prototyped!';
					};
				}
			}

			// OneToMany & ManyToMany
			case 'identifierList': {
				return function(UnifiedContainer $value, $object) use ($self) {
					if ($self->hasParent($object)) {
						return get_class($value) . ' count:' . $value->fetch()->getCount();
					} else {
						try {
							return DataGrid::table()->setParent($self)->addRows($value->fetch()->getList())->ToString();
						} catch (Exception $e) {
							return $e->getMessage();
						}
					}
				};
			}

			case 'boolean': {
				return function ($value) use ($trueName, $falseName) {
					return $value ? $trueName : $falseName;
				};
			}

			case 'set': {
				return function($value) use ($self) {
					try {
						return DataGrid::table()->setParent($self)->addRows($value)->ToString();
					} catch (Exception $e) {
						return $e->getMessage();
					}
				};
			}

            case 'integer': {
				return function ($value) {
					return $value;
				};
			}

            case 'float': {
				return function ($value) {
					return number_format($value, 2, ',', '');
				};
			}

            case 'string': {
				return function ($value) {
					return nl2br($value);
				};
			}

            case 'timestamp':
			case 'date': {
				return function ($value) {
					if( $value instanceof Timestamp ) {
						return $value->toFormatString('d-m-Y H:i:s');
					} elseif( $value instanceof Date ) {
						return $value->toFormatString('d-m-Y');
					} else {
						return '';
					}
				};
			}

            case 'enumeration': {
				return function ($value) {
					if( $value instanceof Enumeration ) {
						return $value->getName();
					} else {
						return '';
					}
				};
			}

            default:
                return function ($value) use ($property) {
                    // DEBUG
                    $props[] = 'name: ' . $property->getName();
                    $props[] = 'className: ' . $property->getClassName();
                    $props[] = 'type: ' . $property->getType();
                    $props[] = 'min: ' . $property->getMin();
                    $props[] = 'max: ' . $property->getMax();
                    $props[] = 'relation: ' . $property->getRelationId();
                    $props[] = 'fetch: ' . $property->getFetchStrategyId();
                    //$props[] = 'value: ' . $value;
                    return  implode(', ', $props);
                };
        }
    }

    /**
     * Находит подходящий рендерер в соответствии с типом значения
     * @param $value
     * @return Closure
     */
    protected function getViewRenderer($value) {
		// переменные для замыканий, т.к. они не биндятся к this
		$self = $this;
		$trueName = $this->trueName;
		$falseName = $this->falseName;

		if ($value instanceof InternationalString) {
			return function ($value) {
				return $value->__toString();
			};
		}
		// для прототипированного объекта можно построить
		// вложенную табличку. Важно запомнить родителя,
		// чтобы избежать бесконечной рекурсии
		if ($value instanceof Prototyped)
			return function($value, $object) use ($self) {
				if ($self->hasParent($object)) {
					return get_class($value) . ' ID:' . $value->getId();
				} else {
					try {
						return
							'<b>' . get_class($value) . '</b><br>' .
							DataGrid::details()->setParent($self)->addRow($value)->ToString();
					} catch (Exception $e) {
						return $e->getMessage();
					}
				}
			};


        // Это в основном для тех случаев, когда у объекта есть
        // OneToMany свойство. UnifiedContainer позволяет получить
        // список дочерних объектов
        if ($value instanceof UnifiedContainer)
			return function(UnifiedContainer $value, $object) use ($self) {
				if ($self->hasParent($object)) {
					return get_class($value) . ' count:' . $value->fetch()->getCount();
				} else {
					try {
						return DataGrid::table()->setParent($self)->addRows($value->fetch()->getList())->ToString();
					} catch (Exception $e) {
						return $e->getMessage();
					}
				}
			};


        // Булевы выведем для удобства словами "Да" или "Нет"
        if (is_bool($value))
			return function ($value) use ($trueName, $falseName) {
				return $value ? $trueName : $falseName;
			};


        // Встроенная табличка
        if (is_array($value))
			return function($value) use ($self) {
				try {
					return DataGrid::table()->setParent($self)->addRows($value)->ToString();
				} catch (Exception $e) {
					return $e->getMessage();
				}
			};


        // Заглушка для всех прочих объектов
        if (is_object($value))
			return function ($value) {
				if (is_null($value)) {
					return '';
				}
				if ($value instanceof Stringable)
					return $value->toString();
				if (method_exists($value, '__toString'))
					return (string)$value;
				return 'object('.get_class($value).')';
			};

		if (is_string($value))
			return function ($value) {
				return nl2br($value);
			};

        // прочие случаи
        return function($value) { return $value; };
    }

    /**
     * 'somePropertyName' => 'Some property name'
     * @param $camelCaseString
     * @return string
     */
    public static function beautifyFieldName($camelCaseString) {
        return ucfirst(
            preg_replace_callback(
                '/([a-z])([A-Z])/',
                function($x) {
                    return $x[1] . ' ' . strtolower($x[2]);
                },
                $camelCaseString
            )
        );
    }

    /**
     * @param DataGrid $parent
     * @return DataGrid
     */
    public function setParent(DataGrid $parent) {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Рекурсивная проверка
     * @param $object
     * @return bool
     */
    public function hasParent($object) {
        $dataGrid = $this;
        $nesting = 0;
        while ($dataGrid = $dataGrid->parent) {
            if ($nesting++ > self::$maxNesting)
                return true;
            foreach ($dataGrid->objects as $o) {
                if ($o == $object) {
                    return true;
                }
                if ($o instanceof Identifiable && $object instanceof Identifiable
                    && get_class($o) == get_class($object)
                    && $o->getId() == $object->getId()) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param $number
     * @return DataGrid
     */
    public function setMaxNesting($number) {
        self::$maxNesting = $number;
        return $this;
    }

    /**
     * @return int
     */
    public static function getMaxNesting() {
        return self::$maxNesting;
    }

    /**
     * Задает массив столбцов таблицы
     * @param array $fields array('fieldID' => 'Field name', ...)
     * @return DataGrid
     */
    public function setFields(array $fields) {
        $this->fields = $fields;

        /* При добавлении объекта, из него выгребаются все свойства,
         * но только его собственные, а зависимые объекты не подгружаются
         * Допустим, мы хотим добавить employee.user.name для отображении
         * имени менеджера, когда выводим инфу по рекламодателю. Тогда
         * следующий код сделает следующую цепочку вызовов:
         *   $advertiser->getEmployee()->getUser()->getName()
         * и заполнит соответствующие поля таблицы значениями.
         * */

        foreach ($this->fields as $fieldId => $fieldName) {
            if (strpos($fieldId, '.') !== false) {
                $path = explode('.', $fieldId);
                foreach ($this->rows as $rowId => $row) {
                    $object = $this->objects[$rowId];

                    $failed = false;
                    foreach ($path as $propertyName) {

						if ($object instanceof Prototyped) {
							try {
								$property = $object->proto()->getPropertyByName($propertyName);
								$getter = $property->getGetter();
								$object = $object->$getter();
								continue;
							} catch (MissingElementException $e) {
								// none
							}
						}

						if (is_object($object)) {
							try {
								// support non-prototyped getters
								$getter = 'get' . ucfirst($propertyName);
								Assert::methodExists($object, $getter);
								$object = $object->$getter();
								continue;
							} catch (WrongArgumentException $e) {
								// none
							}
						}

						$failed = true;
						break;
					}

                    if (!$failed) {
                        $this->setField($rowId, $fieldId, $object, $property);
                        $this->sortingFields[] = $fieldId;
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Исключает столбцы из таблицы
     * можно использовать маски, напр: unsetFields('webmaster.*');
     * @param $_ string, string, ... OR array
     * @return DataGrid
     */
    public function unsetFields($_) {
        if (is_array($_)) $fields = $_;
        else $fields = func_get_args();
        foreach($fields as $fieldId) {
            if (substr($fieldId, -1) == '*') {
                $prefix = substr($fieldId, 0, strlen($fieldId) - 1);
                foreach ($this->fields as $fieldId => $fieldName) {
                    if (substr($fieldId, 0, strlen($prefix)) == $prefix) {
                        unset($this->fields[$fieldId]);
                    }
                }
            } else {
                unset($this->fields[$fieldId]);
            }
        }
        return $this;
    }

    /**
     * @param $fieldId string
     * @param $callback Closure function($value, $rowObject) {}
     * @return DataGrid
     */
    public function setRenderer($fieldId, $callback) {
        $this->renderers[$fieldId] = $callback;
        return $this;
    }

    /**
     * Показать начальный тег
     * @return DataGrid
     */
    public function showHeadTag() {
        $this->showHeadTag = true;
        return $this;
    }

    /**
     * Скрыть начальный тег
     * @return DataGrid
     */
    public function hideHeadTag() {
        $this->showHeadTag = false;
        return $this;
    }

    /**
     * Показать названия колонок
     * @return DataGrid
     */
    public function showHeader() {
        $this->showHeader = true;
        return $this;
    }

    /**
     * Скрыть названия колонок
     * @return DataGrid
     */
    public function hideHeader() {
        $this->showHeader = false;
        return $this;
    }

    /**
     * @param array $fields
     */
    public function setSortingFields(array $fields) {
        $this->sortingFields = $fields;
        return $this;
    }

    /**
     * Исключает столбцы из списка столбцов для сортировки
     * @param $_ string, string, ... OR array
     * @return DataGrid
     */
    public function unsetSortingFields($_) {
        if (is_array($_)) $fields = $_;
        else $fields = func_get_args();
        foreach($fields as $fieldId) {
            foreach($this->sortingFields as $k=>$v) {
                if ($v == $fieldId) {
                    unset($this->sortingFields[$k]);
                }
            }
        }
        return $this;
    }

    /**
     * @return DataGrid
     */
    public function showSorting() {
        $this->showSorting = true;
        return $this;
    }

    /**
     * @return DataGrid
     */
    public function hideSorting() {
        $this->showSorting = false;
        return $this;
    }

    /**
     * @param string $fieldId
     * @param array $htmlOptions
     * @return DataGrid
     */
    public function setHeaderOptions($fieldId, $htmlOptions) {
        $this->fieldHtmlOptions[$fieldId] = $htmlOptions;
        return $this;
    }

    /**
     * @param array $htmlOptions
     * @return DataGrid
     */
    public function setFormOptions($htmlOptions) {
        $this->formHtmlOptions = $htmlOptions;
        return $this;
    }

	/**
	 * @param $formErrors
	 * @return DataGrid
	 */
	public function setForm(Form $form) {
		$this->form = $form;
		return $this;
	}
    
    /**
     *
     * @param array $rowAttrs
     * @return DataGrid 
     */
    public function setRowAttrs($rowAttrs) {
        $this->rowAttrs = $rowAttrs;
        return $this;
    } 

    /**
     * @return Model
     */
    protected function makeModel() {
        $data = array();
        
        if (empty($this->rowAttrs['class'])) {
            $this->rowAttrs['class'] = '';
        }
        
        // рендерим данные
        foreach ($this->rows as $rowId => $row) {
            $object = isset($this->objects[$rowId]) ? $this->objects[$rowId] : $this->rows[$rowId];
            foreach ($this->fields as $fieldId => $fieldName) {
				if( $object instanceof Prototyped ) {
					try {
						$field = PrototypeUtils::getValue($object, $fieldId);
//						$property = $object->proto()->getPropertyByName($fieldId);
//						$field = $object->{$property->getGetter()}();
					} catch( Exception $e ) {
						$field = null;
					}
				} elseif( isset($row[$fieldId]) ) {
					$field = $row[$fieldId];
				} else {
					$field = null;
				}

				if ($this->form instanceof Form	&& $this->form->exists($fieldId)) {
					if ($this->form->get($fieldId)->isImported())
						$field = $this->form->get($fieldId)->getValue();
					else if ($this->form->hasError($fieldId))
						$field = $this->form->get($fieldId)->getRawValue();
				}

				// если есть рендерер, прогоним значение через него
//				var_dump($this->renderers);
//				die();
                if (isset($this->renderers[$fieldId])) {
                    $callback = $this->renderers[$fieldId];
                    if ($this->renderers[$fieldId] instanceof Closure) {
                        $field = $callback($field, $object);
                    } else {
                        $field = call_user_func($callback, $field, $object);
                    }
                }
                $data[$rowId][$fieldId] = $field;
            }
            
            $attrs = array();
            
            foreach ($this->rowAttrs as $key => $value) {
                if ($value instanceof Closure) {
                    $value = $value($object);
                }
//                if ('class' == $key) {
//                    $value = ($rowId % 2 ? 'odd ' : 'even ') . $value;
//                }
                array_push($attrs, $key . '="' . $value . '"');
            }
            $data[$rowId]['attrs'] = implode(' ', $attrs);
        }

        // отрендерим аттрибуты html
        $htmlOptions = array();
        foreach ($this->fields as $fieldId => $fieldName) {
            if (isset($this->fieldHtmlOptions[$fieldId])) {
                $htmlOptions[$fieldId] = Html::attributes($this->fieldHtmlOptions[$fieldId]);
            } else {
                $htmlOptions[$fieldId] = '';
            }
        }

        // отрендерим аттрибуты формы
		$selfUrl = null;
		try {
			$controller = Application::me()->getRunningActionController();
			$selfUrl = $controller->getSelfUrl()->__toString();
		} catch (UnexpectedValueException $e) {
			$selfUrl = $_SERVER['REQUEST_URI'];
			$this->hideSorting();
		}
        $formOptions = Html::attributes(array_merge(
            array('action' => $selfUrl, 'method' => 'POST'),
            $this->formHtmlOptions
        ));

        $model = parent::makeModel()
            ->set('fields', $this->fields)
			->set('localizedFields', $this->localizedFields ? : array())
            ->set('data', $data)
            ->set('totalId', $this->totalId)
            ->set('htmlOptions', $htmlOptions)
            ->set('formOptions', $formOptions)
			->set('showHeadTag', $this->showHeadTag)
            ->set('showHeader', $this->showHeader)
            ->set('showSorting', $this->showSorting)
            ->set('sortingFields', $this->sortingFields)
            ->set('isEditor', $this->isEditor)
      		->set('form', $this->form);
        ;

        return $model;
    }
}
