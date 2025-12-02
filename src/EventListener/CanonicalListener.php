<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\EventListener;

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\DataContainer;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\PageRegular;
use HeimrichHannot\HeadBundle\Manager\HtmlHeadTagManager;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Backports canonical link functionality of contao 4.13 to contao 4.9.
 */
class CanonicalListener
{
    private Utils $utils;
    private HtmlHeadTagManager $headTagManager;
    private RequestStack $requestStack;
    private InsertTagParser $insertTagParser;

    public function __construct(
        Utils              $utils,
        HtmlHeadTagManager $headTagManager,
        RequestStack       $requestStack,
        InsertTagParser    $insertTagParser
    ) {
        $this->utils = $utils;
        $this->headTagManager = $headTagManager;
        $this->requestStack = $requestStack;
        $this->insertTagParser = $insertTagParser;
    }

    /**
     * @Hook("loadDataContainer")
     */
    public function addFields(string $table): void
    {
        if (PageModel::getTable() !== $table) {
            return;
        }

        $dca = &$GLOBALS['TL_DCA'][$table];

        $dca['fields']['enableCanonical'] = [
            'exclude' => true,
            'inputType' => 'checkbox',
            'default' => true,
            'eval' => ['tl_class' => 'w50 m12'],
            'sql' => "char(1) NOT NULL default ''",
        ];
        $dca['fields']['canonicalLink'] = [
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'url', 'decodeEntities' => true, 'maxlength' => 255, 'dcaPicker' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ];
        $dca['fields']['canonicalKeepParams'] = [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['decodeEntities' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ];

        PaletteManipulator::create()
            ->addLegend('canonical_legend', 'meta_legend', PaletteManipulator::POSITION_AFTER, true)
            ->addField('canonicalLink', 'canonical_legend', PaletteManipulator::POSITION_APPEND)
            ->addField('canonicalKeepParams', 'canonical_legend', PaletteManipulator::POSITION_APPEND)
            ->applyToPalette('regular', 'tl_page');

        PaletteManipulator::create()
            ->addField('enableCanonical', 'adminEmail', PaletteManipulator::POSITION_BEFORE)
            ->applyToPalette('root', 'tl_page')
            ->applyToPalette('rootfallback', 'tl_page');
    }

    /**
     * @Callback(table="tl_page", target="fields.canonicalLink.load")
     * @Callback(table="tl_page", target="fields.canonicalKeepParams.load")
     */
    public function disableCanonicalFieldsListener(string $value, DataContainer $dc): string
    {
        if (!$dc->id || ($pageModel = PageModel::findByPk($dc->id)) === null) {
            return $value;
        }

        if (($rootPageModel = $this->utils->request()->getCurrentRootPageModel($pageModel)) === null) {
            return $value;
        }

        if (!$rootPageModel->enableCanonical) {
            $GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['eval']['disabled'] = true;
        }

        return $value;
    }

    /**
     * @Hook("generatePage", priority=-9)
     */
    public function addCanonicalIfNotSet(PageModel $pageModel, LayoutModel $layout, PageRegular $pageRegular): void
    {
        $rootPageModel = $this->utils->request()->getCurrentRootPageModel($pageModel);
        $request = $this->requestStack->getCurrentRequest();

        if (!$request || !$rootPageModel->enableCanonical || $this->headTagManager->getCanonical()) {
            return;
        }

        if ($pageModel->canonicalLink)
        {
            $url = $this->insertTagParser->replace($pageModel->canonicalLink);

            $mainRequest = $this->requestStack->getMainRequest();

            // Ensure absolute links
            if (!\preg_match('@^https?://@', $url))
            {
                if (!$mainRequest) {
                    throw new \RuntimeException('The request stack did not contain a request');
                }

                $url = $request->getSchemeAndHttpHost().$request->getBasePath().'/'.$url;
            }

            $this->headTagManager->setCanonical($url);

            return;
        }

        $keepParams = [];

        if ($pageModel->canonicalKeepParams) {
            $keepParams = array_map('trim', explode(',', $pageModel->canonicalKeepParams));
        }

        $params = [];

        foreach ($request->query->all() as $originalParam => $value) {
            foreach ($keepParams as $param) {
                $regex = sprintf('/^%s$/', str_replace('\*', '.*', preg_quote($param, '/')));

                if (preg_match($regex, $originalParam)) {
                    $params[$originalParam] = $value;
                }
            }
        }

        $request = Request::create(
            $request->getSchemeAndHttpHost().$request->getBaseUrl().$request->getPathInfo(),
            $request->getMethod(),
            $params
        );

        $this->headTagManager->setCanonical($request->getUri());
    }
}
