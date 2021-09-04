<?php

namespace Magedelight\OneStepCheckout\Model;

class JsLayoutAccessData
{
    /**
     * @var array
     */
    protected $data;

    /**
     * JsLayoutAccessData constructor.
     * @param array|null $data
     */
    public function __construct(
        array $data = null
    ) {
        $this->data = $data ?: [];
    }

    /**
     * @param $key
     * @param null $value
     */
    public function setArray($key, $value = null)
    {
        if (strlen($key) == 0) {
            throw new \RuntimeException(
                "Key cannot be an empty string"
            );
        }

        $currentValue =& $this->data;
        $keyPath = explode('.', $key);

        if (count($keyPath) == 1) {
            $currentValue[$key] = $value;

            return;
        }

        $endKey = array_pop($keyPath);
        for ($i = 0; $i < count($keyPath); $i++) {
            $currentKey =& $keyPath[$i];
            if (!isset($currentValue[$currentKey])) {
                $currentValue[$currentKey] = [];
            }
            if (!is_array($currentValue[$currentKey])) {
                throw new \RuntimeException(
                    "Key path at $currentKey of $key cannot be indexed into (is not an array)"
                );
            }
            $currentValue =& $currentValue[$currentKey];
        }
        $currentValue[$endKey] = $value;
    }

    /**
     * @param $key
     */
    public function removeArray($key)
    {
        if (strlen($key) == 0) {
            throw new \RuntimeException("Key cannot be an empty string");
        }

        $currentValue =& $this->data;
        $keyPath = explode('.', $key);

        if (count($keyPath) == 1) {
            unset($currentValue[$key]);

            return;
        }

        $endKey = array_pop($keyPath);
        for ($i = 0; $i < count($keyPath); $i++) {
            $currentKey =& $keyPath[$i];
            if (!isset($currentValue[$currentKey])) {
                return;
            }
            $currentValue =& $currentValue[$currentKey];
        }
        unset($currentValue[$endKey]);
    }

    /**
     * @param $key
     * @param null $default
     * @return array|mixed|null
     */
    public function getArray($key, $default = null)
    {
        $currentValue = $this->data;
        $keyPath = explode('.', $key);

        for ($i = 0; $i < count($keyPath); $i++) {
            $currentKey = $keyPath[$i];
            if (!isset($currentValue[$currentKey])) {
                return $default;
            }
            if (!is_array($currentValue)) {
                return $default;
            }
            $currentValue = $currentValue[$currentKey];
        }

        return $currentValue === null ? $default : $currentValue;
    }

    /**
     * @param $key
     * @return bool
     */
    public function hasArray($key)
    {
        $currentValue = &$this->data;
        $keyPath = explode('.', $key);

        for ($i = 0; $i < count($keyPath); $i++) {
            $currentKey = $keyPath[$i];
            if (!is_array($currentValue) ||
                !array_key_exists($currentKey, $currentValue)
            ) {
                return false;
            }
            $currentValue = &$currentValue[$currentKey];
        }

        return true;
    }

    /**
     * @return array
     */
    public function exportArray()
    {
        return $this->data;
    }
}
