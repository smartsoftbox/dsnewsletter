<?php
/**
 * 2020 Smart Soft.
 *
 * @author    Marcin Kubiak
 * @copyright Smart Soft
 * @license   Commercial License
 *  International Registered Trademark & Property of Smart Soft
 */

abstract class BaseEnum
{
    private static $constCacheArray = null;

    protected static function getConstants()
    {
        if (self::$constCacheArray == null) {
            self::$constCacheArray = [];
        }
        $calledClass = get_called_class();
        if (!array_key_exists($calledClass, self::$constCacheArray)) {
            $reflect = new ReflectionClass($calledClass);
            self::$constCacheArray[$calledClass] = $reflect->getConstants();
        }
        return self::$constCacheArray[$calledClass];
    }

    public static function getConstantsForSelect()
    {
        $result = array();
        $enums = self::getconstants();
        foreach ($enums as $key => $enum) {
            $result[$key]['id'] = $enum;
            $result[$key]['name'] = self::getlabel($key);
        }
        return $result;
    }

    public static function getLabelByValue($value)
    {
        $enums = self::getconstants();
        foreach ($enums as $key => $enum) {
            if ((int)$enum === (int)$value) {
                return self::getlabel($key);
            }
        }
    }

    public static function getNameByValue($value)
    {
        $enums = self::getconstants();
        foreach ($enums as $key => $enum) {
            if ($enum === $value) {
                return $key;
            }
        }
    }

    public static function isValidName($name, $strict = false)
    {
        $constants = self::getConstants();

        if ($strict) {
            return array_key_exists($name, $constants);
        }

        $keys = array_map('strtolower', array_keys($constants));
        return in_array(strtolower($name), $keys);
    }

    public static function isValidValue($value, $strict = true)
    {
        $values = array_values(self::getConstants());
        return in_array($value, $values, $strict);
    }

    /**
     * @param $key
     * @return string
     */
    private static function getLabel($key)
    {
        return strtolower(str_replace('_', ' ', $key));
    }
}
