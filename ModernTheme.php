<?php

declare(strict_types=1);

namespace JessewebDotCom\Webtrees\Module\ModernTheme;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Menu;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\View;
use Fisharebest\Webtrees\Session;
use Fisharebest\Webtrees\FlashMessages;
use Psr\Http\Message\ResponseInterface;
use Fisharebest\Localization\Translation;
use Psr\Http\Message\ServerRequestInterface;
use Fisharebest\Webtrees\Module\MinimalTheme;
use Illuminate\Database\Capsule\Manager as DB;
use Fisharebest\Webtrees\Contracts\UserInterface;
use Fisharebest\Webtrees\Module\ModuleThemeTrait;
use Fisharebest\Webtrees\Module\ModuleConfigTrait;
use Fisharebest\Webtrees\Module\ModuleCustomTrait;
use Fisharebest\Webtrees\Module\ModuleFooterTrait;
use Fisharebest\Webtrees\Module\ModuleGlobalTrait;
use Fisharebest\Webtrees\Module\ModuleThemeInterface;
use Fisharebest\Webtrees\Module\ModuleConfigInterface;
use Fisharebest\Webtrees\Module\ModuleCustomInterface;
use Fisharebest\Webtrees\Module\ModuleFooterInterface;
use Fisharebest\Webtrees\Module\ModuleGlobalInterface;

class ModernTheme extends MinimalTheme implements ModuleThemeInterface, ModuleCustomInterface, ModuleFooterInterface, ModuleGlobalInterface, ModuleConfigInterface
{
    use ModuleThemeTrait;
    use ModuleCustomTrait;
    use ModuleFooterTrait;
    use ModuleGlobalTrait;
    use ModuleConfigTrait;

