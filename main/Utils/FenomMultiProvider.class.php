<?php
/**
 * Компоновщик провайдеров для Fenom
 * @author Михаил Кулаковский <m@klkvsk.ru>
 * @date 09.07.13
 */
use Fenom\ProviderInterface;

class FenomMultiProvider implements ProviderInterface {

	/** @var ProviderInterface[] */
	protected $providers = array();

	public function addProvider(ProviderInterface $provider) {
		$this->providers []= $provider;
		return $this;
	}

	protected function __construct() {}
	public static function create() {
		return new static;
	}

	/**
	 * @param string $tpl
	 * @return bool
	 */
	public function templateExists($tpl) {
		foreach ($this->providers as $provider) {
			if ($provider->templateExists($tpl)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @param string $tpl
	 * @param int    $time
	 * @throws RuntimeException
	 * @return string
	 */
	public function getSource($tpl, &$time) {
		foreach ($this->providers as $provider) {
			if ($provider->templateExists($tpl)) {
				return $provider->getSource($tpl, $time);
			}
		}
		throw new RuntimeException('can not find "' . $tpl . '" anywhere');
	}

	/**
	 * @param string $tpl
	 * @return int
	 */
	public function getLastModified($tpl) {
		foreach ($this->providers as $provider) {
			if ($provider->templateExists($tpl)) {
				return $provider->getLastModified($tpl);
			}
		}
		throw new RuntimeException('can not find "' . $tpl . '" anywhere');
	}

	/**
	 * Verify templates by change time
	 *
	 * @param array $templates [template_name => modified, ...] By conversation you may trust the template's name
	 * @return bool
	 */
	public function verify(array $templates) {
		// not optimal, but works
		foreach ($templates as $template_name => $modified) {
			$verified = false;
			foreach ($this->providers as $provider) {
				$verified = $provider->verify(array($template_name => $modified));
				if ($verified) break;
			}
			if (!$verified) {
				return false;
			}
		}
		return true;
	}

	/**
	 * @return array
	 */
	public function getList() {
		$list = array();
		foreach ($this->providers as $provider) {
			$list = array_merge($list, $provider->getList());
		}
		return $list;
	}

}