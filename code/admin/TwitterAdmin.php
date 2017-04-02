<?php

class TwitterAdmin extends ModelAdmin
{
    public static $dependencies = [
        'twitterService' => '%$TwitterService',
    ];

    /**
     * @var TwitterService
     */
    public $twitterService;

    /**
     * @var string
     */
    private static $url_segment = 'twitter';

    /**
     * @var string
     */
    private static $menu_icon = 'silverstripe-twitter/images/twitter-logo.png';

    /**
     * @var string
     */
    private static $menu_title = 'Twitter';

    /**
     * @var array
     */
    private static $managed_models = [
        'TwitterAccount',
    ];

    /**
     * @var array
     */
    private static $allowed_actions = [
        'ImportForm',
        'SearchForm',
        'OAuth',
    ];

    /**
     * @param int $id
     *
     * @param FieldList $fields
     *
     * @return Form
     */
    public function getEditForm($id = null, $fields = null)
    {
        $form = parent::getEditForm($id, $fields);

        $gridFieldName = $this->sanitiseClassName($this->modelClass);
        $gridFieldConfig = $form->Fields()->fieldByName($gridFieldName)->getConfig();

        $gridFieldConfig->removeComponentsByType('GridFieldPrintButton');
        $gridFieldConfig->removeComponentsByType('GridFieldExportButton');
        $gridFieldConfig
            ->getComponentByType('GridFieldAddNewButton')
            ->setButtonName(_t('Twitter.ButtonLabelAddAccount', 'Add account'));
        $gridFieldConfig
            ->getComponentByType('GridFieldDetailForm')
            ->setItemRequestClass('TwitterAdminRequestHandler');

        return $form;
    }

    /**
     * Handles failed OAuth attempts.
     *
     * @param string $url
     *
     * @param Form $form
     *
     * @return Controller
     */
    public function handleOAuthError($url, $form, $message = null)
    {
        $message = $message ? $message : _t(
            'Twitter.MessageOAuthErrorResponse',
            'Unable to authorise account. Please try again.'
        );

        $form->sessionMessage($message, 'bad');

        user_error($message, E_USER_WARNING);

        return Controller::curr()->redirect($url);
    }

    /**
     * OAuth callback handler.
     *
     * @param SS_HTTPRequest $request
     */
    public function OAuth($request)
    {
        $denied = $request->getVar('denied');
        $token = $request->getVar('oauth_token');
        $verifier = $request->getVar('oauth_verifier');

        $sessionRequestToken = $this->twitterService->getSessionOAuthToken();

        $this->twitterService->clearSessionOAuthToken();

        $form = $this->getEditForm();

        if (
            $denied ||
            !$token ||
            !$verifier ||
            !$sessionRequestToken ||
            !array_key_exists('oauth_token', $sessionRequestToken) ||
            !array_key_exists('oauth_token_secret', $sessionRequestToken) ||
            $token !== $sessionRequestToken['oauth_token']
        ) {
            return $this->handleOAuthError($this->Link(), $form);
        }

        $accessToken = $this->twitterService->getAccessToken(
            $sessionRequestToken['oauth_token'],
            $sessionRequestToken['oauth_token_secret'],
            $verifier
        );

        if (!$accessToken) {
            return $this->handleOAuthError($this->Link(), $form);
        }

        $twitterAccount = TwitterAccount::get()
            ->filter(['Title' => $accessToken['screen_name']])
            ->first();

        if (!$twitterAccount) {
            return $this->handleOAuthError($this->Link(), $form);
        }

        $twitterAccount->setAccessToken($accessToken);
        $twitterAccount->write();

        $form->sessionMessage(_t('Twitter.MessageOAuthSuccess', 'Successfully authorised your account.'), 'good');

        return Controller::curr()->redirect($this->Link());
    }
}

class TwitterAdminRequestHandler extends GridFieldDetailForm_ItemRequest
{
    private static $allowed_actions = [
        'edit',
        'view',
        'ItemEditForm'
    ];

    /**
     * @return Form
     */
    public function ItemEditForm()
    {
        $form = parent::ItemEditForm();

        $formActions = $form->Actions();

        if ($actions = $this->record->getCMSActions()) {
            foreach ($actions as $action) {
                $formActions->push($action);
            }
        }

        return $form;
    }
}
