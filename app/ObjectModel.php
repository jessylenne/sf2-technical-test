<?php

abstract class ObjectModel {
    /**
     * List of field types
     */
    const TYPE_INT = 1;
    const TYPE_BOOL = 2;
    const TYPE_STRING = 3;
    const TYPE_FLOAT = 4;
    const TYPE_DATE = 5;
    const TYPE_HTML = 6;
    const TYPE_NOTHING = 7;

    /**
     * List of data to format
     */
    const FORMAT_COMMON = 1;
    const FORMAT_LANG = 2;
    const FORMAT_SHOP = 3;

    /**
     * List of association types
     */
    const HAS_ONE = 1;
    const HAS_MANY = 2;

    /** @var integer Object id */
    public $id;

    /**
     * @deprecated 1.5.0 This property shouldn't be overloaded anymore in class, use static $definition['table'] property instead
     */
    protected $table;

    /**
     * @deprecated 1.5.0 This property shouldn't be overloaded anymore in class, use static $definition['primary'] property instead
     */
    protected $identifier;

    /**
     * @deprecated 1.5.0 This property shouldn't be overloaded anymore in class, use static $definition['fields'] property instead
     */
    protected $fieldsRequired = array();

    /**
     * @deprecated 1.5.0 This property shouldn't be overloaded anymore in class, use static $definition['fields'] property instead
     */
    protected $fieldsSize = array();

    /**
     * @deprecated 1.5.0 This property shouldn't be overloaded anymore in class, use static $definition['fields'] property instead
     */
    protected $fieldsValidate = array();

    /**
     * @var array Contain object definition
     * @since 1.5.0
     */
    public static $definition = array();

    /**
     * @var array Contain current object definition
     */
    protected $def;

    /**
     * @var array List of specific fields to update (all fields if null)
     */
    protected $update_fields = null;

    /**
     * @var Db An instance of the db in order to avoid calling Db::getInstance() thousands of time
     */
    protected static $db = false;

    /**
     * Returns object validation rules (fields validity)
     *
     * @param string $class Child class name for static use (optional)
     * @return array Validation rules (fields validity)
     */
    public static function getValidationRules($class = __CLASS__) {
        $object = new $class();
        return array(
            'required' => $object->fieldsRequired,
            'size' => $object->fieldsSize,
            'validate' => $object->fieldsValidate,
            'requiredLang' => $object->fieldsRequiredLang,
            'sizeLang' => $object->fieldsSizeLang,
            'validateLang' => $object->fieldsValidateLang,
        );
    }

    private $_db;

    /**
     * Build object
     *
     * @param int $id Existing object id in order to load object (optional)
     * @param null $db
     * @throws Exception
     */
    public function __construct($id = null, $db = null) {
        $id_lang = null;
        $id_shop = null;

        if (!ObjectModel::$db)
            ObjectModel::$db = Db::getInstance();

        $this->_db = $db ? $db : ObjectModel::$db;

        $this->def = ObjectModel::getDefinition($this);

        if (!Validate::isTableOrIdentifier($this->def['primary']) || !Validate::isTableOrIdentifier($this->def['table']))
            throw new Exception('Identifier or table format not valid for class ' . get_class($this));

        if ($id) {
            // Load object from database if object id is present
            $cache_id = 'objectmodel_' . $this->def['classname'] . '_' . (int) $id . '_' . $this->getDb()->getDatabaseName();
            if (1 || !Cache::isStored($cache_id)) {
                $sql = new DbQuery();
                $sql->from($this->def['table'], 'a');
                $sql->where('a.' . $this->def['primary'] . ' = ' . (int) $id);

                // Get shop informations
                if ($object_datas = $this->getDb()->getRow($sql)) {
                    Cache::store($cache_id, $object_datas);
                }
            } else
                $object_datas = Cache::retrieve($cache_id);

            if ($object_datas) {
                $this->id = (int) $id;
                foreach ($object_datas as $key => $value)
                    if (array_key_exists($key, $this))
                        $this->{$key} = $value;
            }
        }
    }

    public function getDb() {
        if (!is_object($this->_db))
            $this->_db = Db::getInstance();

        return $this->_db;
    }

