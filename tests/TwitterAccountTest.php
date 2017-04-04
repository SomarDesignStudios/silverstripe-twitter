<?php

class TwitterAccountTest extends SapphireTest
{
    public function testSetAccessToken()
    {
        /**
         * Should serialize the token and set it on the TwitterAccount.
         */
        $token = ['token' => 'abc', 'secret' => '123'];

        $twitterAccount = new TwitterAccount();

        $this->assertEquals(null, $twitterAccount->getField('AccessToken'));

        $twitterAccount->setAccessToken($token);

        $this->assertInternalType('string', $twitterAccount->getField('AccessToken'));
    }

    public function testGetAccessToken()
    {
        /**
         * Should return the unserialized token on TwitterAccount.
         */
        $twitterAccount = new TwitterAccount([
            'AccessToken' => serialize(['token' => 'abc', 'secret' => '123']),
        ]);

        $this->assertInternalType('array', $twitterAccount->getAccessToken());
    }

    public function testIsAuthorised()
    {
        /**
         * Should fail if an access token is not set.
         */
        $twitterAccount = new TwitterAccount();

        $this->assertFalse($twitterAccount->isAuthorised());

        /**
         * Should pass if an access token is set.
         */
        $twitterAccount = new TwitterAccount([
            'AccessToken' => serialize(['token' => 'abc', 'secret' => '123']),
        ]);

        $this->assertTrue($twitterAccount->isAuthorised());
    }
}
