<?php

namespace App\Services\Screens;

use App\Services\UI\AbstractUIService;
use App\Services\UI\Components\UIContainer;
use App\Services\UI\Enums\LayoutType;
use App\Services\UI\UIBuilder;
use App\Services\UI\Components\LabelBuilder;
use App\Services\UI\Components\CheckboxBuilder;
use App\Services\UI\Components\ButtonBuilder;

class CheckboxDemoService extends AbstractUIService
{
    // Component references (auto-injected)
    protected LabelBuilder $lbl_instruction;
    protected CheckboxBuilder $chk_javascript;
    protected CheckboxBuilder $chk_python;
    protected ButtonBuilder $btn_submit;
    protected LabelBuilder $lbl_result;

    /**
     * Build the checkbox demo UI
     */
    protected function buildBaseUI(...$params): UIContainer
    {
        $container = UIBuilder::container('main')
            ->parent('main')
            ->layout(LayoutType::VERTICAL)
            ->title('Checkbox Component Demo');

        // Instruction label
        $container->add(
            UIBuilder::label('lbl_instruction')
                ->text('Select your preferred programming languages:')
                ->style('info')
        );

        // JavaScript checkbox
        $container->add(
            UIBuilder::checkbox('chk_javascript')
                ->label('JavaScript')
                ->checked(false)
        );

        // Python checkbox
        $container->add(
            UIBuilder::checkbox('chk_python')
                ->label('Python')
                ->checked(false)
        );

        // Submit button
        $container->add(
            UIBuilder::button('btn_submit')
                ->label('Submit Selection')
                ->action('submit_selection')
                ->style('primary')
        );

        // Result label
        $container->add(
            UIBuilder::label('lbl_result')
                ->text('Make your selection above')
                ->style('secondary')
        );

        return $container;
    }

    /**
     * Handle form submission
     * Reads checkbox states from frontend parameters
     */
    public function onSubmitSelection(array $params): void
    {
        // Get checkbox states from frontend parameters (sent by collectContextValues)
        $jsChecked = $params['chk_javascript'] ?? false;
        $pyChecked = $params['chk_python'] ?? false;

        // Build selections array
        $selections = [];
        
        if ($jsChecked) {
            $selections[] = 'JavaScript';
        }
        if ($pyChecked) {
            $selections[] = 'Python';
        }

        // Validate minimum selection
        if (empty($selections)) {
            $this->lbl_result
                ->text('❌ Error: You must select at least one language')
                ->style('danger');
            return;
        }

        // Success message
        $languagesList = implode(', ', $selections);
        $this->lbl_result
            ->text("✅ Submitted! Your selections: {$languagesList}")
            ->style('success');
    }
}
