<?php
/**
 * Match Input plugin for Craft CMS 3.x
 *
 * Craft field type for text fields that match a regex pattern
 *
 * @link      http://marion.newlevant.com
 * @copyright Copyright (c) 2017 Marion Newlevant
 */

namespace marionnewlevant\matchinput\fields;

use Craft;
use craft\base\ElementInterface;
use craft\fields\PlainText;

/**
 *  Field
 *
 * Whenever someone creates a new field in Craft, they must specify what
 * type of field it is. The system comes with a handful of field types baked in,
 * and we’ve made it extremely easy for plugins to add new ones.
 *
 * https://craftcms.com/docs/plugins/field-types
 *
 * @author    Marion Newlevant
 * @package   MatchInput
 * @since     1.0.0
 */
class MatchInputField extends PlainText
{
    public static function validateRegex($regex)
    {
        set_error_handler(function() { return true; }, E_NOTICE);
        // preg_match() returns 1 if the pattern matches given subject, 0 if it does not, or FALSE if an error occurred.
        $valid = (preg_match($regex, '') !== false);
        restore_error_handler();

        return $valid;
    }

    // Public Properties
    // =========================================================================

    /**
     * @var string The input’s inputMask text
     */
    public $inputMask;

    /**
     * @var string The input’s errorMessage text
     */
    public $errorMessage;

    // If we don't duplicate the properties of PlainText field here, then they don't get saved
    // =========================================================================

    /**
     * @var string|null The input’s placeholder text
     */
    public $placeholder;

    /**
     * @var bool|null Whether the input should allow line breaks
     */
    public $multiline;

    /**
     * @var int The minimum number of rows the input should have, if multi-line
     */
    public $initialRows = 4;

    /**
     * @var int|null The maximum number of characters allowed in the field
     */
    public $charLimit;

    // Static Methods
    // =========================================================================

    /**
     * Returns the display name of this class.
     *
     * @return string The display name of this class.
     */
    public static function displayName(): string
    {
        return Craft::t('match-input', 'Match Input');
    }

    // Public Methods
    // =========================================================================

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['inputMask', 'errorMessage'], 'string'];
        $rules[] = ['inputMask', 'required'];
        $rules[] = ['inputMask', 'isValidRegex'];
        return $rules;
    }

    public function isValidRegex($object, $attribute)
    {
        $inputMask = $this->$object;
        if (!self::validateRegex($inputMask))
        {
            $this->addError($object, Craft::t('matchinput', 'Not a valid regex (missing delimiters?)'));
        }
    }


    /**
     * Returns the component’s settings HTML.
     *
     * @return string|null
     */
    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate(
            'matchinput'
            . DIRECTORY_SEPARATOR
            . '_components'
            . DIRECTORY_SEPARATOR
            . 'fields'
            . DIRECTORY_SEPARATOR
            . '_settings',
            [
            'field' => $this
            ]
        );
    }

    /**
     * Returns the field’s input HTML.
     *
     * @param mixed                 $value           The field’s value. This will either be the [[normalizeValue() normalized value]],
     *                                               raw POST data (i.e. if there was a validation error), or null
     * @param ElementInterface|null $element         The element the field is associated with, if there is one
     *
     * @return string The input HTML.
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {

        return Craft::$app->getView()->renderTemplate(
            'matchinput'. DIRECTORY_SEPARATOR . '_components'. DIRECTORY_SEPARATOR . 'fields'. DIRECTORY_SEPARATOR . '_input',
            [
                'name' => $this->handle,
                'value' => $value,
                'field' => $this,
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getElementValidationRules(): array
    {
        $rules = parent::getElementValidationRules();
        // add our rule
        $rules[] = 'validateMatchesRegex';
        return $rules;
    }

    /**
     * Validates the field value.
     *
     * @param ElementInterface $element
     * @param array|null       $params
     *
     * @return void
     */
    public function validateMatchesRegex(ElementInterface $element, array $params = null)
    {
        $value = $element->getFieldValue($this->handle);
        $match = preg_match($this->inputMask, $value);
        if ($match !== 1)
        {
            $element->addError($this->handle, Craft::t('site', $this->errorMessage));
        }
    }
}
