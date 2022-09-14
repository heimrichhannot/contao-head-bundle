# Contao Head bundle

[![](https://img.shields.io/packagist/v/heimrichhannot/contao-head-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-head-bundle)
[![](https://img.shields.io/packagist/dt/heimrichhannot/contao-head-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-head-bundle)

This bundle enhances the handling of html `<head>` section tags. It provides services to update head tags dynamically from your code.

## Features
- Provide a nice api to set head tags like meta, title, base, link
- Set important meta tags like og:title, og:description, og:url and twitter:card out of the box
- Set open graph and twitter fallback image on root page
- Set twitter author per root page

## Usage

### Setup

1. Install with composer
2. Update your database
3. Set following config variables (if you don't need the legacy implementation)

    ```yaml
    huh_head:
      use_contao_head: true
      use_contao_variables: true
    ```
4. Optional: Set fallback image and twitter author in root page(s)

### Set head content

> Currently, there are two ways to update head tags, as we are in progress of refactoring this bundle to use a better approach. 
> This section describes the new/modern way, currently available for meta tags and base tag. For the legacy way see the next chapters.

To set base tag and meta tags, use the `HtmlHeadTagManager` service:

```php
use HeimrichHannot\HeadBundle\HeadTag\BaseTag;
use HeimrichHannot\HeadBundle\HeadTag\MetaTag;
use HeimrichHannot\HeadBundle\HeadTag\Meta\CharsetMetaTag;
use HeimrichHannot\HeadBundle\HeadTag\Meta\HttpEquivMetaTag;
use HeimrichHannot\HeadBundle\HeadTag\Meta\PropertyMetaTag;
use HeimrichHannot\HeadBundle\Manager\HtmlHeadTagManager;
use Symfony\Component\HttpFoundation\Request;

class SomeEventListener
{
    private HtmlHeadTagManager $headTagManager;

    public function updateBaseTag(Request $request): void
    {
        // Set base tag to null to remove it
        $this->headTagManager->setBaseTag(null);
        
        //Set base tag from object or url
        $this->headTagManager->setBaseTag(new BaseTag($request->getSchemeAndHttpHost()));
        $this->headTagManager->setBaseTag('https://example.org'));
    }
    
    public function setMetaTag(): void
    {
        // Add a new meta tag. If a tag with the same name already exists, it will be overridden
        $this->headTagManager->addMetaTag(new MetaTag('author', 'John Doe'));
        
        // Get an existing tag. To retrieve names from tags with : (colon), replace the colon with _ (underscore).
        $description = ($tag = $this->headTagManager->getMetaTag('og_description')) ? $tag->getContent() : '';
        $this->headTagManager->removeMetaTag('twitter_site');
        
        // Create a tag for property meta tags
        $this->headTagManager->addMetaTag(new PropertyMetaTag('og:type', 'article'));
        
        // Create a http-equiv tag
        $this->headTagManager->addMetaTag(new HttpEquivMetaTag('refresh', '30'));
        
        // Set a charset tag
        $this->headTagManager->addMetaTag(new CharsetMetaTag('UTF-8'));
    }
}
```


### Other tags

> This section describe the current usage for setting tags other than base or meta tags. 
> This is the legacy implementation that should be replaced in the future.

Each meta tags is registered as a symfony service. Get the service and set the content, that's it.

**Example `<meta name="date">`**

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

| tag                                                                   | setter                                                                                                |
|-----------------------------------------------------------------------|-------------------------------------------------------------------------------------------------------|
| `<title>My site title</title>`                                        | `$container->get('huh.head.tag.title')->setContent('My site title')`                                  |
| `<link rel="prev" href="http://heimrich-hannot.de/list?page_n199=1">` | `$container->get('huh.head.tag.link_prev')->setContent('http://heimrich-hannot.de/list?page_n199=1')` |
| `<link rel="next" href="http://heimrich-hannot.de/list?page_n199=3">` | `$container->get('huh.head.tag.link_next')->setContent('http://heimrich-hannot.de/list?page_n199=3')` |
| `<link rel="canonical" href="http://heimrich-hannot.de/site-name">`   | `$container->get('huh.head.tag.link_canonical')->setContent('http://heimrich-hannot.de/site-name')`   |


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
## Legacy integration

Be sure, `huh_head.use_contao_head` and/or `huh_head.use_contao_variables` are not set to true.

Output `$this->meta()` in your fe_page template ()

```
<?php $this->block('meta'); ?>
    <?= $this->meta(); ?>
<?php $this->endblock(); ?>
```

Make sure, that you remove (are outputted by $this->meta() if `huh_head.use_contao_head` not true):

```
<meta charset="<?= $this->charset ?>">
<title><?= $this->title ?></title>
<base href="<?php echo $this->base; ?>">
<meta name="robots" content="<?= $this->robots ?>">
<meta name="description" content="<?= $this->description ?>">
```

The `meta` function accepts currently one parameter that can contain service names (array) that should be skipped.

## Config reference

```yaml
# Default configuration for extension with alias: "huh_head"
huh_head:

    # Use the default head variables for title,base,robots and description instead of removing them from the page template.
    use_contao_head:      false

    # Use the default contao template variables for outputting head tags instead of the meta function.
    use_contao_variables: false
```