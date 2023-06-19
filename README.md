# Contao Head bundle

[![](https://img.shields.io/packagist/v/heimrichhannot/contao-head-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-head-bundle)
[![](https://img.shields.io/packagist/dt/heimrichhannot/contao-head-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-head-bundle)

This bundle enhances the handling of html `<head>` section tags. It provides services to update head tags dynamically from your code.

## Features
- Provide a nice api to set head tags like meta, title, base, link
- Sets important meta tags like og:title, og:description, og:url and twitter:card out of the box
- Allow setting open graph and twitter fallback image on root page
- Allow setting twitter author per root page
- Backport canonical url option from contao 4.13 for contao 4.9+

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

To set base, title, meta and link tags, use the `HtmlHeadTagManager` service:

```php
use HeimrichHannot\HeadBundle\HeadTag\BaseTag;
use HeimrichHannot\HeadBundle\HeadTag\MetaTag;
use HeimrichHannot\HeadBundle\HeadTag\TitleTag;
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
    
    public function updatedTitleTag(): void
    {
        // Set title to "Hello World"
        $this->headTagManager->setTitleTag('Hello World');
        
        // Set title tag from object and adjust output format
        $this->headTagManager->setTitleTag(new TitleTag('Foo Bar', '%s | {{page::rootPageTitle}}'))
        // Will output: <title>Foo Bar | My Great Website Page Title</title>
    }
    
    public function setMetaTags(): void
    {
        // Add a new meta tag. If a tag with the same name already exists, it will be overridden
        $this->headTagManager->addMetaTag(new MetaTag('author', 'John Doe'));
        
        // Get an existing tag
        $description = ($tag = $this->headTagManager->getMetaTag('og:description')) ? $tag->getContent() : '';
        
        // Remove a tag
        $this->headTagManager->removeMetaTag('twitter:site');
        
        // Create a tag for property meta tags
        $this->headTagManager->addMetaTag(new PropertyMetaTag('og:type', 'article'));
        
        // Create a http-equiv tag
        $this->headTagManager->addMetaTag(new HttpEquivMetaTag('refresh', '30'));
        
        // Set a charset tag
        $this->headTagManager->addMetaTag(new CharsetMetaTag('UTF-8'));
        
        // Create tags without class (usefull when creating tags in a loop without custom checks)
        $this->headTagManager->addMetaTag(
            $this->headTagManager->getHeadTagFactory()->createMetaTag('description', 'Lorem ipsum!')
        );
        $this->headTagManager->addMetaTag(
            $this->headTagManager->getHeadTagFactory()->createTagByName('meta_og:url', 'https://example.org')
        );
    }
    
    public function setLinkTags(): void
    {
        // Add a new link tag. If a tag with the same name already exists, it will be overridden
        $this->headTagManager->addLinkTag(new LinkTag('next', 'https://example.org?page=2'));
        
        // Get an existing tag
        $this->headTagManager->getLinkTag('prev');
        
        // Remove a tag
        $this->headTagManager->removeLinkTag('prev');
        
        // Shorthand for canonical tag
        $this->headTagManager->setCanonical('https://example.org');
    }
}
```

## Legacy integration

Be sure, `huh_head.use_contao_head` and/or `huh_head.use_contao_variables` are not set to true.

Output `$this->meta()` in your fe_page template ()

```
<?php $this->block('meta'); ?>
    <?= $this->meta(); ?>
<?php $this->endblock(); ?>
```

Make sure, that you remove (are outputted by $this->meta() if `huh_head.use_contao_head` is not true):

```
<meta charset="<?= $this->charset ?>">
<title><?= $this->title ?></title>
<base href="<?php echo $this->base; ?>">
<meta name="robots" content="<?= $this->robots ?>">
<meta name="description" content="<?= $this->description ?>">
```

The `meta` function accepts currently one parameter that can contain service names (array) that should be skipped.

## Developers

### Backend field

Get tag options for a select field. If you want to define options by your own, prepend meta tag options with `meta_`.

```php
use HeimrichHannot\HeadBundle\Helper\DcaHelper;

class HeadTagOptionsListener {
    private DcaHelper $dcaHelper;

    public function __invoke() {
        return $this->dcaHelper->getTagOptions([
            // filter: (array|null) If set, only tags fulfill given filters will be returned. See FILTER constants for available options. Default null
            'filter' => [DcaHelper::FILTER_META, DcaHelper::FILTER_TITLE],
            // skip_tags: (array) Skip specific tags. Default empty
            'skip_tag' => ['og:locale'],
        ]);
    }
}
```

Example how to evaluate field values:

```php
use Contao\ContentModel;
use HeimrichHannot\HeadBundle\Manager\HtmlHeadTagManager;

class SomeEventListener {
    private HtmlHeadTagManager $headTagManager;
    
    public function __invoke(ContentModel $contentModel){
        $tag = $this->headTagManager->getHeadTagFactory()->createTagByName($contentModel->headTag);
        if ($tag) {
            $tag->setAttribute("content", $contentModel->headTagContent);
            $this->headTagManager->addTag($tag);
        }
    }
}
```

## Config reference

```yaml
# Default configuration for extension with alias: "huh_head"
huh_head:

    # Use the default head variables for title,base,robots and description instead of removing them from the page template.
    use_contao_head:      false

    # Use the default contao template variables for outputting head tags instead of the meta function.
    use_contao_variables: false
```