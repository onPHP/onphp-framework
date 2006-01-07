<?php
/***************************************************************************
*   Copyright (C) 2004-2005 by Sergey S. Sergeev                          *
*   serge@rdw.ru                                                          *
***************************************************************************/
/* $Id$ */

/**
 * Будет генерироваться при обращении к несуществующему методу
 */
class NotExistMethodException extends BaseException {/*_*/}

/**
 * Будет генерироваться при обращении к несуществующему свойству 
 */
class NotExistPropertyException extends BaseException {/*_*/}

/**
 * Будет генерироваться при обращении к несуществующему ключу 
 */
class NotExistKeyException extends BaseException {/*_*/}


/**
 * Абстрактынй класс для работы с set и get методами. Cм. примеры в garbage (репозиторий учёбы.ру).
 * Бизнес классы удобно наследовать от него.
 * 
 * Основные свойства:
 * - для того чтобы создать класс c get и set методами, достаточно унаследоваться от 
 *   класса BaseBusiness. В конструкторе дочернего класса вызвать метод extendsAllowedKeys(), 
 *   аргументом которого будет являться массив ключей, расширящий свойства родительского класса;  
 * - поддерживается прямое обращение к protected свойствам: $this->property_name = Ваше значение;
 * - cвойства и методы можно переопределять;
 * - поддерживается обращение к значению свойства через ключ массива 
 *   (см.методы setKeyValue,  getKeyValue)
 * - Названия имён методов чувствительны к регистру.
 * 
 * ЗАМЕЧАНИЯ:
 * - Объект не может быть сохранён в сессию. Надо использовать методы importVars exportVars.
 * 
 * @todo 
 * Запретить в дочерних классах возможность создания методов с одинаковыми именами, 
 * отличающимися регистром. Предположительно использовать ReflectionClass для этих целей.
 */
abstract class BaseBusiness {
	/**
	 * Ассоциативный массив значений свойств объекта
	 *
	 * @var array
	 */
	private $vars = array();

	/**
	 * Хранит информацию о допустимых ключах объекта
	 *
	 * @var array
	 */
	private $allowed_keys = array();

	public function __construct($init_array = array())
	{
		if (!empty($init_array)){
			$this->extendsAllowedKeys($init_array);
		}
	}

	public function __destruct(){
		$this->vars = array();
		$this->allowed_keys = array();
	}

	/**
	 * Установка ключей, название которых будут преобразовываться 
	 * в свойства и методы класса.
	 * 
	 * Например, ключ institute_status_id, будет преобразован в protected свойство instituteStatusId,
	 * метод get для данного свойства получит название getInstituteStatusId(),
	 * метод set для данного свойства получит название setInstituteStatusId()
	 *
	 * @param array $array
	 */
	public function setAllowedKeys($array){
		$this->allowed_keys = $array;
		return $this;
	}
	
	/**
	 * Список зарегистрированных (допустимых) ключей
	 *
	 * @return array
	 */
	public function getAllowedKeys(){
		return $this->allowed_keys;
	}
	
	/**
	 * Добавляет ключи
	 *
	 * @param array $array
	 * @return object
	 */
	public function addAllowedKeys($array){
		$this->allowed_keys = array_merge($this->allowed_keys, $array);
		return $this;
	}
	
	/**
	 * Расширяет набор зарегистрированных ключей. 
	 * Является враппером для метода addAllowedKeys
	 *
	 * @param array $array
	 * @return object
	 */
	public function extendsAllowedKeys($array)
	{
		$this->addAllowedKeys($array);
		return $this;
	}

	public function resetAllowedKeys(){
		return $this->allowed_keys = array();
	}	
		
	/**
	 * Список доступных свойств класса
	 *
	 * @return array
	 */
	public function getAllowedProperty(){
		$array_properties = array();
		foreach ($this->allowed_keys as $keyname)		
			$array_properties[] = $this->keyToProperty($keyname);		
		return $array_properties;
	}
	
	/**
	 * Список доступных методов класса
	 *
	 * @return array
	 */
	public function getAllowedMethods(){
		$array_methods = array();
		foreach ($this->allowed_keys as $keyname)
		{	
			$nameFromKey = $this->keyToMethod($keyname);		
			$array_methods[] = 'set'.$nameFromKey;
			$array_methods[] = 'get'.$nameFromKey;
		}
		return $array_methods;
	}

