# Contao Head bundle

[![](https://img.shields.io/packagist/v/heimrichhannot/contao-head-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-head-bundle)
[![](https://img.shields.io/packagist/dt/heimrichhannot/contao-head-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-head-bundle)
[![Build Status](https://travis-ci.org/heimrichhannot/contao-head-bundle.svg?branch=master)](https://travis-ci.org/heimrichhannot/contao-head-bundle)
[![Coverage Status](https://coveralls.io/repos/github/heimrichhannot/contao-head-bundle/badge.svg?branch=master)](https://coveralls.io/github/heimrichhannot/contao-head-bundle?branch=master)

This module contains enhancements for the contao frontend page <head> section. It provides a [service for each tag](#available-tags) and gives better overwrite control.

## Usage

Add the meta information to your `fe_page.html5` template and make sure, that you remove robots:

```
<?php $this->block('meta'); ?>
    <?= $this->meta(); ?>
<?php $this->endblock(); ?>
```
The `meta` function accepts currently one parameter that can contain service names (array) that should be skipped.


and make sure, that you remove:

```
<meta charset="<?= $this->charset ?>">
<title><?= $this->title ?></title>
<base href="<?php echo $this->base; ?>">
<meta name="robots" content="<?= $this->robots ?>">
<meta name="description" content="<?= $this->description ?>">
```

as they are already shipped within `$this->meta`.

Each meta tags is registered as a symfony service. Get the service and set the content, thats it.

### Example `<meta name="date">`

```
/** @var ContainerInterface $container */
$container->get('huh.head.tag.meta_date')->setContent(\Date::parse('c', time()));
```

## Available Tags

The container parameter `huh.head.tags` contains a list of all available tag services from the list below. 

```
/** @var ContainerInterface $container */
#available-tags->getParameter('huh.head.tags')
```

| tag | setter |
|----:|--------|
| `<base href="http://heimrich-hannot.de">` | `$container->get('huh.head.tag.base')->setContent(\Environment::get('base'))`  |
| `<title>My site title</title>` | `$container->get('huh.head.tag.title')->setContent('My site title')`  |
| `<meta name="date" content="2017-07-28T11:31:00+02:00">` | `$container->get('huh.head.tag.title')->setContent(\Date::parse('c', time()))`  |
| `<meta name="language" content="de">` | `$container->get('huh.head.tag.meta_language')->setContent($container->get('translator')->getLocale())` |
| `<meta charset="utf-8">` | `$container->get('huh.head.tag.meta_charset')->setContent(\Config::get('characterSet'))`  |
| `<meta name="meta_title" content="My site title">` | `$container->get('huh.head.tag.meta_title')->setContent('My site title')`  |
| `<meta name="meta_description" content="My site description">` | `$container->get('huh.head.tag.meta_title')->setContent('My site title')`  |
| `<meta name="meta_keywords" content="keyword1, keyword2">` | `$container->get('huh.head.tag.meta_keywords')->setContent('keyword1, keyword2')`  |
| `<meta name="meta_robots" content="index,follow">` | `$container->get('huh.head.tag.meta_robots')->setContent('index,follow')`  |
| `<meta property="og:title" content="My site title">` | `$container->get('huh.head.tag.og_title')->setContent('My site title')`  |
| `<meta property="og:type" content="article">` | `$container->get('huh.head.tag.og_type')->setContent('article')`  |
| `<meta property="og:url" content="http://heimrich-hannot.de/my-article-url">` | `$container->get('huh.head.tag.og_url')->setContent(\Environment::get('url') . '/' . $this->alias)`  |
| `<meta property="og:description" content="My site description">` | `$container->get('huh.head.tag.og_description')->setContent('My site description')`  |
| `<meta property="og:image" content="http://heimrich-hannot.de/my-article-image.jpg">` | `$container->get('huh.head.tag.og_image')->setContent('http://heimrich-hannot.de/my-article-image.jpg')`  |
| `<meta property="og:locale" content="de">` | `$container->get('huh.head.tag.og_locale')->setContent($container->get('request_stack')->getCurrentRequest()->getLocale())`  |
| `<meta property="og:site_name" content="My website title">` | `$container->get('huh.head.tag.og_site_name')->setContent('My website title')`  |
| `<meta name="twitter:card" content="summary_large_image">` | `$container->get('huh.head.tag.twitter_card')->setContent('summary_large_image')`  |
| `<meta name="twitter:site" content="@twitterSiteName">` | `$container->get('huh.head.tag.twitter_site')->setContent('@twitterSiteName')`  |
| `<meta name="twitter:creator" content="@twitterCreator">` | `$container->get('huh.head.tag.twitter_creator')->setContent('@twitterCreator')`  |
| `<meta name="twitter:title" content="My article title">` | `$container->get('huh.head.tag.twitter_title')->setContent('My article title')`  |
| `<meta name="twitter:description" content="My article description">` | `$container->get('huh.head.tag.twitter_description')->setContent('My article description')`  |
| `<meta name="twitter:image" content="http://heimrich-hannot.de/my-article-image.jpg">` | `$container->get('huh.head.tag.twitter_image')->setContent('http://heimrich-hannot.de/my-article-image.jpg')`  |
| `<meta name="twitter:image:alt" content="My image alt text">` | `$container->get('huh.head.tag.twitter_image_alt')->setContent('My image alt text')`  |
| `<meta name="twitter:player" content="https://www.youtube.com/embed/tERRFWuYG48">` | `$container->get('huh.head.tag.twitter_player')->setContent('https://www.youtube.com/embed/tERRFWuYG48')`  |
| `<meta name="twitter:player:width" content="480">` | `$container->get('huh.head.tag.twitter_player_width')->setContent('480')`  |
| `<meta name="twitter:player:height" content="300">` | `$container->get('huh.head.tag.twitter_player_height')->setContent('300')`  |
| `<meta name="twitter:player:stream" content="http://heimrich-hannot.de/my-video.mp4">` | `$container->get('huh.head.tag.twitter_player_stream')->setContent('http://heimrich-hannot.de/my-video.mp4')`  |
| `<meta name="twitter:player:stream:content_type" content="video/mp4">` | `$container->get('huh.head.tag.twitter_player_stream_content_type')->setContent('video/mp4')`  |
| `<link rel="prev" href="http://heimrich-hannot.de/list?page_n199=1">` | `$container->get('huh.head.tag.link_prev')->setContent('http://heimrich-hannot.de/list?page_n199=1')`  |
| `<link rel="next" href="http://heimrich-hannot.de/list?page_n199=3">` | `$container->get('huh.head.tag.link_next')->setContent('http://heimrich-hannot.de/list?page_n199=3')`  |
| `<link rel="canonical" href="http://heimrich-hannot.de/site-name">` | `$container->get('huh.head.tag.link_canonical')->setContent('http://heimrich-hannot.de/site-name')`  |


The name of the `twitter:site` @username can be provided within root contao page `tl_page`.

## Custom Tags

If you want to register your custom tag, add create a class that implements the `TagInterface` and extends from one of the `Abstract` Tag classes like `AbstractSimpleTag`.

In your bundle `services.yml` register your tag:

```
services:
    huh.head.tag.meta_custom:
      class: HeimrichHannot\HeadBundle\Tag\Simple\MetaCustom
      public: true
      arguments: ['@huh.head.tag_manager']
```

To set the tag content, simply call:

```
$container->get('huh.head.tag.meta_custom')->setContent('FOO');
```
