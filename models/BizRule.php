<?php

namespace ekalokman\AdminOci8\models;

use yii\rbac\Rule;
use Yii;

/**
 * BizRule
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class BizRule extends \yii\base\Model
{
    /**
     * @var string name of the rule
     */
    public $NAME;

    /**
     * @var integer UNIX timestamp representing the rule creation time
     */
    public $CREATEDAT;

    /**
     * @var integer UNIX timestamp representing the rule updating time
     */
    public $UPDATEDAT;

    /**
     * @var string Rule classname.
     */
    public $CLASSNAME;

    /**
     * @var Rule
     */
    private $_item;

    /**
     * Initilaize object
     * @param \yii\rbac\Rule $item
     * @param array $config
     */
    public function __construct($item, $config = [])
    {
        $this->_item = $item;
        if ($item !== null) {
            $this->NAME = $item->name;
            $this->CLASSNAME = get_class($item);
        }
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['NAME', 'CLASSNAME'], 'required'],
            [['CLASSNAME'], 'string'],
            [['CLASSNAME'], 'classExists']
        ];
    }

    /**
     * Validate class exists
     */
    public function classExists()
    {
        if (!class_exists($this->className) || !is_subclass_of($this->className, Rule::className())) {
            $this->addError('classname', "Unknown Class: {$this->className}");
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'NAME' => Yii::t('rbac-admin', 'Name'),
            'CLASSNAME' => Yii::t('rbac-admin', 'Class Name'),
        ];
    }

    /**
     * Check if new record.
     * @return boolean
     */
    public function getIsNewRecord()
    {
        return $this->_item === null;
    }

    /**
     * Find model by id
     * @param type $id
     * @return null|static
     */
    public static function find($id)
    {
        $item = Yii::$app->authManager->getRule($id);
        if ($item !== null) {
            return new static($item);
        }

        return null;
    }

    /**
     * Save model to authManager
     * @return boolean
     */
    public function save()
    {
        if ($this->validate()) {
            $manager = Yii::$app->authManager;
            $class = $this->classname;
            if ($this->_item === null) {
                $this->_item = new $class();
                $isNew = true;
            } else {
                $isNew = false;
                $oldName = $this->_item->name;
            }
            $this->_item->name = $this->name;

            if ($isNew) {
                $manager->add($this->_item);
            } else {
                $manager->update($oldName, $this->_item);
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * Get item
     * @return Item
     */
    public function getItem()
    {
        return $this->_item;
    }
}