	public function __call($name_method, $args){		
		$preffix = substr($name_method, 0, 3);
		$method = substr($name_method, 3);
		$property = strtolower($method{0}).substr($method, 1);
		switch ($preffix){
			//получение свойства 
			case 'get':
			if (!$this->checkExistMethod($name_method)){
				throw new NotExistMethodException('Метод с именем "'.$name_method.'" не существует в классе "'.get_class($this).'"');
			}
					
			if (isset($this->vars[$property])){
				return $this->vars[$property];
			} else{
				return null;
			}
			
			/* NOTREACHED */
			
			//установка свойства
			case 'set':
			if (!$this->checkExistMethod($name_method)){
				throw new NotExistMethodException('Метод с именем "'.$name_method.'" не существует в классе "'.get_class($this).'"');
			}
			if (sizeof($args)>1){
				throw new WrongArgumentException('Метод "'.$name_method.'" класса "'.get_class($this).'" должен иметь только один аргумент!');
			}
			$this->vars[$property] = &$args['0'];
			return $this;
			
			/* NOTREACHED */
			
			//ошибка
			default:
				//if ( $name_method != '__wakeup' )
				throw new NotExistMethodException('Метод с именем "'.$name_method.'" не существует в классе "'.get_class($this).'"');
			break;
		}
	}
	
	/**
	 * Проверка свойства на существование
	 *
	 * @param string $name_property
	 * @return boolean
	 */
	public function checkExistProperty($name_property){		
		if (in_array($name_property, $this->getAllowedProperty())){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * Проверка метода на существование	 
	 * 
	 * @param string $name_method - название метода c префиксов 'get' или 'set'
	 * @return boolean
	 */
	public function checkExistMethod($name_method){		
		if (in_array($name_method, $this->getAllowedMethods())){
			return true;
		}else{
			return false;
		}

	}
	
	/**
	 * Установка значения свойства по ключу
	 *
	 * @param string $name - название ключа
	 * @param mixed $value - значение
	 */
	public function setKeyValue($name, $value){
		if (in_array($name, $this->allowed_keys)){
			$this->vars[$this->keyToProperty($name)] = &$value;
		} else {
			throw new NotExistKeyException('Ключа с именем "'.$name.'" не существует'); 
		}
		
		return $this;
	}
	
	/**
	 * Получение значения свойства по ключу
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function &getKeyValue($name){
		if (in_array($name, $this->allowed_keys)){
			return $this->vars[$this->keyToProperty($name)];
		} else{
			throw new NotExistKeyException('Ключа с именем "'.$name.'" не существует'); 
		}
	}
	
	/**
	 * Экспорт переменных
	 *
	 * @return array
	 */
	public function exportVars(){
		return $this->vars;
	}
	
	/**
	 * Импорт переменных
	 *
	 * @param array $array
	 * @return object
	 */
	public function importVars($array){
		if (is_array($array)) {
			foreach ($array as $key=>$value){
				if ($this->checkExistProperty($key)){
					$this->vars[$key] = $value;					
				}
			}
		} else {
			throw new WrongArgumentException('Ошибка при импорте переменных');
		}
		return $this;		
	}
	
	
	/************* Protected Methods ***************/
	
	/**
	 * Установка свойства
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	protected function __set($name, $value){
		if (!$this->checkExistProperty($name)){
			throw new NotExistPropertyException('Protected cвойство с именем "'.$name.'" не существует в классе "'.get_class($this).'"');
		}
		$this->vars[$name] = &$value;
	}

	/**
	 * Получение значения свойства
	 *
	 * @param string $name
	 * @return mixed
	 */
	protected function __get($name){
		if (!$this->checkExistProperty($name)){
			throw new NotExistPropertyException('Protected cвойство с именем "'.$name.'" не существует в классе "'.get_class($this).'"');
		}

		if (isset($this->vars[$name])){
			return $this->vars[$name];
		} else{
			return null;
		}
	}
	
	/************* Private Methods ***************/
	
	/**
	 * Преобразование названия ключа в название свойства
	 *
	 * @param string $keyname - название ключа
	 * @return string
	 */
	private function keyToProperty($keyname){
		$partsOfName = explode('_', $keyname);
		$nameFromKey = '';
		foreach ($partsOfName as $key=>$part) {
			if ($key == 0)	{
				$nameFromKey.=strtolower($part);
			} else {
				$nameFromKey.=ucfirst(strtolower($part));
			}
		}
		return $nameFromKey;
	}
	
	/**
	 * Преобразование названия ключа в название метода без префикса 'set', 'get'
	 *
	 * @param string $keyname
	 * @return string
	 */
	private function keyToMethod($keyname){
		$partsOfName = explode('_', $keyname);
		$nameFromKey = '';
		foreach ($partsOfName as $part) {
			$nameFromKey.=ucfirst(strtolower($part));
		}
		return $nameFromKey;
	}
}
?>