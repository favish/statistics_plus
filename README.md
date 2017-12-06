# Statistics Plus

The purpose of this module is to performantly display the view counts of nodes. On a page that has a large number of teasers, a single XHR request returns the view counts and displays them.

## Configuration
After installing the module, go to /admin/config/system/statistics_plus to set your configuration, which includes the following:
* Enable fuzz - if concrete view counts isn't cutting it, add some fuzz.
* Icon placement - should the stylish eyeball icon go before or after the view count text? You decide.

## Usage
* Add the node-extras.html.twig template to your theme.
* Add the following snippet to the node template where you'd like to display view counts (double check that the path is correct):
```
{% if extras %}
  {% include directory ~ '/templates/content/node-extras.html.twig' with extras %}
{% endif %}
```