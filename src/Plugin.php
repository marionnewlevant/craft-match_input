<?php
/**
 * Match Input plugin for Craft CMS 3.x
 *
 * Craft field type for text fields that match a regex pattern
 *
 * @link      http://marion.newlevant.com
 * @copyright Copyright (c) 2017 Marion Newlevant
 */

namespace marionnewlevant\matchinput;


use Craft;
use craft\services\Fields;
use craft\events\RegisterComponentTypesEvent;
use yii\base\Event;
use marionnewlevant\matchinput\fields\MatchInputField;

/**
 *
 * @author    Marion Newlevant
 * @package   MatchInput
 * @since     1.0.0
 */
class Plugin extends \craft\base\Plugin
{

    // Public Methods
    // =========================================================================

    /**
     * Called after the plugin class is instantiated; do any one-time initialization
     * here such as hooks and events.
     *
     * If you have a '/vendor/autoload.php' file, it will be loaded for you automatically;
     * you do not need to load it in your init() method.
     *
     */
    public function init()
    {
        parent::init();

        // Register our fields
        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = MatchInputField::class;
            }
        );
    }
}
