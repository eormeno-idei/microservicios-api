<?php
namespace App\Services\Screens;

use Idei\Usim\Services\AbstractUIService;
use Idei\Usim\Services\Components\ButtonBuilder;
use Idei\Usim\Services\Components\CheckboxBuilder;
use Idei\Usim\Services\Components\LabelBuilder;
use Idei\Usim\Services\Components\UIContainer;
use Idei\Usim\Services\UIBuilder;

class CheckboxDemoService extends AbstractUIService
{
    protected LabelBuilder $lbl_instruction;
    protected CheckboxBuilder $chk_javascript;
    protected CheckboxBuilder $chk_python;
    protected ButtonBuilder $btn_submit;
    protected LabelBuilder $lbl_result;

    // protected bool $store_js_checked = false;
    // protected bool $store_py_checked = false;

    /**
     * Build the checkbox demo UI
     */
    protected function buildBaseUI(UIContainer $container, ...$params): void
    {
        $container
            ->title('Checkbox Component Demo')
            ->maxWidth('500px')
            ->centerHorizontal()
            ->padding('20px')
            ->shadow(2);

        // Instruction label
        $container->add(
            UIBuilder::label('lbl_instruction')
                ->text('Select your preferred programming languages:')
                ->style('info')
        );

        // JavaScript checkbox with onChange handler
        $container->add(
            UIBuilder::checkbox('chk_javascript')
                ->label('JavaScript')
                ->checked(false)
                ->onChange('try_change_javascript') // ← Handler for validation
        );

        // Python checkbox with onChange handler
        $container->add(
            UIBuilder::checkbox('chk_python')
                ->label('Python')
                ->checked(false)
                ->onChange('try_change_python') // ← Handler for validation
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
    }

    /**
     * Handle JavaScript checkbox change attempt
     * Backend validates and confirms or rejects the change
     */
    public function onTryChangeJavascript(array $params): void
    {
        $wantsChecked = $params['checked'] ?? false;

        // // Example validation: you could check any condition here
        // // For now, we'll allow the change
        // $this->store_js_checked = $wantsChecked;

        // Update the checkbox with the confirmed state
        $this->chk_javascript->checked($wantsChecked);

        // Show feedback
        if ($wantsChecked) {
            $this->lbl_result
                ->text('✅ JavaScript selected!')
                ->style('success');
        } else {
            $this->lbl_result
                ->text('ℹ️ JavaScript deselected')
                ->style('info');
        }
    }

    /**
     * Handle Python checkbox change attempt
     * Backend validates and confirms or rejects the change
     */
    public function onTryChangePython(array $params): void
    {
        $wantsChecked = $params['checked'] ?? false;
        $jsChecked    = $this->chk_javascript->isChecked();

        // Example validation: only allow Python if JavaScript is also selected
        // if ($wantsChecked && !$this->store_js_checked) {
        if ($wantsChecked && ! $jsChecked) {
                                               // ❌ REJECT: Don't allow Python without JavaScript
            $this->chk_python->checked(false); // Keep it unchecked
            $this->lbl_result
                ->text('❌ You must select JavaScript first before selecting Python!')
                ->style('danger');
            $this->toast('You must select JavaScript first before selecting Python!', type: 'error');
            return;
        }

        // ✅ APPROVE: Allow the change
        // $this->store_py_checked = $wantsChecked;
        $this->chk_python->checked($wantsChecked);

        if ($wantsChecked) {
            $this->lbl_result
                ->text('✅ Python selected!')
                ->style('success');
        } else {
            $this->lbl_result
                ->text('ℹ️ Python deselected')
                ->style('info');
        }
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

        // $this->store_js_checked = $jsChecked;
        // $this->store_py_checked = $pyChecked;

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

        $this->toast("Submitted! Your selections: {$languagesList}", type: 'success');
    }
}
