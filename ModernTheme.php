<?php

declare(strict_types=1);

namespace JessewebDotCom\Webtrees\Module\ModernTheme;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Menu;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\View;
use Fisharebest\Webtrees\Session;
use Fisharebest\Webtrees\Individual;
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

use Fisharebest\Webtrees\Age;
use Fisharebest\Webtrees\Date;
use Fisharebest\Webtrees\Family;

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
    public const CUSTOM_VERSION = 'v2.1.16.1';

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
        View::registerCustomView('::login-page', $this->name() . '::login-page');
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
            'copyright_display' => $this->getPreference('copyright-display', '1'),
            'copyrightname' => $this->getPreference('copyrightname', ''),
            'copyrighturl' => $this->getPreference('copyrighturl', ''),

            'about_display' => $this->getPreference('about-display', '1'),
            'about_parents' => $this->getPreference('about-parents', '1'),
            'about_siblings' => $this->getPreference('about-siblings', '1'),
            'about_services' => $this->getPreference('about-services', '1'),
            'about_education' => $this->getPreference('about-education', '1'),
            'about_residences' => $this->getPreference('about-residences', '1'),
            'about_marriage' => $this->getPreference('about-marriage', '1'),
            'about_todayage' => $this->getPreference('about-todayage', '1'),

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

            $this->setPreference('copyright-display', $params['copyright-display']);
            $this->setPreference('copyrightname', $params['copyrightname']);
            $this->setPreference('copyrighturl', $params['copyrighturl']);            

            $this->setPreference('about-display', $params['about-display']);
            $this->setPreference('about-parents', $params['about-parents']);
            $this->setPreference('about-siblings', $params['about-siblings']);
            $this->setPreference('about-services', $params['about-services']);
            $this->setPreference('about-education', $params['about-education']);
            $this->setPreference('about-residences', $params['about-residences']);
            $this->setPreference('about-marriage', $params['about-marriage']);
            $this->setPreference('about-todayage', $params['about-todayage']);

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
        $palette = $this->palette();
        if (empty($palette) || $palette === '') {
            $palette = 'colorful (white-blue-pink)';
        }
        
        return [
            $this->assetUrl('css/palettes/' . (string) $palette . '-min.css'),
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
            if ((bool) $this->getPreference('copyright-display')) {   
                return view($this->name() . '::theme/footer-credits', [
                    'url' => (string) $this->getPreference('copyrighturl'),
                    'text' => '© ' . date("Y") . ' ' . (string)  $this->getPreference('copyrightname'),
                ]);
            }
        } 
        return "";
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

        $parameters2 = [
            'distribution-chart-high-values' => '337ab7', // Statistics charts (palette primary color)
            'distribution-chart-low-values'  => '99e0ff', // Statistics charts (geo chart only, lighten 40%)
            'distribution-chart-no-values'   => 'dee2e6', // Statistics charts ((sass jw-gray-300)
        ];

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
     * @return string
     */
    public function background_color(): string
    {
        $background_color = '#000000';
        switch ($this->palette()) {
            case 'colorful (white-blue-pink)':
                $background_color = '#F8F9FA';
                break;
            case 'light (white-black)':
                $background_color = '#F8F9FA';
                break;
            case 'light (white-purple)':
                $background_color = '#F8F9FA';
                break;
            case 'light (white-orange)':
                $background_color = '#F8F9FA';
                break;
            case 'light (white-pink)':
                $background_color = '#F8F9FA';
                break;
            case 'dark (blue-pink)':
                $background_color = '#171A3F';
                break;
            case 'dark (blue-purple)':
                $background_color = '#171A3F';
                break;
        }

        return $background_color;
    }    

    /**
     * @return string
     */
    public function get_part(string $delim, string $phrase, int $number): string
    {
        $parts = explode($delim, $phrase);
        if (count($parts)===1) {
            return $phrase;
        } elseif ($number < 0) {
            return $parts[count($parts)+$number];
        } else {
            if (count($parts) >= $number-1) {
                return $parts[$number];
            } else {
                return '';
            }
        }
        return '';
    }

    /**
     * @return string
     */
    public function get_state_country(string $place): string
    {
        $death_place_state = $this->get_part(', ', $place, -2);
        $death_place_country = $this->get_part(', ', $place, -1);

        return join(', ', array_unique(array_filter(array($death_place_state, $death_place_country), 'strlen')));
    }    

    /**
     * @return string
     */
    public function get_short_place(string $place): string
    {
        $death_place_name = $this->get_part(', ', $place, 0);
        $death_place_state = $this->get_part(', ', $place, -2);
        $death_place_country = $this->get_part(', ', $place, -1);

        return join(', ', array_unique(array_filter(array($death_place_name, $death_place_state, $death_place_country), 'strlen')));
    }

    /**
     * @return string
     */
    public function join_words(array $words): string
    {
        $string = implode(', ', $words);
        if (count($words) == 1) {
            return $words[0];
        } elseif (count($words) > 1) {
            return substr_replace($string, ' and', strrpos($string, ','), 1);
        } else {
            return '';
        }
    }    

    /**
     * @return string
     */
    public function get_article(string $word): string
    {
        if (in_array(strtolower(substr($word, 0, 1)), array('a','e','i','o','u'))) {
            return 'an';
        } else {
            return 'a';
        }
    }
                        
    /**
     * @return string
     */
    public function individual_about(Individual $record): string
    {
        $about = '';
        if ((bool) $this->getPreference('about-display')) {   
            $today = new Date(strtoupper(date('d M Y')));

            $pronoun = 'they'; $ppronoun = 'their'; $pronoun_verb = 'are';
            if ($record->sex() === 'F') { $pronoun = 'she'; $ppronoun = 'her'; $pronoun_verb = 'is';}
            if ($record->sex() === 'M') { $pronoun = 'he'; $ppronoun = 'his'; $pronoun_verb = 'is';}
            
            $fullname = '<a href=' . $record->url() . '>' . $record->fullName() . '</a>';
            $firstname = $this->get_part(' ', strip_tags($record->fullName()), 0);

            // birth
            $birth_date  = $record->getBirthDate();
            $birth_estimated_date  = $record->getEstimatedBirthDate();
            $birth_place = $record->getBirthPlace();

            if ($birth_date->isOK()) {
                $about = $fullname . ' was born on ' . $birth_date->display();
                if ($birth_place->id() !== 0) {
                    $about = $about . ' in ' . $this->get_short_place($record->getBirthPlace()->gedcomName());
                }
            
                // parents
                if ((bool) $this->getPreference('about-parents')) {   
                    $family = $record->childFamilies()->first();
                    $father_fullname = '';
                    $fathers_age = '';
                    $mother_fullname = '';
                    $mothers_age = '';

                    if ($family instanceof Family) {
                        $father = $family->husband();
                        if ($father) {
                            $father_fullname = '<a href=' . $father->url() . '>' . $father->fullName() . '</a>';
                            if ($father->getBirthDate()->isOK()) {
                                $fathers_age = (string) new Age($father->getBirthDate(), $record->getBirthDate());
                            }
                        }
                        $mother = $family->wife();
                        if ($mother) {
                            $mother_fullname = '<a href=' . $mother->url() . '>' . $mother->fullName() . '</a>';
                            if ($mother->getBirthDate()->isOK()) {
                                $mothers_age = (string) new Age($mother->getBirthDate(), $record->getBirthDate());
                            }
                        }

                        if (strlen( $fathers_age ) !== 0 || strlen( $mothers_age ) !== 0) {
                            $about = 'When ' . $about . ', ';
                        }
                        if (strlen( $fathers_age ) !== 0) {
                            $about = $about . $ppronoun . ' father ' . $father_fullname . ' was ' . $this->get_part(' ', $fathers_age, 0); 
                        }
                        if (strlen( $fathers_age ) !== 0 && strlen( $mothers_age ) !== 0) {
                            $about = $about . ' and ' . $ppronoun . ' mother ' . $mother_fullname . ' was ' . $this->get_part(' ', $mothers_age, 0); 
                        } elseif (strlen( $mothers_age ) !== 0) {
                            $about = $about . $ppronoun . ' mother ' . $mother_fullname . ' was ' . $this->get_part(' ', $mothers_age, 0); 
                        }   
                    }                                           
                }
                $about = $about . '. ';

                // adoption
                if ((bool) $this->getPreference('about-parents')) {  
                    $adoptive_parents = array();
                    foreach ($record->facts() as $fact) {
                        if ($fact->tag()=='INDI:ADOP') {
                            array_push($adoptive_parents, $fact->value());
                        }
                    }
                    if (count($adoptive_parents) > 0) {
                        $about = $about . ucfirst($pronoun) . ' was adopted by ' . $this->join_words($adoptive_parents) . '. ';
                    }
                }

            } elseif ($birth_estimated_date->isOK()) {
                $about = $fullname . ' was born ' . str_replace('estimated', 'around', $birth_estimated_date->display()) . '. ';
            }

            // siblings
            if ((bool) $this->getPreference('about-siblings')) {  
                $sibling_names = array();
                foreach ($record->childFamilies() as $family) {
                    foreach ($family->children() as $child) {
                        if ($child !== $record) {
                            array_push($sibling_names, '<a href=' . $child->url() . '>' . $child->fullName() . '</a>');
                        }
                    }
                }
                $sibling_names = array_unique($sibling_names);
                if (count($sibling_names) == 1) {
                    $about = $about . ucfirst($pronoun) . ' has one sibling (' . $sibling_names[0] . '). ';
                } elseif (count($sibling_names) > 1) {
                    $about = $about . ucfirst($pronoun) . ' has ' . count($sibling_names) . ' siblings (' . $this->join_words($sibling_names) . '). ';
                }
            }

            // education
            if ((bool) $this->getPreference('about-education')) {  
                $schools = array();
                foreach ($record->facts() as $fact) {
                    if ($fact->tag()=='INDI:EDUC') {
                        $school = $fact->value();
                        if (strlen($school)===0) {
                            $school = $this->get_part(', ', $fact->attribute('PLAC'), 0);
                        }
                        array_push($schools, $school);
                    }
                }
                if (count($schools) > 0) {
                    $about = $about . ucfirst($pronoun) . ' attended ' . $this->join_words($schools) . '. ';
                }
            }

            // residences
            if ((bool) $this->getPreference('about-residences')) {  
                $residences = array();
                foreach ($record->facts() as $fact) {
                    if (in_array($fact->tag(), array('INDI:RESI','INDI:EDUC','INDI:OCCU','INDI:BIRT'))) {
                        $residence = $this->get_state_country($fact->attribute('PLAC'));
                        $residence = str_replace(',' ,'|', $residence);
                        array_push($residences, $residence);
                    }
                }
                if (count($residences) > 0) {   
                    $residence_string = str_replace('|', ',', $this->join_words(array_unique(array_filter($residences, 'strlen'))));     
                    if (strlen($residence_string)>0){
                        $about = $about . ucfirst($pronoun) . ' lived in ' . $residence_string . '. ';
                    }            
                }
            }            
                            
            // service and occupations
            if ((bool) $this->getPreference('about-services')) {  
                $services = array();
                $jobs = array();
                $services_jobs = '';
                foreach ($record->facts() as $fact) {
                    if ($fact->tag()=='INDI:_MILT') {
                        array_push($services, $fact->value());
                    }
                    if ($fact->tag()=='INDI:OCCU') {
                        array_push($jobs, $fact->value());
                    }
                }
                if (count($services) > 0) {
                    $services_jobs = $services_jobs . ' served in the ' . $this->join_words($services);
                }
                if (count($jobs) > 0) {
                    if (count($services) > 0) {$services_jobs = $services_jobs . ' and '; }
                    // if (count($jobs) > 2) {                        
                    //     $services_jobs = $services_jobs . ' worked as ' . $this->get_article($jobs[0]) . ' ' . $jobs[0] . ', ' . $jobs[1] . ', and more';
                    // } else {
                    //     $services_jobs = $services_jobs . ' worked as ' . $this->get_article($jobs[0]) . ' ' . $this->join_words($jobs);
                    // }   
                    $services_jobs = $services_jobs . ' worked as ' . $this->get_article($jobs[0]) . ' ' . $this->join_words($jobs);                 
                }
                if ((count($services) > 0) || (count($jobs) > 0)) {
                    $about = $about . ucfirst($pronoun) . $services_jobs . '. ';
                }
            }

            // spouses and children
            if ((bool) $this->getPreference('about-marriage')) {  
                $spouse_count = 0;
                $spouse_verb = ' married ';
                $spouseless_children = array();

                $previous_children = array();
                foreach ($record->spouseFamilies() as $family) {
                    $spouse = $family->spouse($record);
                    if ($spouse) {
                        $spouse_count = $spouse_count + 1;
                        if ($spouse_count > 1) {
                            $spouse_verb = ' then married ';
                        }

                        $about = $about . ucfirst($pronoun) . $spouse_verb . '<a href=' . $spouse->url() . '>' . $spouse->fullName() . '</a>';

                        $child_names = array();
                        foreach ($family->children() as $child) {
                            if ($child !== $record) {
                                if (!in_array($child->url(), $previous_children)) {
                                    array_push($previous_children, $child->url());
                                    array_push($child_names, '<a href=' . $child->url() . '>' . $child->fullName() . '</a>');
                                }                                
                            }
                        }
                        $child_names = array_unique($child_names);
                        if (count($child_names) == 1) {
                            $child_description = ' one child';
                            $child_sex = $family->children()->first()->sex();
                            if ($child_sex === 'F') { $child_description = ' one daughter'; }
                            if ($child_sex === 'M') { $child_description = ' one son'; }
                            
                            $about = $about . ' and they had ' . $child_description . ' (' . $child_names[0] . ')';
                        } elseif (count($child_names) > 1) {
                            $about = $about . ' and they had ' . count($child_names) . ' children (' . $this->join_words($child_names) . ')';
                        }

                        $about = $about . '. '; 
                    } else {
                        foreach ($family->children() as $child) {
                            if ($child !== $record) {
                                if (!in_array($child->url(), $previous_children)) {
                                    array_push($previous_children, $child->url());
                                    array_push($spouseless_children, $child);
                                }                                
                            }
                        }
                    }
                }

                if (count($spouseless_children) == 1) {
                    $child_description = ' one child';
                    if ($spouseless_children[0]->sex() === 'F') { $child_description = ' one daughter'; }
                    if ($spouseless_children[0]->sex() === 'M') { $child_description = ' one son'; }

                    $about = $about . ucfirst($pronoun) . ' has ' . $child_description . ' (' . '<a href=' . $spouseless_children[0]->url() . '>' . $spouseless_children[0]->fullName() . '</a>). ';
                } elseif (count($spouseless_children) > 1) {
                    $about = $about . ucfirst($pronoun) . ' has ' . count($spouseless_children) . ' children (';
                    foreach ($spouseless_children as $child) {
                        $about = $about . '<a href=' . $child->url() . '>' . $child->fullName() . '</a>';
                    }
                    $about = $about . '). '; 
                }

                // grandchildren
                $grandchildren = array();
                $great_grandchildren = array();
                foreach ($record->spouseFamilies() as $family) {
                    // children
                    foreach ($family->children() as $child) {
                        foreach ($child->spouseFamilies() as $childfamily) {
                            foreach ($childfamily->children() as $grandchild) {
                                array_push($grandchildren, $grandchild->url());

                                foreach ($grandchild->spouseFamilies() as $grandchildfamily) {
                                    foreach ($grandchildfamily->children() as $greatgrandchild) {
                                        array_push($great_grandchildren, $greatgrandchild->url());
                                    }
                                }
                            }
                        }
                    }
                }
                $grandchildren = array_unique($grandchildren);
                $great_grandchildren = array_unique($great_grandchildren);
   
                if (count($grandchildren) == 1) {
                    $about = $about . ucfirst($pronoun) . ' has one grandchild';
                } elseif (count($grandchildren) > 1) {
                    $about = $about . ucfirst($pronoun) . ' has '. count($grandchildren) . ' grandchildren';
                }
                if (count($great_grandchildren) == 1) {
                    $about = $about . ' and one great-grandchild ';
                } elseif (count($great_grandchildren) > 1) {
                    $about = $about . ' and '. count($great_grandchildren) . ' great-grandchildren';
                }   
                if (count($grandchildren) > 0) {
                    $about = $about . '. ';
                }           
            }

            // death
            if ($record->isDead()) {
                $death_date  = $record->getDeathDate();
                $death_estimated_date  = $record->getEstimatedDeathDate();
                $death_place = $record->getDeathPlace();

                if ($death_date->isOK()) {
                    $death_time_ago = (string) new Age($record->getDeathDate(), $today);
                    if ($death_time_ago > 0) {
                        $about = $about . $firstname . ' died ' . $death_time_ago . ' ago on ' . $death_date->display();
                    } else {
                        $about = $about . $firstname . ' died on ' . $death_date->display();
                    }

                    if ($death_place->id() !== 0) {
                        $about = $about . ' in ' . $this->get_short_place($record->getDeathPlace()->gedcomName());
                    }
                    if ($birth_date->isOK()) {
                        $death_age = explode(" ", ((string) new Age($record->getBirthDate(), $record->getDeathDate())))[0];                        
                        $about = $about . ' at the age of ' . $death_age . '. ';
    
                        if ((bool) $this->getPreference('about-todayage')) {  
                            $age_today = (string) new Age($record->getBirthDate(), $today);
                            if ($age_today && (int)$death_age < (int)$this->get_part(' ', $age_today, 0))  {
                                $about = $about . ' ' . ucfirst($pronoun) . ' would be ' . trim($age_today) . ' old today. ';
                            }      
                        }                  
                    } else {
                        $about = $about . '. '; 
                    }             
                } elseif ($death_estimated_date->isOK()) {
                    $about = $about . $firstname . ' died ' . str_replace('estimated', 'around', $death_estimated_date->display()) . '. ';
                }
            } else {
                if ((bool) $this->getPreference('about-todayage')) {  
                    $age_today = (string) new Age($record->getBirthDate(), $today);
                    if ($age_today)  {
                        $about = $about . ' ' . ucfirst($pronoun) . ' ' . $pronoun_verb . ' ' . $age_today . ' old. ';
                    }      
                }                  

            }
        }
        
        return (string) I18N::translate($about);
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