    // Module constants
    public const CUSTOM_AUTHOR = 'JessewebDotCom';
    public const CUSTOM_VERSION = '0.0.1';
    public const GITHUB_REPO = 'webtrees-theme-modern';
    public const AUTHOR_WEBSITE = 'jesseweb.com';
    public const CUSTOM_SUPPORT_URL = 'https://github.com/JesseWebDotCom/webtrees-theme-modern';

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\AbstractModule::title()
     */
    public function title(): string
    {
        return I18N::translate('modern');
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleCustomInterface::customModuleAuthorName()
     */
    public function customModuleAuthorName(): string
    {
        return self::CUSTOM_AUTHOR;
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleCustomInterface::customModuleVersion()
     */
    public function customModuleVersion(): string
    {
        return self::CUSTOM_VERSION;
    }

    /**
     * A URL that will provide the latest stable version of this module.
     *
     * @return string
     */
    public function customModuleLatestVersionUrl(): string
    {
        return 'https://raw.githubusercontent.com/' . self::CUSTOM_AUTHOR . '/' . self::GITHUB_REPO . '/main/latest-version.txt';
    }

    /**
     * Fetch the latest version of this module.
     *
     * @return string
     */
    public function customModuleLatestVersion(): string
    {
        return 'https://github.com/' . self::CUSTOM_AUTHOR . '/' . self::GITHUB_REPO . '/releases/latest';
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleCustomInterface::customModuleSupportUrl()
     */
    public function customModuleSupportUrl(): string
    {
        return self::CUSTOM_SUPPORT_URL;
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\AbstractModule::boot()
     */
    public function boot(): void
    {
        // Register a namespace for our views.
        View::registerNamespace($this->name(), $this->resourcesFolder() . 'views/');

        // Replace an existing view with our own version.
        View::registerCustomView('::layouts/default', $this->name() . '::layouts/default');
        View::registerCustomView('::individual-page', $this->name() . '::individual-page');
        View::registerCustomView('::individual-page-tabs', $this->name() . '::individual-page-tabs');

        View::registerCustomView('::modules/statistics-chart/page', $this->name() . '::modules/statistics-chart/page');
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\AbstractModule::resourcesFolder()
     */
    public function resourcesFolder(): string
    {
        return __DIR__ . '/resources/';
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function getAdminAction(): ResponseInterface
    {
        if (Session::get('theme') !== $this->name()) {
            // We need to register the namespace for this view because the boot didn't run
            View::registerNamespace($this->name(), $this->resourcesFolder() . 'views/');
        }

        $this->layout = 'layouts/administration';

        return $this->viewResponse($this->name() . '::settings', [
            'enable_icons' => $this->getPreference('enable-icons', '0'),

            'allow_switch' => $this->getPreference('allow-switch', '0'),
            'palette'      => $this->getPreference('palette', 'modern-default'),
            'palettes'     => $this->palettes(),
            'title'        => $this->title()
        ]);
    }

    /**
     * Save the user preference.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function postAdminAction(ServerRequestInterface $request): ResponseInterface
    {
        $params = (array) $request->getParsedBody();

        if ($params['save'] === '1') {

            if ($params['allow-switch'] === '0') {
                // remove any previous set users' palette choice.
                DB::table('user_setting')->where('setting_name', '=', 'modern-palette')->delete();
            }

            $this->setPreference('palette', $params['palette']);
            $this->setPreference('allow-switch', $params['allow-switch']);

            $this->setPreference('enable-icons', $params['enable-icons']);


            $message = I18N::translate('The preferences for the module “%s” have been updated.', $this->title());
            FlashMessages::addMessage($message, 'success');

        }

        return redirect($this->getConfigLink());
    }

    /**
     * Raw content, to be added at the end of the <head> element.
     * Typically, this will be <link> and <meta> elements.
     * we use it to load the special font files
     *
     * @return string
     */
    public function headContent(): string {

        return
            '<style>

            @font-face {
                font-family: \'icomoon\';
                src: url("' . $this->assetUrl('fonts/icomoon.eot') . '");
                src: url("' . $this->assetUrl('fonts/icomoon.eot') . ' #iefix") format("embedded-opentype"),
                    url("' . $this->assetUrl('fonts/icomoon.ttf') . '") format("truetype"),
                    url("' . $this->assetUrl('fonts/icomoon.woff') . '") format("woff"),
                    url("' . $this->assetUrl('fonts/icomoon.svg') . ' #icomoon") format("svg");
                font-weight: normal;
                font-style: normal;
                font-display: block;
            }

            @font-face {
                font-family: \'Font Awesome 5 Free\';
                src: url("' . $this->assetUrl('fonts/fa-solid-900.eot') . '");
                src: url("' . $this->assetUrl('fonts/fa-solid-900.eot') . '?#iefix") format("embedded-opentype"),
                    url("' . $this->assetUrl('fonts/fa-solid-900.woff2') . '") format("woff2"),
                    url("' . $this->assetUrl('fonts/fa-solid-900.woff') . '") format("woff"),
                    url("' . $this->assetUrl('fonts/fa-solid-900.ttf') . '") format("truetype"),
                    url("' . $this->assetUrl('fonts/fa-solid-900.svg') . '#fontawesome") format("svg");
                font-style: normal;
                font-weight: 900;
                font-display: block;
            }

            .fa,
            .fas {
                font-family: \'Font Awesome 5 Free\';
                font-weight: 900;
            }            

            </style>';
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\AbstractModule::stylesheets()
     */
    public function stylesheets(): array
    {
        $files = [];
        
        $palette = $this->palette();

        // load each palette
        $baseDirectory = 'css/palettes/';
    
        $paletteParts = explode('-', $palette);
        $paletteName = $paletteParts[0];
        $subDirectory = isset($paletteParts[1]) ? $paletteParts[1] : '';
    
        if ($subDirectory) {
            $file = $baseDirectory . $paletteName . '/' . $subDirectory . '/bootstrap.min.css';
            $files[] = $this->assetUrl($file);
        } else {
            $file = $baseDirectory . $paletteName . '/bootstrap.min.css';
            $files[] = $this->assetUrl($file);
        }
    
        // load changes that apply to all palettes
        $files[] = $this->assetUrl('css/palettes.css');

        // load any palette customizations
        if ($this->getPreference('enable-icons')) {
            $files[] = $this->assetUrl('css/customizations/enable-icons.min.css');
        }

        // base styles (must be here to override palettes)
        $files[] = $this->assetUrl('css/base.min.css');
    
        return $files;
    }
    

    /**
     * A footer, to be added at the bottom of every page.
     *
     * @param ServerRequestInterface $request
     *
     * @return string
     */
    public function getFooter(ServerRequestInterface $request): string
    {
        if (Session::get('theme') === $this->name()) {
            return view($this->name() . '::theme/footer-credits', [
                'url' => 'https://' . self::AUTHOR_WEBSITE,
                'text' => 'Design: ' . self::AUTHOR_WEBSITE,
            ]);
        } else {
            return "";
        }
    }


    /**
     * Generate a list of items for the user menu.
     *
     * @param Tree|null $tree
     *
     * @return Menu[]
     */
    public function userMenu(?Tree $tree): array
    {
        if ($this->getPreference('allow-switch')) {
            return array_filter([
                $this->menuPendingChanges($tree),
                $this->menuMyPages($tree),
                $this->menuThemes(),
                $this->menuPalette(),
                $this->menuLanguages(),
                $this->menuLogin(),
                $this->menuLogout(),
            ]);
        } else {
            return parent::userMenu($tree);
        }
    }

    /**
     * Create a menu of palette options
     *
     * @return Menu
     */
    protected function menuPalette(): Menu
    {
        /* I18N: A colour scheme */
        $menu = new Menu(I18N::translate('Palette'), '#', 'menu-modern');

        $palette = $this->palette();

        foreach ($this->palettes() as $palette_id => $palette_name) {
            $url = route('module', ['module' => $this->name(), 'action' => 'Palette', 'palette' => $palette_id]);

            $submenu = new Menu(
                $palette_name,
                '#',
                'menu-modern-' . $palette_id . ($palette === $palette_id ? ' active' : ''),
                [
                    'data-wt-post-url' => $url,
                ]
            );

            $menu->addSubmenu($submenu);
        }

        return $menu;
    }

     /**
     * Switch to a new palette
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function postPaletteAction(ServerRequestInterface $request): ResponseInterface
    {
        $user = $request->getAttribute('user');
        assert($user instanceof UserInterface);

        $palette = $request->getQueryParams()['palette'];
        assert(array_key_exists($palette, $this->palettes()));

        $user->setPreference('modern-palette', $palette);

        Session::put('modern-palette', $palette);

        return response();
    }

    /**
     * @return array<string>
     */
    private function palettes(): array
    {
        $palettes = [
            'modern-default'       => I18N::translate('modern-default'),
            'modern-ancestry'       => I18N::translate('modern-ancestry'),
            'bootswatch-cerulean'       => I18N::translate('bootswatch-cerulean'),
            'bootswatch-cosmo'       => I18N::translate('bootswatch-cosmo'),
            'bootswatch-cyborg'       => I18N::translate('bootswatch-cyborg'),
            'bootswatch-darkly'       => I18N::translate('bootswatch-darkly'),
            'bootswatch-flatly'       => I18N::translate('bootswatch-flatly'),
            'bootswatch-journal'       => I18N::translate('bootswatch-journal'),
            'bootswatch-litera'       => I18N::translate('bootswatch-litera'),
            'bootswatch-lumen'       => I18N::translate('bootswatch-lumen'),
            'bootswatch-lux'       => I18N::translate('bootswatch-lux'),
            'bootswatch-materia'       => I18N::translate('bootswatch-materia'),
            'bootswatch-minty'       => I18N::translate('bootswatch-minty'),
            'bootswatch-morph'       => I18N::translate('bootswatch-morph'),
            'bootswatch-pulse'       => I18N::translate('bootswatch-pulse'),
            'bootswatch-quartz'       => I18N::translate('bootswatch-darkquartzly'),
            'bootswatch-sandstone'       => I18N::translate('bootswatch-sandstone'),
            'bootswatch-simplex'       => I18N::translate('bootswatch-simplex'),
            'bootswatch-sketchy'       => I18N::translate('bootswatch-sketchy'),
            'bootswatch-spacelab'       => I18N::translate('bootswatch-spacelab'),
            'bootswatch-superhero'       => I18N::translate('bootswatch-superhero'),
            'bootswatch-united'       => I18N::translate('bootswatch-united'),
            'bootswatch-vapor'       => I18N::translate('bootswatch-vapor'),
            'bootswatch-yeti'       => I18N::translate('bootswatch-yeti'),
            'bootswatch-zephyr'       => I18N::translate('bootswatch-zephyr'),
        ];

        uasort($palettes, I18N::comparator());

        return $palettes;
    }

    /**
     * @return string
     */
    public function palette(): string
    {
        // If we are logged in, use our preference
        $palette = Auth::user()->getPreference('modern-palette', '');

        // If not logged in or no preference, use one we selected earlier in the session.
        if ($palette === '') {
            $palette = Session::get('modern-palette');
            $palette = is_string($palette) ? $palette : '';
        }

        // We haven't selected one this session? Use the site default
        if ($palette === '') {
            $palette = $this->getPreference('palette', 'modern-default');
        }

        return $palette;
    }

    public function customTranslations(string $language): array
    {
        $lang_dir   = $this->resourcesFolder() . 'lang/';
        $extensions = array('mo', 'po');
        foreach ($extensions as &$extension) {
            $file       = $lang_dir . $language . '.' . $extension;
            if (file_exists($file)) {
                return (new Translation($file))->asArray();
            }
        }
        return [];
    }
};