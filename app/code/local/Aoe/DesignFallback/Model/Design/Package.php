<?php

class Aoe_DesignFallback_Model_Design_Package extends Mage_Core_Model_Design_Package {


	/**
	 * Check for files existence by specified scheme
	 *
	 * @param string $file
	 * @param array &$params
	 * @param array $fallbackScheme
	 * @return string
	 */
	protected function _fallback($file, array &$params, array $fallbackScheme = array(array())) {
        /** @var $store Mage_Core_Model_Store */
        $store = $params['_store'];

        if ($store->isAdmin())
        {
            return parent::_fallback($file, $params, $fallbackScheme);
        } else {
            return parent::_fallback($file, $params, $this->getFallbackScheme($params));
        }
	}

	/**
	 * Get fallback scheme from configuration
	 *
	 * @param array $defaults (optional). Needed for resolving default package and theme for duplicates eliminiation
	 * @return array
	 */
	protected function getFallbackScheme(array $defaults=array()) {
		$configuration = Mage::getStoreConfig('design/fallback/fallback', $this->getStore());
		$fallbackScheme = array();
		foreach (explode("\n", $configuration) as $line) {
			if (strpos($line, ':') === false) {
				Mage::throwException('Line must contain package and theme separated by ":"');
			}
			list($packageName, $themeName) = explode(':', $line);

			$packageName = $this->resolveConfiguration($packageName);
			if (!empty($packageName)) { // empty values will be evaluated to current package ...
				if (!$this->designPackageExists($packageName, $this->getArea())) {
					// Mage::log(sprintf('Could not find package "%s". Using "%s" instead.', $packageName, Mage_Core_Model_Design_Package::DEFAULT_PACKAGE));
					$packageName = Mage_Core_Model_Design_Package::DEFAULT_PACKAGE;
				}
			} else {
				$packageName = $defaults['_package'];
			}

			$themeName = $this->resolveConfiguration($themeName);
			if (empty($themeName)) {
				$themeName = $defaults['_theme'];
			}

			$params = array(
				'_package' => $packageName,
				'_theme' => $themeName,
			);

			// avoid exact duplicates that are neighbours
			if ($params !== end($fallbackScheme)) {
				$fallbackScheme[] = $params;
			}
		}

		/*
		$debug = array();
		foreach ($fallbackScheme as $level) { $debug[] = implode('/', $level); }
		Mage::log($debug);
		*/

		return $fallbackScheme;
	}

	/**
	 * Resolve configuration.
	 * Values wrapped in {...} will be looked up in configuration.
	 * Example: {design/package/name}
	 *
	 * @param $value
	 * @return string
	 */
	protected function resolveConfiguration($value) {
		$value = trim($value);
		if (strtolower($value == '[current]')) {
			// empty value will be in ->updateParamDefaults().
			// to the current package and theme taking type-specific themes and design changed (System -> Design) into account
			$value = NULL;
		} elseif ($value[0] == '{' && $value[strlen($value)-1] == '}') {
			$value = substr($value, 1, -1);
			$value = Mage::getStoreConfig($value, $this->getStore());
		}
		return $value;
	}

}