    /**
     * Get object definition
     *
     * @param string $class Name of object
     * @param string $field Name of field if we want the definition of one field only
     * @return array
     */
    public static function getDefinition($class, $field = null) {
        if (is_object($class))
            $class = get_class($class);

        if ($field === null)
            $cache_id = 'objectmodel_def_' . $class;

        if ($field !== null || !Cache::isStored($cache_id)) {
            $reflection = new ReflectionClass($class);
            $definition = $reflection->getStaticPropertyValue('definition');

            $definition['classname'] = $class;

            if ($field)
                return isset($definition['fields'][$field]) ? $definition['fields'][$field] : null;

            Cache::store($cache_id, $definition);
            return $definition;
        }

        return Cache::retrieve($cache_id);
    }

    /**
     * Prepare fields for ObjectModel class (add, update)
     * All fields are verified (pSQL, intval...)
     *
     * @return array All object fields
     */
    public function getFields() {
        $this->validateFields();
        $fields = $this->formatFields(self::FORMAT_COMMON);

        // Ensure that we get something to insert
        if (!$fields && isset($this->id) && Validate::isUnsignedId($this->id))
            $fields[$this->def['primary']] = $this->id;
        return $fields;
    }

    /**
     * @since 1.5.0
     * @param int $type FORMAT_COMMON or FORMAT_LANG or FORMAT_SHOP
     * @param int $id_lang If this parameter is given, only take lang fields
     * @return array
     */
    protected function formatFields($type, $id_lang = null) {
        $fields = array();

        // Set primary key in fields
        if (isset($this->id))
            $fields[$this->def['primary']] = $this->id;

        foreach ($this->def['fields'] as $field => $data) {
            $value = $this->$field;
            $fields[$field] = ObjectModel::formatValue($value, $data['type']);
        }

        return $fields;
    }

    /**
     * Format a data
     *
     * @param mixed $value
     * @param int $type
     * @param bool $with_quotes
     * @return mixed|string
     */
    public static function formatValue($value, $type, $with_quotes = false) {
        switch ($type) {
            case self::TYPE_INT :
                return (int) $value;

            case self::TYPE_BOOL :
                return (int) $value;

            case self::TYPE_FLOAT :
                return (float) str_replace(',', '.', $value);

            case self::TYPE_DATE :
                if (!$value)
                    return '0000-00-00';

                if ($with_quotes)
                    return '\'' . pSQL($value) . '\'';
                return pSQL($value);

            case self::TYPE_HTML :
                if ($with_quotes)
                    return '\'' . pSQL($value, true) . '\'';
                return pSQL($value, true);

            case self::TYPE_NOTHING :
                return $value;

            case self::TYPE_STRING :
            default :
                if ($with_quotes)
                    return '\'' . pSQL($value) . '\'';
                return pSQL($value);
        }
    }

    /**
     * Save current object to database (add or update)
     *
     * @param bool $null_values
     * @param bool $autodate
     * @return boolean Insertion result
     */
    public function save($null_values = false, $autodate = true, $params = array()) {
        return (int) $this->id > 0 ? $this->update($null_values, $params) : $this->add($autodate, $null_values);
    }

    /**
     * Add current object to database
     *
     * @param bool $null_values
     * @return boolean Insertion result
     */
    public function add($null_values = false) {
        if (!ObjectModel::$db)
            ObjectModel::$db = Db::getInstance();

        // Database insertion
        if (isset($this->id) && !Tools::getValue('forceIDs'))
            unset($this->id);

        if(property_exists($this, 'date_add'))
            $this->date_add = date('Y-m-d H:i:s');

        if (!$result = $this->getDb()->insert($this->def['table'], $this->getFields(), $null_values))
            return false;

        // Get object id in database
        $this->id = $this->getDb()->Insert_ID();


        if (!$result)
            return false;

        return $result;
    }

    /**
     * Update current object to database
     *
     * @param bool $null_values
     * @param array $params
     * @return boolean Update result
     */
    public function update($null_values = false, $params = array()) {
        $this->clearCache();

        // Database update
        if (!$result = $this->getDb()->update($this->def['table'], $this->getFields(), '`' . pSQL($this->def['primary']) . '` = ' . (int) $this->id, 0, $null_values))
            return false;

        return $result;
    }

    /**
     * Delete current object from database
     *
     * @return boolean Deletion result
     */
    public function delete() {
        if (!ObjectModel::$db)
            ObjectModel::$db = Db::getInstance();

        if (!$this->_db)
            $this->_db = ObjectModel::$db;


        $this->clearCache();

        $result = $this->_db->delete($this->def['table'], '`' . pSQL($this->def['primary']) . '` = ' . (int) $this->id);

        if (!$result)
            return false;

        $this->id = null;

        return $result;
    }

