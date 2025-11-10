<?php

namespace App\Services\Screens;

use App\Services\UI\UIBuilder;
use App\Services\UI\Enums\LayoutType;
use App\Services\UI\AbstractUIService;
use App\Services\UI\Components\UIContainer;
use App\Services\UI\Components\InputBuilder;
use App\Services\UI\Components\LabelBuilder;
use App\Services\UI\Components\ButtonBuilder;

class FormDemoService extends AbstractUIService
{
    protected LabelBuilder $lbl_instruction;
    protected InputBuilder $input_name;
    protected InputBuilder $input_email;
    protected ButtonBuilder $btn_submit;
    protected LabelBuilder $lbl_result;

    protected function buildBaseUI(UIContainer $container, ...$params): void
    {
        $container->title('Form Component Demo');

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
        );

        $container->add(
            UIBuilder::input('input_email')
                ->label('Email')
                ->placeholder('Enter your email')
                ->value('')
                ->required(true)
                ->type('email')
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

    /**
     * Handle form submission with validation
     * Reads input values from frontend parameters (sent by collectContextValues)
     */
    public function onSubmitForm(array $params): void
    {
        // Get input values from frontend parameters (sent by collectContextValues)
        $name = trim($params['input_name'] ?? '');
        $email = trim($params['input_email'] ?? '');

        // Validation errors array
        $errors = [];

        // Validate name
        if (empty($name)) {
            $errors[] = 'Name is required';
        } elseif (strlen($name) < 2) {
            $errors[] = 'Name must be at least 2 characters';
        }

        // Validate email
        if (empty($email)) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email is invalid';
        }

        // Show errors or success
        if (!empty($errors)) {
            $errorMessage = "❌ Validation errors:\n" . implode("\n", array_map(fn($e) => "  • $e", $errors));
            
            $this->lbl_result
                ->text($errorMessage)
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
