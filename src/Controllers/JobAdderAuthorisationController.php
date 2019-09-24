<?php

namespace BiffBangPow\JobAdderJobBoard\Controllers;

use GuzzleHttp\Exception\GuzzleException;
use JobAdderAPIClient;
use JobAdderSync;
use PageController;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\ValidationException;
use SilverStripe\SiteConfig\SiteConfig;

class JobAdderAuthorisationController extends PageController
{
    private static $allowed_actions = [
        'index'           => 'ADMIN',
        'process'         => 'ADMIN',
        'complete'        => 'ADMIN',
        'board'           => 'ADMIN',
        'SelectBoardForm' => 'ADMIN',
    ];

    /**
     * @param HTTPRequest $request
     * @throws ValidationException
     */
    public function index(HTTPRequest $request)
    {
        $state = uniqid();

        $siteConfig = SiteConfig::current_site_config();
        $siteConfig->update(['JobAdderJobBoardState' => $state]);
        $siteConfig->write();

        $url = sprintf(
            "%s/connect/authorize?response_type=code&client_id=%s&scope=read%%20offline_access&redirect_uri=%s&state=%s",
            "https://id.jobadder.com",
            $this->getClientId(),
            urlencode($this->getRedirectURL()),
            $state
        );

        $this->redirect($url);
    }

    /**
     * @param HTTPRequest $request
     * @return HTTPResponse
     * @throws GuzzleException
     * @throws ValidationException
     */
    public function process(HTTPRequest $request)
    {
        $code = $request->getVar('code');
        $state = $request->getVar('state');
        $error = $request->getVar('error');

        // check state
        $siteConfig = SiteConfig::current_site_config();

        if ($error !== null || $state === null || $state !== $siteConfig->JobAdderJobBoardState) {
            return new HTTPResponse('Authorisation failed', 403);
        }

        $apiClient = new JobAdderAPIClient();
        $apiClient->setAccessToken($code, $this->getRedirectURL());

        $this->redirect($this->Link('board'));
    }

    /**
     * @param HTTPRequest $request
     * @return array
     */
    public function board(HTTPRequest $request)
    {
        $siteConfig = SiteConfig::current_site_config();
        if ($siteConfig->JobAdderJobBoardAccessToken === '' || $siteConfig->JobAdderJobBoardAccessToken === null) {
            return $this->redirect($this->Link());
        }

        return [];
    }

    /**
     * @param HTTPRequest $request
     * @return array
     */
    public function complete(HTTPRequest $request)
    {
        $siteConfig = SiteConfig::current_site_config();
        if ($siteConfig->JobAdderJobBoardAccessToken === '' || $siteConfig->JobAdderJobBoardAccessToken === null) {
            return $this->redirect($this->Link());
        }

        return [
            'Message' => 'Job adder integration successful, a sync will be run shortly',
        ];
    }

    public function SelectBoardForm()
    {
        $apiClient = new JobAdderAPIClient();

        $boards = $apiClient->getJobBoards();
        $boardArray = new ArrayList($boards);
        $options = $boardArray->map('boardId', 'name')->toArray();

        $boardDropdown = DropdownField::create('BoardId', 'Job Board', $options)->setEmptyString('Select board')->addExtraClass('col-12');

        $siteConfig = SiteConfig::current_site_config();
        $currentBoardId = $siteConfig->JobAdderJobBoardJobBoardID;

        if ($currentBoardId !== '') {
            $boardDropdown->setValue($currentBoardId);
        }

        $fields = FieldList::create([
            $boardDropdown,
        ]);

        $actions = FieldList::create(
            FormAction::create('selectJobBoard', 'Select')
        );

        $form = Form::create(
            $this,
            __FUNCTION__,
            $fields,
            $actions,
            new RequiredFields([
                    'BoardId',
                ]
            )
        );

        return $form;
    }

    /**
     * @param $data
     * @param Form $form
     * @return HTTPResponse
     * @throws ValidationException
     */
    public function selectJobBoard($data, $form)
    {
        $siteConfig = SiteConfig::current_site_config();
        $siteConfig->update(
            [
                'JobAdderJobBoardJobBoardID' => $data['BoardId'],
            ]
        );
        $siteConfig->write();

        return $this->redirect($this->Link('complete'));
    }

    /**
     * @param null $action
     * @return string
     */
    public function Link($action = null)
    {
        $link = Controller::join_links('authorise-job-adder', $action);
        $this->extend('updateLink', $link, $action);

        return $link;
    }

    /**
     * @return string
     */
    private function getRedirectURL()
    {
        return Director::absoluteBaseURL() . $this->Link('process');
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
