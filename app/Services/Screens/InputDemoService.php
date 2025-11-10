<?php

namespace App\Services\Screens;

use App\Services\UI\UIBuilder;
use App\Services\UI\Enums\LayoutType;
use App\Services\UI\AbstractUIService;
use App\Services\UI\Components\UIContainer;
use App\Services\UI\Components\LabelBuilder;

/**
 * Input Demo Service
 * 
 * Demonstrates input component functionality:
 * - Text input with placeholder
 * - Reading input value from frontend
 * - Updating input value from backend
 * - Label updates based on input
 * 
 * Uses AbstractUIService for automatic event lifecycle management.
 * Event handlers only need to modify components, no return needed.
 */
class InputDemoService extends AbstractUIService
{
    protected LabelBuilder $lbl_result;

    protected function buildBaseUI(UIContainer $container, ...$params): void
    {
        $container->title('Input Component Demo');

        $container->add(
            UIBuilder::label('lbl_instruction')
                ->text('📝 Type something in the input below and click "Get Value"')
                ->style('info')
        );

        $container->add(
            UIBuilder::input('input_text')
                ->placeholder('Enter your text here...')
                ->value('')
                ->required(false)
        );

        $container->add(
            UIBuilder::button('btn_get_value')
                ->label('Get Value')
                ->action('get_value')
                ->style('primary')
        );

        $container->add(
            UIBuilder::label('lbl_result')
                ->text('Result will appear here')
                ->style('default')
        );
    }

    /**
     * Handle "Get Value" button click
     * 
     * Reads the input value sent from frontend and displays it in the result label.
     * No return needed - AbstractUIService handles diff calculation and response.
     * 
     * @param array $params Event parameters (should include 'input_text' from input)
     * @return void
     */
    public function onGetValue(array $params): void
    {
        $inputValue = $params['input_text'] ?? '';
        
        if (empty($inputValue)) {
            $this->lbl_result->text('⚠️ Input is empty!')->style('warning');
        } else {
            $this->lbl_result->text("✅ You typed: \"$inputValue\"")->style('success');
        }
    }
}
