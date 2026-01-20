<?php
namespace App\Services\Screens;

use Idei\Usim\Services\UIBuilder;
use Idei\Usim\Services\AbstractUIService;
use Idei\Usim\Services\Components\UIContainer;
use Idei\Usim\Services\Components\InputBuilder;
use Idei\Usim\Services\Components\LabelBuilder;
use Idei\Usim\Services\Components\ButtonBuilder;

class FormDemoService extends AbstractUIService
{
    protected LabelBuilder $lbl_instruction;
    protected InputBuilder $input_name;
    protected InputBuilder $input_email;
    protected ButtonBuilder $btn_submit;
    protected LabelBuilder $lbl_result;

    protected function buildBaseUI(UIContainer $container, ...$params): void
    {
        $container
            ->title('Form Component Demo')
            ->maxWidth('500px')
            ->centerHorizontal()
            ->shadow(2)
            ->padding('30px');

        $container->add(
            UIBuilder::label('lbl_instruction')
                ->text('Fill out the form below (all fields are required):')
                ->style('info')
        );

        $container->add(
            UIBuilder::input('input_name')
                ->label('Name')
                ->placeholder('Enter your name')
                ->value('')
                ->required(true)
                ->type('text')
                ->width('100%')
        );

        $container->add(
            UIBuilder::input('input_email')
                ->label('Email')
                ->placeholder('Enter your email')
                ->value('')
                ->required(true)
                ->type('email')
                ->width('100%')
        );

        $container->add(
            UIBuilder::button('btn_submit')
                ->label('Submit Form')
                ->action('submit_form')
                ->style('primary')
        );

        $container->add(
            UIBuilder::label('lbl_result')
                ->text('Fill the form to continue')
                ->style('secondary')
        );
    }

    protected function postLoadUI(): void
    {
        $this->input_name->value("")->error(null);
        $this->input_email->value("")->error(null);
        $this->lbl_result
            ->text('Fill the form to continue')
            ->style('secondary');
    }

    /**
     * Handle form submission with validation
     * Reads input values from frontend parameters (sent by collectContextValues)
     */
    public function onSubmitForm(array $params): void
    {
        // Get input values from frontend parameters (sent by collectContextValues)
        $name  = trim($params['input_name'] ?? '');
        $email = trim($params['input_email'] ?? '');

        // Clear previous errors
        $this->input_name->error(null);
        $this->input_email->error(null);

        // Validation flags
        $hasErrors = false;

        // Validate name
        if (empty($name)) {
            $this->input_name->error('Name is required');
            $hasErrors = true;
        } elseif (strlen($name) < 2) {
            $this->input_name->error('Name must be at least 2 characters');
            $hasErrors = true;
        }

        // Validate email
        if (empty($email)) {
            $this->input_email->error('Email is required');
            $hasErrors = true;
        } elseif (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->input_email->error('Email is invalid');
            $hasErrors = true;
        }

        // Show result
        if ($hasErrors) {
            $this->lbl_result
                ->text('❌ Please fix the errors above')
                ->style('danger');
        } else {
            $this->lbl_result
                ->text("✅ Form submitted successfully!\n\nName: {$name}\nEmail: {$email}")
                ->style('success');

            // Clear form inputs after successful submission
            $this->input_name->value('');
            $this->input_email->value('');
        }
    }
}
