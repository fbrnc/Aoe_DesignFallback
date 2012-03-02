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
		return parent::_fallback($file, $params, $this->getFallbackScheme());
	}

	/**
	 * Get fallback scheme from configuration
	 *
	 * @return array
	 */
	protected function getFallbackScheme() {
		$configuration = Mage::getStoreConfig('design/fallback/fallback', $this->getStore());
		$fallbackScheme = array();
		foreach (explode("\n", $configuration) as $line) {
			if (strpos($line, ':') === false) {
				Mage::throwException('Line must contain package and theme separated by ":"');
			}
			list($package, $theme) = explode(':', $line);
			$fallbackScheme[] = array(
				'_package' => $this->resolveConfiguration($package),
				'_theme' => $this->resolveConfiguration($theme),
			);
		}
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
		if ($value[0] == '{' && $value[strlen($value)-1] == '}') {
			$value = substr($value, 1, -1);
			$value = Mage::getStoreConfig($value, $this->getStore());
		}
		return $value;
	}

}
