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

    /**
     * @var string
     */
    public const CUSTOM_AUTHOR = 'JessewebDotCom';

    /**
     * @var string
     */
    public const CUSTOM_VERSION = '1.0';

    /**
     * @var string
     */
    public const GITHUB_REPO = 'webtrees-theme-modern';

    /**
     * @var string
     */
    public const AUTHOR_WEBSITE = 'jesseweb.com';

     /**
     * @var string
     */
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
        return 'https://github.com/JesseWebDotCom/webtrees-theme-modern/releases/latest';
    }

    /**
     * Fetch the latest version of this module.
     *
     * @return string
     */
    public function customModuleLatestVersion(): string
    {
        return 'https://github.com/JesseWebDotCom/webtrees-theme-modern/releases/latest';
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

        // Add all javascript used by this module in a view
        // View($this->name() . '::theme/script.js');

        // Replace an existing view with our own version.

        /**
         * Global
         */
        View::registerCustomView('::layouts/default', $this->name() . '::layouts/default'); // Site template

        /**
        * Custom
        */
        View::registerCustomView('::modules/family_nav/sidebar-family', $this->name() . '::modules/family_nav/sidebar-family');
        View::registerCustomView('::individual-page', $this->name() . '::individual-page');
        View::registerCustomView('::individual-page-menu', $this->name() . '::individual-page-menu');
        View::registerCustomView('::individual-page-names', $this->name() . '::individual-page-names');
        View::registerCustomView('::individual-page-images', $this->name() . '::individual-page-images');
        View::registerCustomView('::fact', $this->name() . '::fact');
        View::registerCustomView('::fact-sources', $this->name() . '::fact-sources');
        View::registerCustomView('::modules/personal_facts/tab', $this->name() . '::modules/personal_facts/tab');
        View::registerCustomView('::fact-place', $this->name() . '::fact-place');
        View::registerCustomView('::modules/random_media/slide-show', $this->name() . '::modules/random_media/slide-show');
 
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
            'allow_switch' => $this->getPreference('allow-switch', '0'),
            'palette'      => $this->getPreference('palette', 'modern'),
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
        return [
            $this->assetUrl('css/palettes/' . $this->palette() . '-min.css'),
            $this->assetUrl('css/base-min.css'),
            $this->assetUrl('css/custom-min.css')
        ];
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
                'text' => '© ' . date("Y") . ' ' . self::AUTHOR_WEBSITE,
            ]);
        } else {
            return "";
        }
    }


    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleThemeInterface::stylesheets()
     * Usage: FanChartModule and Statistics charts
     */
    public function parameter($parameter_name)
    {
        $parameters1 = [
            'chart-background-f'             => 'fff0f5', // FanChart
            'chart-background-m'             => 'd7eaf9', // Fanchart
            'chart-background-u'             => 'f9f9f9', // Fanchart
            'chart-box-x'                    => 260, // unused
            'chart-box-y'                    => 85, // unused
            'chart-font-color'               => '212529', // FanChart
            'chart-spacing-x'                => 5, // unused
            'chart-spacing-y'                => 10, // unused
            'compact-chart-box-x'            => 240, // unused
            'compact-chart-box-y'            => 50, // unused
        ];

        if ($this->palette() === 'justblack') {
            $parameters2 = [
                'distribution-chart-high-values' => 'd86400', // Statistics charts (palette primary color)
                'distribution-chart-low-values'  => 'ffca66', // Statistics charts (geo chart only, lighten 40%)
                'distribution-chart-no-values'   => 'dee2e6', // Statistics charts (sass jw-gray-300)
            ];
        } else {
            $parameters2 = [
                'distribution-chart-high-values' => '337ab7', // Statistics charts (palette primary color)
                'distribution-chart-low-values'  => '99e0ff', // Statistics charts (geo chart only, lighten 40%)
                'distribution-chart-no-values'   => 'dee2e6', // Statistics charts ((sass jw-gray-300)
            ];
        }

        $parameters = array_merge($parameters1, $parameters2);

        return $parameters[$parameter_name];
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
            'colorful (white-blue-pink)'       => I18N::translate('colorful (white-blue-pink)'),
            'light (white-black)'       => I18N::translate('light (white-black)'),
            'light (white-purple)'       => I18N::translate('light (white-purple)'),
            'light (white-orange)'       => I18N::translate('light (white-orange)'),
            'light (white-pink)'       => I18N::translate('light (white-pink)'),
            'dark (blue-pink)'       => I18N::translate('dark (blue-pink)'),
            'dark (blue-purple)'       => I18N::translate('dark (blue-purple)')
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
        if (empty($palette) || $palette === '') {
            $palette = Session::get('modern-palette');
            $palette = is_string($palette) ? $palette : '';
        }

        // if session value is not valid, set a default palette
        if (!in_array($palette, $this->palettes())) {
            $this->setPreference('palette', 'colorful (white-blue-pink)');
        }

        // We haven't selected one this session? Use the site default
        if (empty($palette) || $palette === '') {
            $palette = $this->getPreference('palette', 'colorful (white-blue-pink)');
        }

        return $palette;
        
    }

    /**
     * Additional/updated translations.
     *
     * @param string $language
     *
     * @return array<string>
     */
    public function customTranslations(string $language): array
    {
        $file = $this->resourcesFolder() . 'lang/' . $language . '.php';

        return file_exists($file) ? (new Translation($file))->asArray() : [];
    }
};
