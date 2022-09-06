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
     * @inheritdoc
     */
    protected function defineRules(): array
    {
        $rules = parent::defineRules();
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
     * @inheritdoc
     */
    public function getSettingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate(
            'match-input/_components/fields/MatchInputField_settings',
            [
                'field' => $this
            ]
        );
    }

    /**
     * @inheritdoc
     */
    protected function inputHtml(mixed $value, ?ElementInterface $element = null): string
    {
        return Craft::$app->getView()->renderTemplate(
            'match-input/_components/fields/MatchInputField_input',
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
    public function validateMatchesRegex(ElementInterface $element): void
    {
        $value = $element->getFieldValue($this->handle);
        $match = preg_match($this->inputMask, $value);
        if ($match !== 1)
        {
            $element->addError($this->handle, Craft::t('site', $this->errorMessage));
        }
    }
}
