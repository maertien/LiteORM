<?php

/**
 * LiteORMValidationHelper
 */
class LiteORMValidationHelper {

	/**
	 * Validate entity type
	 * @param string $entityType Entity type
	 * @return bool True if entity type is valid
	 * @throws Exception
	 */
	public static function isEntityTypeValid($entityType) {

		if (class_exists($entityType) !== true) {

			throw new LiteORMException("Entity type " . $entityType . " is invalid");
		}

		return true;
	}
}
