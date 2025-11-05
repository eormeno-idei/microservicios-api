<?php

namespace App\Services\Screens;

use App\Services\UI\UIBuilder;
use App\Services\UI\Enums\LayoutType;
use App\Services\UI\AbstractUIService;
use App\Services\UI\Components\UIContainer;
use App\Services\UI\Components\SelectBuilder;
use App\Services\UI\Components\LabelBuilder;
use App\Services\UI\Components\CheckboxBuilder;
use App\Services\UI\Components\ButtonBuilder;

/**
 * Select Demo Service
 * 
 * Demonstrates select component functionality:
 * - Cascading selects (country → city)
 * - Dynamic option updates
 * - Conditional enabling/disabling
 * - Single and multiple selection
 * - Searchable selects
 * - Value retrieval and display
 * 
 * Uses AbstractUIService for automatic event lifecycle management.
 * Event handlers only need to modify components, no return needed.
 */
class SelectDemoService extends AbstractUIService
{
    protected SelectBuilder $sel_country;
    protected SelectBuilder $sel_city;
    protected SelectBuilder $sel_languages;
    protected LabelBuilder $lbl_result;
    protected CheckboxBuilder $chk_enable_multiple;
    protected ButtonBuilder $btn_reset;

    /**
     * Country and city data
     */
    private const COUNTRIES = [
        ['value' => 'us', 'label' => '🇺🇸 United States'],
        ['value' => 'es', 'label' => '🇪🇸 Spain'],
        ['value' => 'fr', 'label' => '🇫🇷 France'],
        ['value' => 'jp', 'label' => '🇯🇵 Japan'],
        ['value' => 'br', 'label' => '🇧🇷 Brazil'],
    ];

    private const CITIES = [
        'us' => [
            ['value' => 'ny', 'label' => 'New York'],
            ['value' => 'la', 'label' => 'Los Angeles'],
            ['value' => 'chicago', 'label' => 'Chicago'],
            ['value' => 'miami', 'label' => 'Miami'],
        ],
        'es' => [
            ['value' => 'madrid', 'label' => 'Madrid'],
            ['value' => 'barcelona', 'label' => 'Barcelona'],
            ['value' => 'valencia', 'label' => 'Valencia'],
            ['value' => 'sevilla', 'label' => 'Sevilla'],
        ],
        'fr' => [
            ['value' => 'paris', 'label' => 'Paris'],
            ['value' => 'marseille', 'label' => 'Marseille'],
            ['value' => 'lyon', 'label' => 'Lyon'],
            ['value' => 'toulouse', 'label' => 'Toulouse'],
        ],
        'jp' => [
            ['value' => 'tokyo', 'label' => 'Tokyo'],
            ['value' => 'osaka', 'label' => 'Osaka'],
            ['value' => 'kyoto', 'label' => 'Kyoto'],
            ['value' => 'yokohama', 'label' => 'Yokohama'],
        ],
        'br' => [
            ['value' => 'sao_paulo', 'label' => 'São Paulo'],
            ['value' => 'rio', 'label' => 'Rio de Janeiro'],
            ['value' => 'brasilia', 'label' => 'Brasília'],
            ['value' => 'salvador', 'label' => 'Salvador'],
        ],
    ];

