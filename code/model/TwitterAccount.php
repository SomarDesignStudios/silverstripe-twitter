<?php

class TwitterAccount extends DataObject
{
    public static $dependencies = [
        'twitterService' => '%$TwitterService',
    ];

    /**
     * @var TwitterService
     */
    public $twitterService;

    /**
     * @var array
     */
    private static $db = [
        'Title' => 'Varchar',
        'AccessToken' => 'Text',
    ];

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName('AccessToken');

        if ($this->isAuthorised()) {
            $usernameField = LiteralField::create(
                'Title',
                '<p class="message good">' .
                    _t('Twitter.AccountAuthorisedMessage', 'This account has been authorised.') .
                '</p>' .
                '<div class="field">' .
                    '<label class="left">' .
                        _t('Twitter.FieldLabelTitle', 'Username') .
                    '</label>' .
                    '<div class="middleColumn" style="padding-top:8px;">' .
                        '<a ' .
                            "href='https://www.twitter.com/{$this->Title}' " .
                            'title="View on Twitter" ' .
                            'target="_blank">' .
                            $this->Title .
                        '</a>' .
                    '</div>' .
                '</div>'
            );
        } else {
            $usernameField = Textfield::create('Title', _t('Twitter.FieldLabelTitle', 'Username'));
            $usernameField->setDescription(
                _t(
                    'Twitter.FieldDescriptionTitle',
                    'The Twitter account you want to pull media from.'
                )
            );
        }

        $fields->addFieldToTab('Root.Main', $usernameField);

        $this->extend('updateCMSFields', $fields);

        return $fields;
    }

    /**
     * @return FieldList
     */
    public function getCMSActions()
    {
        $actions = parent::getCMSActions();

        if (!$this->getField('ID') || $this->isAuthorised()) {
            $this->extend('updateCMSActions', $actions);
            return $actions;
        }

        $token = $this->twitterService->getOAuthToken();

        // Set the token in session so it can be accessed throughout the OAuth flow.
        $this->twitterService->setSessionOAuthToken($token);

        $actions->push(
            LiteralField::create(
                'OAuthLink',
                '<a class="ss-ui-button" href="' . $this->twitterService->getAuthoriseUrl($token) . '">' .
                    _t('Twitter.ButtonLabelAuthoriseAccount', 'Authorise account') .
                '</a>'
            )
        );

        $this->extend('updateCMSActions', $actions);

        return $actions;
    }

    /**
     * @return RequiredFields
     */
    public function getCMSValidator()
    {
        return new RequiredFields('Title');
    }

    /**
     * Sets an OAuth token for the TwitterAccount.
     *
     * @param array $token
     */
    public function setAccessToken($token)
    {
        $this->setField('AccessToken', serialize($token));
    }

    /**
     * Gets the TwitterAccount's OAuth token.
     *
     * @return array
     */
    public function getAccessToken()
    {
        return unserialize($this->getField('AccessToken'));
    }

    /**
     * Checks if the TwitterAccount has been authorised via OAuth.
     *
     * @return boolean
     */
    public function isAuthorised()
    {
        return (bool) $this->getField('AccessToken');
    }
}
