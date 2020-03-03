<?php

namespace RKW\RkwNewsletter\Domain\Model;
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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Issue
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Issue extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * title
     *
     * @var string
     */
    protected $title;

    /**
     * status
     *
     * @var integer
     */
    protected $status;

    /**
     * newsletter
     *
     * @var \RKW\RkwNewsletter\Domain\Model\Newsletter
     */
    protected $newsletter;

    /**
     * pages
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwNewsletter\Domain\Model\Pages>
     */
    protected $pages;

    /**
     * approvals
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwNewsletter\Domain\Model\Approval>
     * @cascade remove
     */
    protected $approvals;

    /**
     * recipients
     * throws error without default value: Field 'recipients' doesn't have a default value
     *
     * @var string
     */
    protected $recipients = '';


    /**
     * queueMail
     *
     * @var \RKW\RkwMailer\Domain\Model\QueueMail
     */
    protected $queueMail;


    /**
     * infoTstamp
     *
     * @var integer
     */
    protected $infoTstamp;


    /**
     * reminderTstamp
     *
     * @var integer
     */
    protected $reminderTstamp;


    /**
     * allowedAdmin
     *
     * @var integer
     */
    protected $releaseTstamp;

    /**
     * sentTstamp
     *
     * @var integer
     */
    protected $sentTstamp;


    /**
     * __construct
     */
    public function __construct()
    {
        //Do not remove the next line: It would break the functionality
        $this->initStorageObjects();
    }

    /**
     * Initializes all ObjectStorage properties
     * Do not modify this method!
     * It will be rewritten on each save in the extension builder
     * You may modify the constructor of this class instead
     *
     * @return void
     */
    protected function initStorageObjects()
    {
        $this->pages = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->approvals = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();

    }

    /**
     * Returns the title
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the title
     *
     * @param string $title
     * @return void
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Returns the status
     *
     * @return integer $status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Sets the status
     *
     * @param integer $status
     * @return void
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Returns the newsletter
     *
     * @return \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter
     */
    public function getNewsletter()
    {
        return $this->newsletter;
    }

    /**
     * Sets the newsletter
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter
     * @return void
     */
    public function setNewsletter(\RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter)
    {
        $this->newsletter = $newsletter;
    }

    /**
     * Adds a page to the issue
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Pages $pages
     * @return void
     * @api
     */
    public function addPages(\RKW\RkwNewsletter\Domain\Model\Pages $pages)
    {
        $this->pages->attach($pages);
    }

    /**
     * Removes a page from the issue
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Pages $pages
     * @return void
     * @api
     */
    public function removePages(\RKW\RkwNewsletter\Domain\Model\Pages $pages)
    {
        $this->pages->detach($pages);
    }

    /**
     * Returns the pages. Keep in mind that the property is called "pages"
     * although it can hold several pages.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwNewsletter\Domain\Model\Pages>
     * @api
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * Sets the pages. Keep in mind that the property is called "pages"
     * although it can hold several pages.
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwNewsletter\Domain\Model\Pages> $pages
     * @return void
     * @api
     */
    public function setPages(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $pages)
    {
        $this->pages = $pages;
    }

    /**
     * Adds a approval to the release
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Approval $approval
     * @return void
     * @api
     */
    public function addApprovals(\RKW\RkwNewsletter\Domain\Model\Approval $approval)
    {
        $this->approvals->attach($approval);
    }

    /**
     * Removes a approval from the release
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Approval $approval
     * @return void
     * @api
     */
    public function removeApprovals(\RKW\RkwNewsletter\Domain\Model\Approval $approval)
    {
        $this->approvals->detach($approval);
    }

    /**
     * Returns the approval. Keep in mind that the property is called "approvals"
     * although it can hold several pages.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwNewsletter\Domain\Model\Approval>
     * @api
     */
    public function getApprovals()
    {
        return $this->approvals;
    }

    /**
     * Sets the approval. Keep in mind that the property is called "pages"
     * although it can hold several pages.
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwNewsletter\Domain\Model\Approval> $approval
     * @return void
     * @api
     */
    public function setApprovals(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $approvals)
    {
        $this->approvals = $approvals;
    }


    /**
     * Adds a recipient to the release
     *
     * @param int|\TYPO3\CMS\Extbase\DomainObject\AbstractEntity $recipientId
     * @return void
     * @throws \Exception
     * @api
     */
    public function addRecipients($recipient)
    {
        if ($recipient instanceOf \TYPO3\CMS\Extbase\DomainObject\AbstractEntity) {
            $recipientId = $recipient->getUid();
        } else if(is_int($recipient)) {
            $recipientId = $recipient;
        } else {
            throw new \Exception('Wrong type given');
            //====
        }

        $recipients = $this->getRecipients();
        $recipients[] = $recipientId;
        $this->setRecipients($recipients);
    }

    /**
     * Removes a recipient from the release
     *
     * @param int|\TYPO3\CMS\Extbase\DomainObject\AbstractEntity $recipient
     * @return void
     * @throws \Exception
     * @api
     */
    public function removeRecipients($recipient)
    {
        if ($recipient instanceOf \TYPO3\CMS\Extbase\DomainObject\AbstractEntity) {
            $recipientId = $recipient->getUid();
        } else if(is_int($recipient)) {
            $recipientId = $recipient;
        } else {
            throw new \Exception('Wrong type given');
            //====
        }

        $recipients = $this->getRecipients();
        if (false !== $key = array_search($recipientId, $recipients)) {
            array_splice($recipients, $key, 1);
            $this->setRecipients($recipients);
        }
    }

    /**
     * Returns the recipient. Keep in mind that the property is called "recipients"
     * although it can hold several pages.
     *
     * @return array
     * @api
     */
    public function getRecipients()
    {
        return GeneralUtility::trimExplode(',', $this->recipients, true);
    }

    /**
     * Sets the recipient. Keep in mind that the property is called "pages"
     * although it can hold several pages.
     *
     * @param array $recipients
     * @return void
     * @api
     */
    public function setRecipients($recipients = [])
    {
        $this->recipients = implode(',', $recipients);
    }

    /**
     * Returns the queueMail
     *
     * @return \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     */
    public function getQueueMail()
    {
        return $this->queueMail;
    }

    /**
     * Sets the queueMail
     *
     * @param \RKW\RkwMailer\Domain\Model\QueueMail $queueMail
     * @return void
     */
    public function setQueueMail(\RKW\RkwMailer\Domain\Model\QueueMail $queueMail)
    {
        $this->queueMail = $queueMail;
    }


    /**
     * Returns the infoTstamp
     *
     * @return integer $infoTstamp
     */
    public function getInfoTstamp()
    {
        return $this->infoTstamp;
    }

    /**
     * Sets the infoTstamp
     *
     * @param integer $infoTstamp
     * @return void
     */
    public function setInfoTstamp($infoTstamp)
    {
        $this->infoTstamp = $infoTstamp;
    }

    /**
     * Returns the reminderTstamp
     *
     * @return integer $reminderTstamp
     */
    public function getReminderTstamp()
    {
        return $this->reminderTstamp;
    }

    /**
     * Sets the reminderTstamp
     *
     * @param integer $reminderTstamp
     * @return void
     */
    public function setReminderTstamp($reminderTstamp)
    {
        $this->reminderTstamp = $reminderTstamp;
    }

    /**
     * Returns the releaseTstamp
     *
     * @return integer $sent
     */
    public function getReleaseTstamp()
    {
        return $this->releaseTstamp;
    }

    /**
     * Sets the releaseTstamp
     *
     * @param integer releaseTstamp
     * @return void
     */
    public function setReleaseTstamp($releaseTstamp)
    {
        $this->releaseTstamp = $releaseTstamp;
    }


    /**
     * Returns the sentTstamp
     *
     * @return integer $sentTstamp
     */
    public function getSentTstamp()
    {
        return $this->sentTstamp;
    }

    /**
     * Sets the sent
     *
     * @param integer $sentTstamp
     * @return void
     */
    public function setSentTstamp($sentTstamp)
    {
        $this->sentTstamp = $sentTstamp;
    }


}