    /**
     * Check for fields validity before database interaction
     *
     * @param bool $die
     * @param bool $error_return
     * @return bool|string
     * @throws Exception
     */
    public function validateFields($die = true, $error_return = false) {
        foreach ($this->def['fields'] as $field => $data) {
            if (is_array($this->update_fields) && empty($this->update_fields[$field]))
                continue;

            $message = $this->validateField($field, $this->$field);
            if ($message !== true) {
                if ($die)
                    throw new Exception($message);
                return $error_return ? $message : false;
            }
        }

        return true;
    }

    /**
     * Validate a single field
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @param array $skip
     * @param bool $human_errors
     * @return bool|string
     * @throws Exception
     */
    public function validateField($field, $value, $skip = array(), $human_errors = false) {
        $data = $this->def['fields'][$field];

        if (!in_array('required', $skip) && (!empty($data['required'])))
            if (Tools::isEmpty($value))
                if ($human_errors)
                    return sprintf(Tools::displayError('The %s field is required.'), $this->displayFieldName($field, get_class($this)));
                else
                    return 'Property ' . get_class($this) . '->' . $field . ' is empty';

        // Default value
        if (!$value && !empty($data['default'])) {
            $value = $data['default'];
            $this->$field = $value;
        }

        // Check field values
        if (!in_array('values', $skip) && !empty($data['values']) && is_array($data['values']) && !in_array($value, $data['values']))
            return 'Property ' . get_class($this) . '->' . $field . ' has bad value (allowed values are: ' . implode(', ', $data['values']) . ')';

        // Check field size
        if (!in_array('size', $skip) && !empty($data['size'])) {
            $size = $data['size'];
            if (!is_array($data['size']))
                $size = array('min' => 0, 'max' => $data['size']);

            $length = Tools::strlen($value);
            if ($length < $size['min'] || $length > $size['max']) {
                if ($human_errors) {
                    return sprintf(Tools::displayError('The %1$s field is too long (%2$d chars max).'), $this->displayFieldName($field, get_class($this)), $size['max']);
                } else
                    return 'Property ' . get_class($this) . '->' . $field . ' length (' . $length . ') must be between ' . $size['min'] . ' and ' . $size['max'];
            }
        }

        // Check field validator
        if (!in_array('validate', $skip) && !empty($data['validate'])) {
            if (!method_exists('Validate', $data['validate']))
                throw new Exception('Validation function not found. ' . $data['validate']);

            if (!empty($value)) {
                $res = true;
                if (Tools::strtolower($data['validate']) == 'iscleanhtml') {

                    if (!call_user_func(array('Validate', $data['validate']), $value, 0))
                        $res = false;
                }
                else {
                    if (!call_user_func(array('Validate', $data['validate']), $value))
                        $res = false;
                }
                if (!$res) {
                    if ($human_errors)
                        return sprintf(Tools::displayError('Le champs %s est invalide.'), $this->displayFieldName($field, get_class($this)));
                    else
                        return 'La propriété ' . get_class($this) . '->' . $field . ' (' . $value . ') est invalide';
                }
            }
        }

        return true;
    }

    public static function displayFieldName($field, $class = __CLASS__, $htmlentities = true, Context $context = null) {
        global $_FIELDS;

        /* if ($_FIELDS === null && file_exists(_TRANSLATIONS_DIR_.Context::getContext()->language->iso_code.'/fields.php'))
          include_once(_TRANSLATIONS_DIR_.Context::getContext()->language->iso_code.'/fields.php'); */

        $key = $class . '_' . md5($field);
        return ((is_array($_FIELDS) && array_key_exists($key, $_FIELDS)) ? ($htmlentities ? htmlentities($_FIELDS[$key], ENT_QUOTES, 'utf-8') : $_FIELDS[$key]) : $field);
    }

