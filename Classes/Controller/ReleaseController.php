<?php

namespace RKW\RkwNewsletter\Controller;
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * ReleaseController
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ReleaseController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * Signal name for use in ext_localconf.php
     *
     * @const string
     */
    const SIGNAL_FOR_SENDING_MAIL_TEST = 'sendTestMail';


    /**
     * newsletterRepository
     *
     * @var \RKW\RkwNewsletter\Domain\Repository\NewsletterRepository
     * @inject
     */
    protected $newsletterRepository = null;

    /**
     * issueRepository
     *
     * @var \RKW\RkwNewsletter\Domain\Repository\IssueRepository
     * @inject
     */
    protected $issueRepository = null;

    /**
     * approvalRepository
     *
     * @var \RKW\RkwNewsletter\Domain\Repository\ApprovalRepository
     * @inject
     */
    protected $approvalRepository = null;

    /**
     * topicRepository
     *
     * @var \RKW\RkwNewsletter\Domain\Repository\TopicRepository
     * @inject
     */
    protected $topicRepository = null;

    /**
     * pagesRepository
     *
     * @var \RKW\RkwNewsletter\Domain\Repository\PagesRepository
     * @inject
     */
    protected $pagesRepository = null;


    /**
     * frontendUserRepository
     *
     * @var \RKW\RkwNewsletter\Domain\Repository\FrontendUserRepository
     * @inject
     */
    protected $frontendUserRepository = null;


    /**
     * backendUserRepository
     *
     * @var \RKW\RkwNewsletter\Domain\Repository\BackendUserRepository
     * @inject
     */
    protected $backendUserRepository = null;

    /**
     * Persistence Manager
     *
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     * @inject
     */
    protected $persistenceManager;

    /**
     * Mail Service
     *
     * @var \RKW\RkwMailer\Service\MailService
     * @inject
     */
    protected $mailService;

    /**
     * Validation Helper
     *
     * @var \RKW\RkwNewsletter\Helper\Validator
     * @inject
     */
    protected $validatorHelper;

    /**
     * Approval Helper
     *
     * @var \RKW\RkwNewsletter\Helper\Approval
     * @inject
     */
    protected $approvalHelper;



    /**
     * action list
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function listAction()
    {
        $this->view->assignMultiple(
            array(
                'issuesOpenApprovalStage1List'  => $this->issueRepository->findAllToApproveOnStage1ByBackendUser(intval($GLOBALS['BE_USER']->user['uid'])),
                'issuesOpenApprovalStage2List'  => $this->issueRepository->findAllToApproveOnStage2ByBackendUser(intval($GLOBALS['BE_USER']->user['uid'])),
                'issuesApprovedStage1List'      => $this->issueRepository->findAllApprovedOnStage1ByBackendUser(intval($GLOBALS['BE_USER']->user['uid'])),
                'issuesApprovedStage2List'      => $this->issueRepository->findAllApprovedOnStage2ByBackendUser(intval($GLOBALS['BE_USER']->user['uid'])),
                'backendUserId'                 => intval($GLOBALS['BE_USER']->user['uid']),
                'backendUserName'               => $GLOBALS['BE_USER']->user['realName'],
            )
        );
    }


    /**
     * action approve
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Approval $approval
     * @param int $stage
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @ignorevalidation $approval
     */
    public function approveAction(\RKW\RkwNewsletter\Domain\Model\Approval $approval, $stage = 1)
    {
        if (in_array($stage, array(1, 2))) {

            $setterTstamp = 'setAllowedTstampStage' . intval($stage);
            $setterBackendUser = 'setAllowedByUserStage' . intval($stage);

            /** @var \RKW\RkwNewsletter\Domain\Model\BackendUser $backendUser */
            $backendUser = $this->backendUserRepository->findByUid(intval($GLOBALS['BE_USER']->user['uid']));

            $approval->$setterTstamp(time());
            $approval->$setterBackendUser($backendUser);
            $this->approvalRepository->update($approval);

            $this->approvalHelper->updatePagePerms($approval);

            $this->addFlashMessage(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                'releaseController.message.approvalSuccessful',
                'rkw_newsletter'
            ), '', \TYPO3\CMS\Core\Messaging\FlashMessage::OK);

        } else {
            $this->addFlashMessage(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                'releaseController.error.unexpected',
                'rkw_newsletter'
            ), '', \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
        }

        $this->redirect('list');
        //===
    }

    /**
     * action approve
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Approval $approval
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @ignorevalidation $approval
     */
    public function revokeAction(\RKW\RkwNewsletter\Domain\Model\Approval $approval)
    {
        /** @var \RKW\RkwNewsletter\Domain\Model\BackendUser $backendUser */
        $backendUser = $this->backendUserRepository->findByUid(intval($GLOBALS['BE_USER']->user['uid']));

        // remove last approval
        // remove setAllowedTstampStage && setAllowedByUserStage
        if (
            $approval->getAllowedTstampStage2()
            && $approval->getAllowedByUserStage2()
        ) {
            $approval->setAllowedTstampStage2(0);
        } else {
            $approval->setAllowedTstampStage1(0);
        }

        // Set correct values for issue? (via cron?)
        $approval->getIssue()->setStatus(1);

        // @toDo: Send mails that the approval was revoked

        // remove
        $this->approvalRepository->update($approval);

        $this->approvalHelper->updatePagePerms($approval);

        $this->addFlashMessage(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
            'releaseController.message.revokeSuccessful',
            'rkw_newsletter'
        ), '', \TYPO3\CMS\Core\Messaging\FlashMessage::OK);

        $this->redirect('list');
        //===
    }

    /**
     * action defer
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    public function deferAction(\RKW\RkwNewsletter\Domain\Model\Issue $issue)
    {

        $issue->setStatus(98);
        $this->issueRepository->update($issue);

        $this->addFlashMessage(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
            'releaseController.message.issueDeferredSuccessfully',
            'rkw_newsletter'
        ), '', \TYPO3\CMS\Core\Messaging\FlashMessage::OK);


        $this->redirect('list');
        //===
    }


    /**
     * action testList
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function testListAction()
    {

        /** @var \RKW\RkwNewsletter\Domain\Model\BackendUser $backendUser */
        $backendUser = $this->backendUserRepository->findByUid(intval($GLOBALS['BE_USER']->user['uid']));

        $issues = $this->issueRepository->findAllToApproveOrReleaseByBackendUser(intval($GLOBALS['BE_USER']->user['uid']));
        $this->view->assignMultiple(
            array(
                'settings'    => $this->settings,
                'issues'      => $issues,
                'backendUser' => $backendUser,
            )
        );
    }


    /**
     * action test
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue
     * @param string $email
     * @param string $title
     * @param int myTopicsOnly
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @ignorevalidation $issue
     */
    public function testAction(\RKW\RkwNewsletter\Domain\Model\Issue $issue, $email, $title = null, $myTopicsOnly = 0)
    {

        $validateEmail = $this->validatorHelper->email($email);
        if ($validateEmail->hasErrors()) {
            $this->addFlashMessage(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                'releaseController.error.emailIncorrect',
                'rkw_newsletter'
            ), '', \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);

            $this->forward("testList");
            //===
        }

        /** @var \RKW\RkwNewsletter\Domain\Model\BackendUser $backendUser */
        $backendUser = $this->backendUserRepository->findByUid($GLOBALS['BE_USER']->user['uid']);

        /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface<\RKW\RkwNewsletter\Domain\Model\Pages> $pages */
        $pages = $this->pagesRepository->findAllByIssueAndSpecialTopic($issue);

        /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface<\RKW\RkwNewsletter\Domain\Model\Pages> $specialPages */
        $specialPages = $this->pagesRepository->findAllByIssueAndSpecialTopic($issue, true);

        // if user wants his topics only, we need only their pages!
        if ($myTopicsOnly) {
            $pages = $this->pagesRepository->findAllByIssueAndBackendUserAndSpecialTopic($issue, $backendUser);
            // $specialPages = $this->pagesRepository->findAllByIssueAndBackendUserAndSpecialTopic($issue, $backendUser, true);
        }

        $this->getSignalSlotDispatcher()->dispatch(__CLASS__, self::SIGNAL_FOR_SENDING_MAIL_TEST, array($backendUser, $email, $issue, $pages, $specialPages, $title));

        $this->addFlashMessage(
            \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                'releaseController.message.testMailSent',
                'rkw_newsletter'
            )
        );

        $this->redirect("testList");
        //===

    }


    /**
     * action sendList
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function sendListAction()
    {

        $issues = $this->issueRepository->findAllToSendByBackendUser(intval($GLOBALS['BE_USER']->user['uid']));
        $this->view->assignMultiple(
            array(
                'issues'          => $issues,
                'backendUserId'   => intval($GLOBALS['BE_USER']->user['uid']),
                'backendUserName' => $GLOBALS['BE_USER']->user['realName'],
            )
        );
    }


    /**
     * action test
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue
     * @param string $title
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @ignorevalidation $issue
     */
    public function sendAction(\RKW\RkwNewsletter\Domain\Model\Issue $issue, $title = null)
    {

        // now get all recipients and add them to the current issue
        $subscribers = $this->frontendUserRepository->findAllSubscribersByIssue($issue);

        /** @var \RKW\RkwNewsletter\Domain\Model\FrontendUser $frontendUser */
        foreach ($subscribers as $frontendUser) {
            // add to issue
            $issue->addRecipients($frontendUser);
        }

        // set final title and mark as sending
        $issue->setTitle($title);
        $issue->setStatus(3);
        $issue->setReleaseTstamp(time());
        $this->issueRepository->update($issue);

        // Issue is sent: Update page permissions
        // Or more exactly: Is enabled to sent through cronjob
        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        foreach ($issue->getApprovals() as $approval) {
            $this->approvalHelper->updatePagePerms($approval);
        }

        $this->addFlashMessage(
            \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                'releaseController.message.sendingStarted',
                'rkw_newsletter'
            )
        );

        $this->redirect('sendList');
        //===

    }


    /**
     * Returns SignalSlotDispatcher
     *
     * @return \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     */
    protected function getSignalSlotDispatcher()
    {
        if (!$this->signalSlotDispatcher) {
            $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
            $this->signalSlotDispatcher = $objectManager->get('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
        }

        return $this->signalSlotDispatcher;
        //===
    }
}