    private const CITY_INFO = [
        'ny' => ['country' => 'United States', 'population' => '8.3M', 'timezone' => 'EST'],
        'la' => ['country' => 'United States', 'population' => '3.9M', 'timezone' => 'PST'],
        'chicago' => ['country' => 'United States', 'population' => '2.7M', 'timezone' => 'CST'],
        'miami' => ['country' => 'United States', 'population' => '467K', 'timezone' => 'EST'],
        'madrid' => ['country' => 'Spain', 'population' => '3.2M', 'timezone' => 'CET'],
        'barcelona' => ['country' => 'Spain', 'population' => '1.6M', 'timezone' => 'CET'],
        'valencia' => ['country' => 'Spain', 'population' => '791K', 'timezone' => 'CET'],
        'sevilla' => ['country' => 'Spain', 'population' => '688K', 'timezone' => 'CET'],
        'paris' => ['country' => 'France', 'population' => '2.1M', 'timezone' => 'CET'],
        'marseille' => ['country' => 'France', 'population' => '870K', 'timezone' => 'CET'],
        'lyon' => ['country' => 'France', 'population' => '513K', 'timezone' => 'CET'],
        'toulouse' => ['country' => 'France', 'population' => '471K', 'timezone' => 'CET'],
        'tokyo' => ['country' => 'Japan', 'population' => '13.9M', 'timezone' => 'JST'],
        'osaka' => ['country' => 'Japan', 'population' => '2.7M', 'timezone' => 'JST'],
        'kyoto' => ['country' => 'Japan', 'population' => '1.5M', 'timezone' => 'JST'],
        'yokohama' => ['country' => 'Japan', 'population' => '3.7M', 'timezone' => 'JST'],
        'sao_paulo' => ['country' => 'Brazil', 'population' => '12.3M', 'timezone' => 'BRT'],
        'rio' => ['country' => 'Brazil', 'population' => '6.7M', 'timezone' => 'BRT'],
        'brasilia' => ['country' => 'Brazil', 'population' => '3.0M', 'timezone' => 'BRT'],
        'salvador' => ['country' => 'Brazil', 'population' => '2.9M', 'timezone' => 'BRT'],
    ];

    private const LANGUAGES = [
        ['value' => 'en', 'label' => 'English'],
        ['value' => 'es', 'label' => 'Spanish'],
        ['value' => 'fr', 'label' => 'French'],
        ['value' => 'de', 'label' => 'German'],
        ['value' => 'it', 'label' => 'Italian'],
        ['value' => 'pt', 'label' => 'Portuguese'],
        ['value' => 'ja', 'label' => 'Japanese'],
        ['value' => 'zh', 'label' => 'Chinese'],
    ];

    protected function buildBaseUI(...$params): UIContainer
    {
        $container = UIBuilder::container('main')
            ->parent('main')
            ->layout(LayoutType::VERTICAL)
            ->title('Select Component Demo');

        // Instruction label
        $container->add(
            UIBuilder::label('lbl_instruction')
                ->text('🌍 Select a country to see available cities, then select a city to see details')
                ->style('info')
        );

        // Country select
        $container->add(
            UIBuilder::select('sel_country')
                ->label('Select Country')
                ->placeholder('Choose a country...')
                ->options(self::COUNTRIES)
                ->value(null)
                ->required(true)
                ->onChange('country_change')
                ->style('primary')
        );

        // City select (initially disabled)
        $container->add(
            UIBuilder::select('sel_city')
                ->label('Select City')
                ->placeholder('First select a country')
                ->options([])
                ->value(null)
                ->disabled(true)
                ->onChange('city_change')
                ->style('primary')
        );

        // Checkbox to enable multiple language selection
        $container->add(
            UIBuilder::checkbox('chk_enable_multiple')
                ->label('Enable multiple language selection')
                ->checked(false)
                ->onChange('toggle_multiple_languages')
        );

        // Languages select (searchable and optionally multiple)
        $container->add(
            UIBuilder::select('sel_languages')
                ->label('Select Language(s)')
                ->placeholder('Choose language(s)...')
                ->options(self::LANGUAGES)
                ->value(null)
                ->searchable(true, 'Search languages...')
                ->multiple(false)
                ->onChange('language_change')
                ->style('info')
        );

        // Result label
        $container->add(
            UIBuilder::label('lbl_result')
                ->text('Select options above to see results')
                ->style('default')
        );

        // Reset button
        $container->add(
            UIBuilder::button('btn_reset')
                ->label('Reset All')
                ->action('reset_selections')
                ->icon('refresh')
                ->style('secondary')
        );

        return $container;
    }

