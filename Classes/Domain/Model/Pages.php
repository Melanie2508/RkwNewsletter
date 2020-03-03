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

/**
 * Pages
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Pages extends \RKW\RkwNewsletter\Domain\Model\PagesAbstract
{
    /**
     * permsUserId
     *
     * @var integer
     */
    protected $permsUserId = 0;

    /**
     * permsGroupId
     *
     * @var integer
     */
    protected $permsGroupId = 0;

    /**
     * permsUser
     *
     * @var integer
     */
    protected $permsUser = 0;

    /**
     * permsGroup
     *
     * @var integer
     */
    protected $permsGroup = 0;

    /**
     * permsEverybody
     *
     * @var integer
     */
    protected $permsEverybody = 0;

    /**
     * doktype
     *
     * @var integer
     */
    protected $dokType = 1;

    /**
     * txRkwnewsletterNewsletter
     *
     * @var \RKW\RkwNewsletter\Domain\Model\Newsletter
     */
    protected $txRkwnewsletterNewsletter;

    /**
     * txRkwnewsletterTopic
     *
     * @var \RKW\RkwNewsletter\Domain\Model\Topic
     */
    protected $txRkwnewsletterTopic;


    /**
     * txRkwnewsletterIssue
     *
     * @var \RKW\RkwNewsletter\Domain\Model\Issue
     */
    protected $txRkwnewsletterIssue;


    /**
     * txRkwnewsletterExclude
     *
     * @var integer
     */
    protected $txRkwnewsletterExclude;

    /**
     * txRkwnewsletterTeaserHeading
     *
     * @var string
     */
    protected $txRkwnewsletterTeaserHeading;

    /**
     * txRkwnewsletterTeaserText
     * set default value to avoid an error: "Field 'tx_rkwbasics_teaser_text' doesn't have a default value"
     *
     * @var string
     */
    protected $txRkwnewsletterTeaserText = '';

    /**
     * txRkwnewsletterTeaserImage
     *
     * @var \RKW\RkwBasics\Domain\Model\FileReference
     */
    protected $txRkwnewsletterTeaserImage;

    /**
     * txRkwnewsletterTeaserLink
     *
     * @var string
     */
    protected $txRkwnewsletterTeaserLink;

    /**
     * txRkwnewsletterIncludeTstamp
     *
     * @var int
     */
    protected $txRkwnewsletterIncludeTstamp;

    /**
     * Returns the permsUserId
     *
     * @return integer $permsUserId
     */
    public function getPermsUserId()
    {
        return $this->permsUserId;
    }

    /**
     * Sets the permsUserId
     *
     * @param integer $permsUserId
     * @return void
     */
    public function setPermsUserId($permsUserId)
    {
        $this->permsUserId = $permsUserId;
    }

    /**
     * Returns the permsGroupId
     *
     * @return integer $permsGroupId
     */
    public function getPermsGroupId()
    {
        return $this->permsGroupId;
    }

    /**
     * Sets the permsGroupId
     *
     * @param integer $permsGroupId
     * @return void
     */
    public function setPermsGroupId($permsGroupId)
    {
        $this->permsGroupId = $permsGroupId;
    }

    /**
     * Returns the permsUser
     *
     * @return integer $permsUser
     */
    public function getPermsUser()
    {
        return $this->permsUser;
    }

    /**
     * Sets the permsUser
     *
     * @param integer $permsUser
     * @return void
     */
    public function setPermsUser($permsUser)
    {
        $this->permsUser = $permsUser;
    }

    /**
     * Returns the permsGroup
     *
     * @return integer $permsGroup
     */
    public function getPermsGroup()
    {
        return $this->permsGroup;
    }

    /**
     * Sets the permsGroup
     *
     * @param integer $permsGroup
     * @return void
     */
    public function setPermsGroup($permsGroup)
    {
        $this->permsGroup = $permsGroup;
    }

    /**
     * Returns the permsEverybody
     *
     * @return integer $permsEverybody
     */
    public function getPermsEverybody()
    {
        return $this->permsEverybody;
    }

    /**
     * Sets the permsEverybody
     *
     * @param integer $permsEverybody
     * @return void
     */
    public function setPermsEverybody($permsEverybody)
    {
        $this->permsEverybody = $permsEverybody;
    }

    /**
     * Returns the dokType
     *
     * @return integer $dokType
     */
    public function getDokType()
    {
        return $this->dokType;
    }

    /**
     * Sets the dokType
     *
     * @param integer $dokType
     * @return void
     */
    public function setDokType($dokType)
    {
        $this->dokType = $dokType;
    }

    /**
     * Returns the newsletter
     *
     * @return \RKW\RkwNewsletter\Domain\Model\Newsletter
     */
    public function getTxRkwnewsletterNewsletter()
    {
        return $this->txRkwnewsletterNewsletter;
    }

    /**
     * Sets the newsletter
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Newsletter $txRkwnewsletterNewsletter
     * @return void
     */
    public function setTxRkwnewsletterNewsletter($txRkwnewsletterNewsletter)
    {
        $this->txRkwnewsletterNewsletter = $txRkwnewsletterNewsletter;
    }

    /**
     * Returns the topic
     *
     * @return \RKW\RkwNewsletter\Domain\Model\Topic
     */
    public function getTxRkwnewsletterTopic()
    {
        return $this->txRkwnewsletterTopic;
    }

    /**
     * Sets the topic
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Topic $txRkwnewsletterTopic
     * @return void
     */
    public function setTxRkwnewsletterTopic($txRkwnewsletterTopic)
    {
        $this->txRkwnewsletterTopic = $txRkwnewsletterTopic;
    }


    /**
     * Returns the txRkwnewsletterIssue
     *
     * @return \RKW\RkwNewsletter\Domain\Model\Issue $txRkwnewsletterIssue
     */
    public function getTxRkwnewsletterIssue()
    {
        return $this->txRkwnewsletterIssue;
    }

    /**
     * Sets the txRkwnewsletterIssue
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $txRkwnewsletterIssue
     * @return void
     */
    public function setTxRkwnewsletterIssue($txRkwnewsletterIssue)
    {
        $this->txRkwnewsletterIssue = $txRkwnewsletterIssue;
    }


    /**
     * Returns the txRkwnewsletterExclude
     *
     * @return integer $txRkwnewsletterExclude
     */
    public function getTxRkwnewsletterExclude()
    {
        return $this->txRkwnewsletterExclude;
    }

    /**
     * Sets the txRkwnewsletterExclude
     *
     * @param integer $txRkwnewsletterExclude
     * @return void
     */
    public function setTxRkwnewsletterExclude($txRkwnewsletterExclude)
    {
        $this->txRkwnewsletterExclude = $txRkwnewsletterExclude;
    }

    /**
     * Returns the txRkwnewsletterTeaserHeading
     *
     * @return string $txRkwnewsletterTeaserHeading
     */
    public function getTxRkwnewsletterTeaserHeading()
    {
        return $this->txRkwnewsletterTeaserHeading;
    }

    /**
     * Sets the txRkwnewsletterTeaserHeading
     *
     * @param string $txRkwnewsletterTeaserHeading
     * @return void
     */
    public function setTxRkwnewsletterTeaserHeading($txRkwnewsletterTeaserHeading)
    {
        $this->txRkwnewsletterTeaserHeading = $txRkwnewsletterTeaserHeading;
    }

    /**
     * Returns the txRkwnewsletterTeaserText
     *
     * @return string $txRkwnewsletterTeaserText
     */
    public function getTxRkwnewsletterTeaserText()
    {
        return $this->txRkwnewsletterTeaserText;
    }

    /**
     * Sets the txRkwnewsletterTeaserText
     *
     * @param string $txRkwnewsletterTeaserText
     * @return void
     */
    public function setTxRkwnewsletterTeaserText($txRkwnewsletterTeaserText)
    {
        $this->txRkwnewsletterTeaserText = $txRkwnewsletterTeaserText;
    }

    /**
     * Returns the image
     *
     * @return \RKW\RkwBasics\Domain\Model\FileReference $txRkwnewsletterTeaserImage
     */
    public function getTxRkwnewsletterTeaserImage()
    {
        return $this->txRkwnewsletterTeaserImage;
    }

    /**
     * Sets the image
     *
     * @param \RKW\RkwBasics\Domain\Model\FileReference $txRkwnewsletterTeaserImage
     * @return void
     */
    public function setTxRkwnewsletterTeaserImage($image)
    {
        $this->txRkwnewsletterTeaserImage = $image;
    }

    /**
     * Returns the txRkwnewsletterTeaserLink
     *
     * @return string $txRkwnewsletterTeaserLink
     */
    public function getTxRkwnewsletterTeaserLink()
    {
        return $this->txRkwnewsletterTeaserLink;
    }

    /**
     * Sets the txRkwnewsletterTeaserLink
     *
     * @param string $txRkwnewsletterTeaserLink
     * @return void
     */
    public function setTxRkwnewsletterTeaserLink($txRkwnewsletterTeaserLink)
    {
        $this->txRkwnewsletterTeaserLink = $txRkwnewsletterTeaserLink;
    }

    /**
     * Returns the txRkwnewsletterIncludeTstamp
     *
     * @return integer $txRkwnewsletterIncludeTstamp
     */
    public function getTxRkwnewsletterIncludeTstamp()
    {
        return $this->txRkwnewsletterIncludeTstamp;
    }

    /**
     * Sets the txRkwnewsletterIncludeTstamp
     *
     * @param integer $txRkwnewsletterIncludeTstamp
     * @return void
     */
    public function setTxRkwnewsletterIncludeTstamp($txRkwnewsletterIncludeTstamp)
    {
        $this->txRkwnewsletterIncludeTstamp = $txRkwnewsletterIncludeTstamp;
    }


}