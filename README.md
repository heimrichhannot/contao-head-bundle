# Contao <head> bundle

This module contains enhancements for the contao frontend page <head> region.

## Usage

Add the meta information to your `fe_page.html5` template and make sure, that you remove robots:

```
<?php $this->block('meta'); ?>
    <?= $this->meta; ?>
<?php $this->endblock(); ?>
```

and make sure, that you remove:

```
<meta charset="<?= $this->charset ?>">
<title><?= $this->title ?></title>
<meta name="robots" content="<?= $this->robots ?>">
<meta name="description" content="<?= $this->description ?>">
```

as they are already shipped within `$this->meta`.

Each meta tags is registered as a symfony service. Get the service and set the content, thats it.

### Example `<meta name="date">`

```
\System::getContainer()->get('huh.head.tag.meta_date')->setContent(\Date::parse('c', time()));
```

## Available Tags

| tag | setter |
|----:|--------|
| `<base href="http://heimrich-hannot.de">` | `\System::getContainer()->get('huh.head.tag.base')->setContent(\Environment::get('base'))`  |
| `<title>My custom title</title>` | `\System::getContainer()->get('huh.head.tag.title')->setContent('My custom title')`  |
| `<meta name="date" content="2017-07-28T11:31:00+02:00">` | `\System::getContainer()->get('huh.head.tag.title')->setContent('My custom title')`  |

## Custom Tags

If you want to register your custom tag, add create a class that implements the `TagInterface` and extends from one of the `Abstract` Tag classes like `AbstractSimpleTag`.

In your bundle `services.yml` register your tag:

```
services:
    huh.head.tag.meta_custom:
      class: HeimrichHannot\HeadBundle\Tag\Simple\MetaCustom
      arguments: ['@huh.head.tag_manager']
```

To set the tag content, simply call:

```
\System::getContainer()->get('huh.head.tag.meta_custom')->setContent('FOO');
```