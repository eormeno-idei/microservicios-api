<?php
namespace App\Services\Screens;

use Idei\Usim\Services\UIBuilder;
use Idei\Usim\Services\AbstractUIService;
use Idei\Usim\Services\Components\UIContainer;
use Idei\Usim\Services\Components\InputBuilder;
use Idei\Usim\Services\Components\LabelBuilder;

/**
 * Input Demo Service
 *
 * Demonstrates input component functionality:
 * - Text input with placeholder
 * - Reading input value from frontend
 * - Updating input value from backend
 * - Label updates based on input
 * - Error state with tooltip
 *
 * Uses AbstractUIService for automatic event lifecycle management.
 * Event handlers only need to modify components, no return needed.
 */
class InputDemoService extends AbstractUIService
{
    protected InputBuilder $input_text;
    protected LabelBuilder $lbl_result;

    protected function buildBaseUI(UIContainer $container, ...$params): void
    {
        $container
            ->title('Input Component Demo')
            ->maxWidth('500px')
            ->centerHorizontal()
            ->shadow(2)
            ->padding('30px');

        $container->add(
            UIBuilder::label('lbl_instruction')
                ->text('📝 Enter a name with at least 3 characters and click "Validate"')
                ->style('info')
                ->width('100%')
        );

        $container->add(
            UIBuilder::input('input_text')
                ->label('Your Name')
                ->placeholder('Enter your name here...')
                ->value('')
                ->required(true)
                ->type('text')
                ->width('100%')
        );

        $container->add(
            UIBuilder::button('btn_get_value')
                ->label('Validate')
                ->action('get_value')
                ->style('primary')
        );

        $container->add(
            UIBuilder::label('lbl_result')
                ->text('Result will appear here')
                ->style('default')
                ->width('100%')
        );
    }

    protected function postLoadUI(): void
    {
        $this->input_text->value('')->error(null);
        $this->lbl_result
            ->text('Result will appear here')
            ->style('default');
    }

    /**
     * Handle "Validate" button click
     *
     * Validates the input and shows errors using the error() method with tooltip.
     * No return needed - AbstractUIService handles diff calculation and response.
     *
     * @param array $params Event parameters (should include 'input_text' from input)
     * @return void
     */
    public function onGetValue(array $params): void
    {
        $inputValue = trim($params['input_text'] ?? '');

        // Clear previous error
        $this->input_text->error(null);

        if (empty($inputValue)) {
            $this->displayError('Name is required');
        } elseif (\strlen($inputValue) < 3) {
            $this->displayError('Name must be at least 3 characters');
        } else {
            $this->lbl_result->text("✅ Valid name: \"{$inputValue}\"")->style('success');
        }
    }

    private function displayError(string $message): void
    {
        $this->input_text->error($message);
        $this->toast($message, 'error');
        $this->lbl_result->text('❌ Please fix the error above')->style('danger');
    }
}
