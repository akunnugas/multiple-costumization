<?php

namespace Modules\Admission\Livewire;

class Login extends FrontComponent
{
    protected $view = 'admission::livewire.login';
    protected array $queryString = ['activePage'];

    // active page
    public string $activePage = 'login';
    // for breadcrumb
    public array $parentNav = [];

    // input fields
    public array $formFields;
    public string $email;
    public string $password;

    // text in page:
    public string $title;
    public string $description;
    public string $copySubmitButton;
    public string $copyBackPage;
    public string $changeActivePage;
    public string $routeActionForm;

    // other state
    public array $alert = [];

    /**
     * Render view.
     *
     * @return mixed
     */
    public function render()
    {
        if ($this->activePage == 'forgot-password') {
            $this->title = __('admission::auth.forgot_password');
            $this->description = __('admission::auth.instructor_to_fill_email_for_register');
            $this->copySubmitButton = __('admission::auth.send');
            $this->copyBackPage = __('admission::auth.back_to_login');
            $this->changeActivePage = 'login';
            $this->routeActionForm = 'admission.forgot-password';
        } else {
            $this->title = __('admission::auth.title');
            $this->description = __('admission::auth.description');
            $this->copySubmitButton = __('admission::auth.sign_in');
            $this->copyBackPage = __('admission::auth.forgot_password') . '?';
            $this->changeActivePage = 'forgot-password';
            $this->routeActionForm = 'admission.login';
        }

        // set form fields
        $this->formFields = $this->defineFormFields();

        // handle error
        $this->handleError();

        // display view
        return $this->buildView($this->view);
    }

    /**
     * Update active page.
     *
     * @param $state
     * @return void
     */
    public function updateActivePage($state)
    {
        $this->activePage = $state;
        $this->render();
    }

    /**
     * Handle and set state error.
     *
     * @return void
     */
    private function handleError()
    {
        // jika ada errors/error session (dari controller)
        if (session()->has('error')) {
            $this->setError(session('error'));
        }

        if (session()->has('errors')) {
            $this->setError(session('errors')->first());
        }
    }

    /**
     * Set error to show alert in form login.
     *
     * @param $message
     * @return array
     */
    private function setError($message)
    {
        return $this->alert = [
            'message' => $message,
            'type' => 'error'
        ];
    }

    /**
     * Define form fields.
     *
     * @return array[]
     */
    private function defineFormFields()
    {
        if ($this->activePage == 'forgot-password') {
            return [
                ['field' => 'email', 'required' => true, 'type' => 'email', 'placeholder' => 'Masukkan alamat email',
                    'label' => 'Alamat Email', 'wire:model' => 'email'
                ],
            ];
        } else {
            return [
                ['field' => 'email', 'required' => true, 'type' => 'email', 'placeholder' => 'Masukkan alamat email',
                    'label' => 'Alamat Email', 'wire:model' => 'email'
                ],
                ['field' => 'password', 'required' => true, 'type' => 'password', 'placeholder' => 'Masukkan password',
                    'label' => 'Password', 'wire:model' => 'password'
                ],
            ];
        }
    }
}