    /**
     * Handle country selection change
     * Updates city select with cities from selected country
     * 
     * @param array $params Contains 'value' with selected country code
     * @return void
     */
    public function onCountryChange(array $params): void
    {
        $countryCode = $params['value'] ?? null;

        if (empty($countryCode)) {
            // No country selected - disable city select
            $this->sel_city
                ->options([])
                ->value(null)
                ->disabled(true)
                ->placeholder('First select a country');
            
            $this->lbl_result
                ->text('Select a country to continue')
                ->style('default');
        } else {
            // Country selected - enable city select with options
            $cities = self::CITIES[$countryCode] ?? [];
            
            $this->sel_city
                ->options($cities)
                ->value(null)
                ->disabled(false)
                ->placeholder('Choose a city...');
            
            $countryName = collect(self::COUNTRIES)
                ->firstWhere('value', $countryCode)['label'] ?? $countryCode;
            
            $this->lbl_result
                ->text("✅ Country selected: {$countryName}. Now select a city.")
                ->style('success');
        }
    }

    /**
     * Handle city selection change
     * Displays city information
     * 
     * @param array $params Contains 'value' with selected city code
     * @return void
     */
    public function onCityChange(array $params): void
    {
        $cityCode = $params['value'] ?? null;

        if (empty($cityCode)) {
            $this->lbl_result
                ->text('Select a city to see details')
                ->style('default');
        } else {
            $info = self::CITY_INFO[$cityCode] ?? null;
            
            if ($info) {
                $cityName = collect(array_merge(...array_values(self::CITIES)))
                    ->firstWhere('value', $cityCode)['label'] ?? $cityCode;
                
                $text = "📍 {$cityName}, {$info['country']}\n";
                $text .= "👥 Population: {$info['population']}\n";
                $text .= "🕐 Timezone: {$info['timezone']}";
                
                $this->lbl_result
                    ->text($text)
                    ->style('success');
            } else {
                $this->lbl_result
                    ->text("City information not available")
                    ->style('warning');
            }
        }
    }

    /**
     * Handle language selection change
     * Displays selected language(s)
     * 
     * @param array $params Contains 'value' with selected language code(s)
     * @return void
     */
    public function onLanguageChange(array $params): void
    {
        $value = $params['value'] ?? null;

        if (empty($value)) {
            return; // Don't update result for language changes
        }

        // Get current result text using the public get() method
        $currentResult = $this->lbl_result->get('text', '');
        
        if (is_array($value)) {
            // Multiple languages selected
            $languageNames = collect(self::LANGUAGES)
                ->whereIn('value', $value)
                ->pluck('label')
                ->join(', ');
            
            $languageText = "\n🗣️ Languages: {$languageNames}";
        } else {
            // Single language selected
            $languageName = collect(self::LANGUAGES)
                ->firstWhere('value', $value)['label'] ?? $value;
            
            $languageText = "\n🗣️ Language: {$languageName}";
        }

        // Only add language info if there's already city info
        if (str_contains($currentResult, '📍')) {
            $this->lbl_result->text($currentResult . $languageText);
        }
    }

    /**
     * Toggle multiple language selection mode
     * 
     * @param array $params Contains 'checked' boolean
     * @return void
     */
    public function onToggleMultipleLanguages(array $params): void
    {
        $enableMultiple = $params['checked'] ?? false;

        if ($enableMultiple) {
            $this->sel_languages
                ->multiple(true, 3) // Allow up to 3 selections
                ->placeholder('Choose up to 3 languages...')
                ->value([]);
        } else {
            $this->sel_languages
                ->multiple(false)
                ->placeholder('Choose a language...')
                ->value(null);
        }
    }

    /**
     * Reset all selections
     * 
     * @param array $params Event parameters
     * @return void
     */
    public function onResetSelections(array $params): void
    {
        $this->sel_country->value(null);
        
        $this->sel_city
            ->options([])
            ->value(null)
            ->disabled(true)
            ->placeholder('First select a country');
        
        $this->sel_languages
            ->value(null)
            ->multiple(false)
            ->placeholder('Choose a language...');
        
        $this->chk_enable_multiple->checked(false);
        
        $this->lbl_result
            ->text('All selections have been reset. Start over!')
            ->style('info');
    }
}
