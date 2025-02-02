<?php
/**
 * @package     HTML_QuickForm
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 * @copyright   2001-2011 The PHP Group
 * @license     http://www.php.net/license/3_01.txt PHP License 3.01
 */

/**
 * Validates values using callback functions or methods
 *
 * @package     HTML_QuickForm
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 */
class HTML_QuickForm_Rule_Callback extends HTML_QuickForm_Rule
{
    /**
     * Array of callbacks
     *
     * Array is in the format:
     * $_data['rulename'] = array('functionname', 'classname');
     * If the callback is not a method, then the class name is not set.
     *
     * @var     array
     * @access  private
     */
    public $_data = [];

   /**
    * Whether to use BC mode for specific rules
    *
    * Previous versions of QF passed element's name as a first parameter
    * to validation functions, but not to validation methods. This behaviour
    * is emulated if you are using 'function' as rule type when registering.
    *
    * @var array
    * @access private
    */
    public $_BCMode = [];

    /**
     * Validates a value using a callback
     *
     * @param     string    $value      Value to be checked
     * @param     mixed     $options    Options for callback
     * @return    boolean   true if value is valid
     */
    public function validate($value, $options = null)
    {
        if (isset($this->_data[$this->name])) {
            $callback = $this->_data[$this->name];
            if (isset($callback[1])) {
                return call_user_func([$callback[1], $callback[0]], $value, $options);
            } elseif ($this->_BCMode[$this->name]) {
                return $callback[0]('', $value, $options);
            } else {
                return $callback[0]($value, $options);
            }
        } elseif (is_callable($options)) {
            return call_user_func($options, $value);
        } else {
            return true;
        }
    }

    /**
     * Adds new callbacks to the callbacks list
     *
     * @param     string    $name       Name of rule
     * @param     string    $callback   Name of function or method
     * @param     string    $class      Name of class containing the method
     * @param     bool      $BCMode     Backwards compatibility mode
     */
    public function addData($name, $callback, $class = null, $BCMode = false)
    {
        if (!empty($class)) {
            $this->_data[$name] = [$callback, $class];
        } else {
            $this->_data[$name] = [$callback];
        }
        $this->_BCMode[$name] = $BCMode;
    }


    function getValidationScript($options = null)
    {
        if (isset($this->_data[$this->name])) {
            $callback = $this->_data[$this->name][0];
            $params   = ($this->_BCMode[$this->name]? "'', {jsVar}": '{jsVar}') .
                        (isset($options)? ", '{$options}'": '');
        } else {
            $callback = is_array($options)? $options[1]: $options;
            $params   = '{jsVar}';
        }
        return ['', "{jsVar} != '' && !{$callback}({$params})"];
    }
}