    public function validateController($htmlentities = true) {
        $errors = array();
        foreach ($this->def['fields'] as $field => $data) {
            if ($field != 'date_add' && $field != 'date_upd' && $field != 'date_delete') {
                $value = Tools::getValue($field, $this->{$field});

                // Checking for required fields
                if (isset($data['required']) && $data['required'] && empty($value) && $value !== '0')
                    if (!$this->id || ($field != 'passwd' && $field != 'password'))
                        if (!in_array($field, array('passwd', 'password', 'id_user_add', 'id_user_upd', 'id_user_delete', 'date_add', 'date_upd', 'date_delete')))
                            $errors[$field] = '<b>' . self::displayFieldName($field, get_class($this), $htmlentities) . '</b> ' . Tools::displayError('est requis (' . $value . ').');

                // Checking for maximum fields sizes
                if (isset($data['size']) && !empty($value) && Tools::strlen($value) > $data['size'])
                    $errors[$field] = sprintf(
                        Tools::displayError('%1$s is too long. Maximum length: %2$d'), self::displayFieldName($field, get_class($this), $htmlentities), $data['size']
                    );

                // Checking for fields validity
                // Hack for postcode required for country which does not have postcodes
                if (!empty($value) || $value === '0' || ($field == 'postcode' && $value == '0')) {
                    if (isset($data['validate']) && !Validate::$data['validate']($value) && (!empty($value) || (isset($data['required']) && $data['required'])))
                    {
                        $errors[$field] = '<b>' . self::displayFieldName($field, get_class($this), $htmlentities) . '</b> ' . Tools::displayError('est invalide.') . ' ("' . $value . '")';
                    }
                    else {
                        if (isset($data['copy_post']) && !$data['copy_post'])
                            continue;
                        if (($field == 'passwd') || $field == 'password') {
                            if ($value = Tools::getValue($field))
                                $this->{$field} = Tools::encrypt($value);
                        } else
                            $this->{$field} = $value;
                    }
                }
            }
        }

        return $errors;
    }

    public function clearCache($all = false) {
        if ($all)
            Cache::clean('objectmodel_' . $this->def['classname'] . '_*');
        elseif ($this->id)
            Cache::clean('objectmodel_' . $this->def['classname'] . '_' . (int) $this->id . '_*');
    }

    /**
     * Specify if an ObjectModel is already in database
     *
     * @param int $id_entity
     * @param string $table
     * @return boolean
     */
    public static function existsInDatabase($id_entity, $table) {
        $row = Db::getInstance()->getRow('
			SELECT `id_' . $table . '` as id
			FROM `' . _DB_PREFIX_ . $table . '` e
			WHERE e.`id_' . $table . '` = ' . (int) $id_entity
        );

        return isset($row['id']);
    }

    /**
     * Fill an object with given data. Data must be an array with this syntax: array(objProperty => value, objProperty2 => value, etc.)
     *
     * @since 1.5.0
     * @param array $data
     * @param int $id_lang
     */
    public function hydrate(array $data, $id_lang = null) {
        $this->id_lang = $id_lang;
        if (isset($data[$this->def['primary']]))
            $this->id = $data[$this->def['primary']];
        foreach ($data as $key => $value)
            if (array_key_exists($key, $this))
                $this->$key = $value;
    }

    /**
     * Fill (hydrate) a list of objects in order to get a collection of these objects
     *
     * @param string $class Class of objects to hydrate
     * @param array $datas List of data (multi-dimensional array)
     * @param int $id_lang
     * @return array
     * @throws Exception
     */
    public static function hydrateCollection($class, array $datas, $id_lang = null) {
        if (!class_exists($class))
            throw new Exception("Class '$class' not found");

        $collection = array();
        $rows = array();
        if ($datas) {
            $definition = ObjectModel::getDefinition($class);
            if (!array_key_exists($definition['primary'], $datas[0]))
                throw new Exception("Identifier '{$definition['primary']}' not found for class '$class'");

            foreach ($datas as $row) {
                // Get object common properties
                $id = $row[$definition['primary']];
                if (!isset($rows[$id]))
                    $rows[$id] = $row;
            }
        }

        // Hydrate objects
        foreach ($rows as $row) {
            $obj = new $class;
            $obj->hydrate($row);
            $collection[] = $obj;
        }
        return $collection;
    }

    /**
     * Set a list of specific fields to update
     * array(field1 => true, field2 => false, langfield1 => array(1 => true, 2 => false))
     *
     * @since 1.5.0
     * @param array $fields
     */
    public function setFieldsToUpdate(array $fields) {
        $this->update_fields = $fields;
    }

    public function isDeleted() {
        if (property_exists($this, 'date_delete'))
            return $this->date_delete !== "0000-00-00 00:00:00";
        if (property_exists($this, 'id_user_deleted'))
            return (int) $this->id_user_deleted !== 0;
        if (property_exists($this, 'deleted'))
            return (int) $this->deleted;

        return false;
    }

    public function getUserAdd()
    {
        if(property_exists($this, 'id_user_add') && (int)$this->id_user_add)
            return User::getById((int)$this->id_user_add);

        return new User();
    }
}
