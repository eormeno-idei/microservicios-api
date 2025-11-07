<?php

namespace App\Services\Screens;

use App\Services\UI\UIBuilder;
use App\Services\UI\Enums\LayoutType;
use App\Services\UI\AbstractUIService;
use App\Services\UI\Components\UIContainer;
use App\Services\UI\Components\LabelBuilder;
use App\Services\UI\Components\ButtonBuilder;
use App\Services\UI\Components\CheckboxBuilder;

class CheckboxDemoService extends AbstractUIService
{
    protected LabelBuilder $lbl_instruction;
    protected CheckboxBuilder $chk_javascript;
    protected CheckboxBuilder $chk_python;
    protected ButtonBuilder $btn_submit;
    protected LabelBuilder $lbl_result;

    protected bool $store_js_checked = false;
    protected bool $store_py_checked = false;

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
                ->checked($this->store_js_checked)
        );

        // Python checkbox
        $container->add(
            UIBuilder::checkbox('chk_python')
                ->label('Python')
                ->checked($this->store_py_checked)
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

        $this->store_js_checked = $jsChecked;
        $this->store_py_checked = $pyChecked;

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
