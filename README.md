# Classy plugin for Craft CMS 3.x

Twig helpers inspired by https://github.com/JedWatson/classnames

## Usage

### Filter

```twig
{% apply class(
  "add-this-initially",
  {
    "add-this": true,
    "remove-this": false,
    "neither-add-nor-remove": null,
    "add-or-remove": ifthis and ifthat,
    "add-but-dont-remove": condition ? true : null,
    "remove-but-dont-add": not condition ? false : null,
  },
  "add-this-regardless-of-above",
) %}
  <mytag class="existing classes"> ... </mytag>
{% endapply %}
```

### Function

```twig
<mytag class="{{ class("yup yes", { "maybe": ifthis and ifthat }) }}">
```

Which depending on the conditions `ifthis` and `ifthat` will give

```twig
<mytag class="yup yes maybe"> or <mytag class="yup yes">
```

---

Each argument can be either a string or a map.

A string argument will be treated as a class name (or multiple space-separated
class names) to add.

An array argument is a map of class names to statuses.

Its keys are single class names or groups of space-seprated classes. The
corresponding values can be

- strictly `true` to add the class or classes
- strictly `false` to remove the class or classes
- `null` or anything else to leave them alone

Any existing classes which aren't mentioned in any of these arguments are left
alone.

Arguments are processed in order.
