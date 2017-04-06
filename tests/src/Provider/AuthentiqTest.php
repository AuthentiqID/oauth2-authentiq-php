<?php
/**
 * Created by alexkeramidas for Authentiq B.V.
 * Authentiq Test
 * User: alexkeramidas
 * Date: 6/4/2017
 * Time: 12:00 μμ
 */
namespace Authentiq\OAuth2\Client\Test\Provider;

use Authentiq\OAuth2\Client\Provider\Authentiq;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class AuthentiqTest extends TestCase
{
    protected $provider;

    protected function setUp(){
        $this->provider = new Authentiq([
            'domain'                  => 'https://example.com',
            'clientId'                => 'mock_client_id',
            'clientSecret'            => 'mock_secret',
            'redirectUri'             => 'none',
            'scope' => 'aq:name address aq:location aq:push email phone'
        ]);
    }

    public function tearDown()
    {
        m::close();
        parent::tearDown();
    }


    public function testGetAuthorizationUrl()
    {
        $url = $this->provider->getBaseAuthorizationUrl();
        $uri = parse_url($url);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayHasKey('approval_prompt', $query);
        $this->assertNotNull($this->provider->getState());
    }

    public function testGetAuthorizationUrlWhenAccountIsNotSpecifiedShouldThrowException()
    {
        unset($this->config['account']);

        $provider = new OauthProvider($this->config);

        $this->setExpectedException('RuntimeException');
        $provider->urlAuthorize();
    }

    public function testGetUrlAccessToken()
    {
        $provider = new OauthProvider($this->config);
        $url = $provider->urlAccessToken();
        $uri = parse_url($url);

        $this->assertEquals($this->config['account'] . '.authentiq.com', $uri['host']);
        $this->assertEquals('/oauth/token', $uri['path']);
    }

    public function testGetAccessTokenUrlWhenAccountIsNotSpecifiedShouldThrowException()
    {
        unset($this->config['account']);

        $provider = new OauthProvider($this->config);

        $this->setExpectedException('RuntimeException');
        $provider->urlAccessToken();
    }

    public function testGetUrlUserDetails()
    {
        $provider = new OauthProvider($this->config);

        $accessTokenDummy = $this->getAccessToken();

        $url = $provider->urlUserDetails($accessTokenDummy);
        $uri = parse_url($url);

        $this->assertEquals($this->config['account'] . '.authentiq.com', $uri['host']);
        $this->assertEquals('/userinfo', $uri['path']);
    }

    public function testGetUserDetailsUrlWhenAccountIsNotSpecifiedShouldThrowException()
    {
        unset($this->config['account']);

        $provider = new OauthProvider($this->config);

        $accessTokenDummy = $this->getAccessToken();

        $this->setExpectedException('RuntimeException');
        $provider->urlUserDetails($accessTokenDummy);
    }

    public function getUserDetailsDataProvider()
    {
        return [
            [
                [
                    'user_id'  => 123,
                    'nickname' => 'mock_nickname',
                ],
                [
                    'uid'      => 123,
                    'nickname' => 'mock_nickname',
                    'name'     => null,
                    'email'    => null,
                    'imageUrl' => null,
                ],
            ],
            [
                [
                    'user_id'  => 123,
                    'nickname' => 'mock_nickname',
                    'name'     => 'mock_name',
                    'email'    => 'mock_email',
                    'picture'  => 'mock_picture',
                ],
                [
                    'uid'      => 123,
                    'nickname' => 'mock_nickname',
                    'name'     => 'mock_name',
                    'email'    => 'mock_email',
                    'imageUrl' => 'mock_picture',
                ],
            ],
        ];
    }

    /**
     * @dataProvider getUserDetailsDataProvider
     */
    public function testGetUserDetails($responseData, $expectedUserData)
    {
        $response = (object) $responseData;

        $provider = new OauthProvider($this->config);

        $accessTokenDummy = $this->getAccessToken();
        $userDetails = $provider->userDetails($response, $accessTokenDummy);

        $this->assertInstanceOf('League\OAuth2\Client\Entity\User', $userDetails);

        $this->assertObjectHasAttribute('uid', $userDetails);
        $this->assertObjectHasAttribute('nickname', $userDetails);
        $this->assertObjectHasAttribute('name', $userDetails);
        $this->assertObjectHasAttribute('email', $userDetails);
        $this->assertObjectHasAttribute('imageUrl', $userDetails);

        $this->assertSame($expectedUserData['uid'], $userDetails->uid);
        $this->assertSame($expectedUserData['nickname'], $userDetails->nickname);
        $this->assertSame($expectedUserData['name'], $userDetails->name);
        $this->assertSame($expectedUserData['email'], $userDetails->email);
        $this->assertSame($expectedUserData['imageUrl'], $userDetails->imageUrl);
    }

    public function testGetUserUid()
    {
        $response = new \stdClass();
        $response->user_id = 123;

        $provider = new OauthProvider($this->config);

        $accessTokenDummy = $this->getAccessToken();
        $userUid = $provider->userUid($response, $accessTokenDummy);

        $this->assertSame($response->user_id, $userUid);
    }

    private function getAccessToken()
    {
        return $this->getMockBuilder('League\OAuth2\Client\Token\AccessToken')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
