<?php

namespace korcontrol\classy;

use Twig;
use yii\base\InvalidArgumentException;
use craft\helpers\Html;
use Craft;

class Extension extends \Twig\Extension\AbstractExtension
{
    public function getFilters()
    {
        return [
            new Twig\TwigFilter(
                "class",
                [$this, "classFilter"],
                ["is_safe" => ["html"]],
            ),
        ];
    }

    public function getFunctions()
    {
        return [new Twig\TwigFunction("class", [$this, "classFunction"])];
    }

    /**
     * Modify classes.
     *
     * @param array $set - A set (array where the keys are the only important
     *     thing) of initial classes
     * @param (string|array)[] $args - See the public methods' docs
     * @return string[] - List of final classes
     * @throws InvalidArgumentException if a bad argument is given
     */
    private static function modifyClasses(array $set, array $args): array
    {
        foreach ($args as $arg) {
            if (is_string($arg)) {
                foreach (Html::explodeClass($arg) as $class) {
                    $set[$class] = true;
                }
            } elseif (is_array($arg)) {
                foreach ($arg as $classes => $status) {
                    foreach (Html::explodeClass($classes) as $class) {
                        if ($status === true) {
                            $set[$class] = true;
                        } elseif ($status === false) {
                            unset($set[$class]);
                        }
                    }
                }
            } else {
                throw new InvalidArgumentException(
                    "Each argument should be a string or map",
                );
            }
        }

        return array_keys($set);
    }

    /**
     * Modify classes on the given HTML tag.
     *
     * Usage example:
     *
     *     {% apply class(
     *       "add-this-initially",
     *       {
     *         "add-this": true,
     *         "remove-this": false,
     *         "neither-add-nor-remove": null,
     *         "add-or-remove": ifthis and ifthat,
     *         "add-but-dont-remove": condition ? true : null,
     *         "remove-but-dont-add": not condition ? false : null,
     *       },
     *       "add-this-regardless-of-above",
     *     ) %}
     *       <mytag class="existing classes"> ... </mytag>
     *     {% endapply %}
     *
     * @param string $tag - The HTML tag to modify
     * @param (string|array)[] ...$args - Classes to add or modify.
     *     Each argument can be either a string or a map.
     *
     *     A string argument will be treated as a class name (or multiple
     *     space-separated class names) to add.
     *
     *     An array argument is a map of class names to statuses.
     *     Its keys are single class names or groups of space-seprated classes.
     *     The corresponding values can be
     *     - strictly `true` to add the class or classes
     *     - strictly `false` to remove the class or classes
     *     - `null` or anything else to leave them alone
     *
     *     Any existing classes which aren't mentioned in any of these arguments
     *     are left alone.
     *
     *     Arguments are processed in order.
     * @return string - The modified HTML tag
     */
    public function classFilter(string $tag): string
    {
        try {
            $newClasses = self::modifyClasses(
                array_flip(Html::parseTagAttributes($tag)["class"] ?? []),
                array_slice(func_get_args(), 1),
            );

            $newTag = Html::modifyTagAttributes($tag, ["class" => false]);
            if (!empty($newClasses)) {
                $newTag = Html::modifyTagAttributes($newTag, [
                    "class" => $newClasses,
                ]);
            }
            return $newTag;
        } catch (InvalidArgumentException $e) {
            Craft::warning($e->getMessage(), __METHOD__);
            return $tag;
        }
    }

    /**
     * From class names and maps of class names to statuses, build a string
     * suitable for a class attribute.
     *
     * Usage example:
     *
     *     <mytag class="{{ class("yup yes", { "maybe": ifthis and ifthat }) }}">
     *
     * Which depending on the conditions `ifthis` and `ifthat` will give
     *
     *     <mytag class="yup yes maybe"> or <mytag class="yup yes">
     *
     * @param (string|array)[] ...$args - Classes to add or modify.
     *     Each argument can be either a string or a map.
     *
     *     A string argument will be treated as a class name (or multiple
     *     space-separated class names) to add.
     *
     *     An array argument is a map of class names to statuses.
     *     Its keys are single class names or groups of space-seprated classes.
     *     The corresponding values can be
     *     - strictly `true` to add the class or classes
     *     - strictly `false` to remove the class or classes
     *     - `null` or anything else to leave them alone
     *
     *     Arguments are processed in order.
     * @return string - Final space-separated class string
     */
    public function classFunction(): string
    {
        try {
            return implode(" ", self::modifyClasses([], func_get_args()));
        } catch (InvalidArgumentException $e) {
            Craft::warning($e->getMessage(), __METHOD__);
            return "";
        }
    }
}
