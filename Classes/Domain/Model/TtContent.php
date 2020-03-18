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
 * TtContent
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class TtContent extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * uid
     *
     * @var integer
     */
    protected $uid;

    /**
     * crdate
     *
     * @var integer
     */
    protected $crdate;

    /**
     * sysLanguageUid
     *
     * @var integer
     */
    protected $sysLanguageUid;

    /**
     * header
     *
     * @var string
     */
    protected $header;

    /**
     * headerLink
     *
     * @var string
     */
    protected $headerLink;

    /**
     * bodytext
     *
     * @var string
     */
    protected $bodytext;

    /**
     * cType
     *
     * @var string
     */
    protected $contentType = 'textpic';

    /**
     * imageCols
     *
     * @var int
     */
    protected $imageCols = 0;

    /**
     * Image
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
     */
    protected $image;


    /**
     * txRkwnewsletterAuthors
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwNewsletter\Domain\Model\Authors>
     */
    protected $txRkwnewsletterAuthors;


    /**
     * txRkwnewsletterIsEditorial
     *
     * @var bool
     */
    protected $txRkwnewsletterIsEditorial;

    /**
     * txRkwbasicsBodytextMobile
     * Included because tests throws following error:
     * TYPO3\CMS\Extbase\Persistence\Generic\Storage\Exception\SqlErrorException : Field 'tx_rkwbasics_bodytext_mobile' doesn't have a default value
     *
     * @var boolean
     */
    protected $txRkwbasicsBodytextMobile = '';

    /**
     * __construct
     */
    public function __construct()
    {
        //Do not remove the next line: It would break the functionality
        $this->initStorageObjects();

        // it's not created by itself
        $this->setCrdate(time());
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
        $this->image = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->txRkwnewsletterAuthors = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();

    }


    /**
     * Returns the uid
     *
     * @return integer $uid
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * Sets the uid
     *
     * @param integer $uid
     * @return void
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
    }

    /**
     * Returns the crdate
     *
     * @return integer $crdate
     */
    public function getCrdate()
    {
        return $this->crdate;
    }

    /**
     * Sets the crdate
     *
     * @param integer $crdate
     * @return void
     */
    public function setCrdate($crdate)
    {
        $this->crdate = $crdate;
    }

    /**
     * Returns the sysLanguageUid
     *
     * @return integer $sysLanguageUid
     */
    public function getSysLanguageUid()
    {
        return $this->sysLanguageUid;
    }

    /**
     * Sets the sysLanguageUid
     *
     * @param integer $sysLanguageUid
     * @return void
     */
    public function setSysLanguageUid($sysLanguageUid)
    {
        $this->sysLanguageUid = $sysLanguageUid;
    }

    /**
     * Returns the header
     *
     * @return string $header
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * Sets the header
     *
     * @param string $header
     * @return void
     */
    public function setHeader($header)
    {
        $this->header = $header;
    }


    /**
     * Returns the headerLink
     *
     * @return string $headerLink
     */
    public function getHeaderLink()
    {
        return $this->headerLink;
    }

    /**
     * Sets the headerLink
     *
     * @param string $headerLink
     * @return void
     */
    public function setHeaderLink($headerLink)
    {
        $this->headerLink = $headerLink;
    }


    /**
     * Returns the bodytext
     *
     * @return string $bodytext
     */
    public function getBodytext()
    {
        return $this->bodytext;
    }

    /**
     * Sets the bodytext
     *
     * @param string $bodytext
     * @return void
     */
    public function setBodytext($bodytext)
    {
        $this->bodytext = $bodytext;
    }

    /**
     * Returns the contentType
     *
     * @return string $contentType
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Sets the contentType
     *
     * @param string $contentType
     * @return void
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    /**
     * Returns the imageCols
     *
     * @return int $imageCols
     */
    public function getImageCols()
    {
        return $this->imageCols;
    }

    /**
     * Sets the imageCols
     *
     * @param int $imageCols
     * @return void
     */
    public function setImageCols($imageCols)
    {
        $this->imageCols = $imageCols;
    }


    /**
     * Adds a backend user to the newsletter
     *
     * @param \RKW\RkwBasics\Domain\Model\FileReference $image
     * @return void
     * @api
     */
    public function addImage(\RKW\RkwBasics\Domain\Model\FileReference $image)
    {
        $this->image->attach($image);
    }

    /**
     * Removes a backend user from the newsletter
     *
     * @param \RKW\RkwBasics\Domain\Model\FileReference $image
     * @return void
     * @api
     */
    public function removeImage(\RKW\RkwBasics\Domain\Model\FileReference $image)
    {
        $this->image->detach($image);
    }

    /**
     * Returns the backend user.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage An object storage containing the backend user
     * @api
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Sets the backend user.
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $image
     * @return void
     * @api
     */
    public function setImage(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $image)
    {
        $this->image = $image;
    }


    /**
     * Adds a Authors
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Authors $txRkwnewsletterAuthors
     * @return void
     */
    public function addTxRkwnewsletterAuthors(\RKW\RkwNewsletter\Domain\Model\Authors $txRkwnewsletterAuthors)
    {
        $this->txRkwnewsletterAuthors->attach($txRkwnewsletterAuthors);
    }

    /**
     * Removes a Authors
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Authors $txRkwnewsletterAuthorsToRemove The Authors to be removed
     * @return void
     */
    public function removeTxRkwnewsletterAuthors(\RKW\RkwNewsletter\Domain\Model\Authors $txRkwnewsletterAuthorsToRemove)
    {
        $this->txRkwnewsletterAuthors->detach($txRkwnewsletterAuthorsToRemove);
    }

    /**
     * Returns the txRkwnewsletterAuthors
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwNewsletter\Domain\Model\Authors> $txRkwnewsletterAuthors
     */
    public function getTxRkwnewsletterAuthors()
    {
        return $this->txRkwnewsletterAuthors;
    }

    /**
     * Sets the txRkwnewsletterAuthors
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwNewsletter\Domain\Model\Authors> $txRkwnewsletterAuthors
     * @return void
     */
    public function setTxRkwnewsletterAuthors(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $txRkwnewsletterAuthors)
    {
        $this->txRkwnewsletterAuthors = $txRkwnewsletterAuthors;
    }

    /**
     * Returns the uid
     *
     * @return bool $txRkwnewsletterIsEditorial
     */
    public function getTxRkwnewsletterIsEditorial()
    {
        return $this->txRkwnewsletterIsEditorial;
    }

    /**
     * Sets the uid
     *
     * @param bool $txRkwnewsletterIsEditorial
     * @return void
     */
    public function setTxRkwnewsletterIsEditorial($txRkwnewsletterIsEditorial)
    {
        $this->txRkwnewsletterIsEditorial = $txRkwnewsletterIsEditorial;
    }

    /**
     * Returns the txRkwbasicsBodytextMobile
     *
     * @return boolean $txRkwbasicsBodytextMobile
     */
    public function getTxRkwbasicsBodytextMobile()
    {
        return $this->txRkwbasicsBodytextMobile;
    }

    /**
     * Sets the txRkwbasicsBodytextMobile
     *
     * @param boolean $txRkwbasicsBodytextMobile
     * @return void
     */
    public function setTxRkwbasicsBodytextMobile($txRkwbasicsBodytextMobile)
    {
        $this->txRkwbasicsBodytextMobile = $txRkwbasicsBodytextMobile;
    }


}