<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\ValidationException;
use SilverStripe\SiteConfig\SiteConfig;

class JobAdderAPIClient
{
    private $apiBaseURL;

    private $accessToken;

    private $refreshToken;

    private $jobBoardId;

    /**
     * JobAdderAPIClient constructor.
     */
    public function __construct()
    {
        $siteConfig = SiteConfig::current_site_config();
        $this->apiBaseURL = $siteConfig->JobAdderJobBoardAPIBaseURL;
        $this->accessToken = $siteConfig->JobAdderJobBoardAccessToken;
        $this->refreshToken = $siteConfig->JobAdderJobBoardRefreshToken;
        $this->jobBoardId = $siteConfig->JobAdderJobBoardJobBoardID;
    }

    /**
     * @return mixed
     */
    public function getJobBoards()
    {
        $contents = $this->makeGetRequest('jobboards');
        $boards = json_decode($contents, true);
        return $boards['items'];
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getJobAds()
    {
        $now = new DateTime();

        $contents = $this->makeGetRequest('/jobboards/' . $this->jobBoardId . '/ads', [
            '>expiresAt' => $now->format(DateTimeInterface::ISO8601),
        ]);

        $ads = json_decode($contents, true);
        return $ads['items'];
    }

    /**
     * @param $adId
     * @return mixed
     */
    public function getJobAd($adId)
    {
        $contents = $this->makeGetRequest('/jobboards/' . $this->jobBoardId . '/ads/' . $adId);
        $ad = json_decode($contents, true);
        var_dump($ad);
        die();
        return $ad;
    }

    /**
     * @param $consultantId
     * @return mixed|ResponseInterface|null
     * @throws GuzzleException
     */
    public function getConsultantPhotoResponse($consultantId)
    {
        $client = new Client();
        try {
            $response = $client->request('GET', $this->apiBaseURL . sprintf('users/%d/photo', $consultantId), ['headers' => $this->getAuthorsationHeaders()]);
            return $response;
        } catch (ClientException $exception) {
            return null;
        }
    }

    public function makeGetRequest($path, $params = [])
    {
        if (count($params) > 0) {
            $path .= '?' . http_build_query($params);
        }

        $client = new Client();
        $response = $client->request('GET', $this->apiBaseURL . $path, ['headers' => $this->getAuthorsationHeaders()]);
        return $response->getBody()->getContents();
    }

    /**
     * @param $code
     * @param $redirectURL
     * @throws GuzzleException
     * @throws ValidationException
     */
    public function setAccessToken($code, $redirectURL)
    {
        $client = new Client();

        $requestBody = http_build_query([
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'redirect_uri'  => $redirectURL,
            'client_id'     => $this->getClientId(),
            'client_secret' => $this->getClientSecret(),
        ]);

        $response = $client->request('POST', 'https://id.jobadder.com/connect/token', [
            'body' => $requestBody,
        ]);

        $contents = $response->getBody()->getContents();

        $contentsArray = json_decode($contents, true);

        $accessToken = $contentsArray['access_token'];
        $refreshToken = $contentsArray['refresh_token'];
        $apiBaseURL = $contentsArray['api'];

        $siteConfig = SiteConfig::current_site_config();
        $siteConfig->update(
            [
                'JobAdderJobBoardAccessToken'  => $accessToken,
                'JobAdderJobBoardRefreshToken' => $refreshToken,
                'JobAdderJobBoardAPIBaseURL'   => $apiBaseURL,
            ]
        );
        $siteConfig->write();
    }

    /**
     * @throws GuzzleException
     * @throws ValidationException
     */
    public function refreshAccessToken()
    {
        $client = new Client();
        $siteConfig = SiteConfig::current_site_config();

        $requestBody = http_build_query([
            'grant_type'    => 'refresh_token',
            'refresh_token' => $siteConfig->JobAdderJobBoardRefreshToken,
            'client_id'     => $this->getClientId(),
            'client_secret' => $this->getClientSecret(),
        ]);

        $response = $client->request('POST', 'https://id.jobadder.com/connect/token', [
            'body' => $requestBody,
        ]);

        $contents = $response->getBody()->getContents();

        $contentsArray = json_decode($contents, true);

        $accessToken = $contentsArray['access_token'];
        $refreshToken = $contentsArray['refresh_token'];

        $siteConfig = SiteConfig::current_site_config();
        $siteConfig->update(
            [
                'JobAdderJobBoardAccessToken'  => $accessToken,
                'JobAdderJobBoardRefreshToken' => $refreshToken,
            ]
        );
        $siteConfig->write();
    }

    /**
     * @return array
     */
    private function getAuthorsationHeaders()
    {
        return [
            'Authorization' => 'Bearer ' . $this->accessToken,
        ];
    }

    /**
     * @return mixed
     */
    private function getClientId()
    {
        return Config::inst()->get('BiffBangPow\JobAdderJobBoard\JobAdderJobBoard', 'client_id');
    }

    /**
     * @return mixed
     */
    private function getClientSecret()
    {
        return Config::inst()->get('BiffBangPow\JobAdderJobBoard\JobAdderJobBoard', 'client_secret');
    }
}
