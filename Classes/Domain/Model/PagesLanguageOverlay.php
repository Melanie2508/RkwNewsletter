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
 * PagesLanguageOverlay
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PagesLanguageOverlay extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * title
     *
     * @var string
     */
    protected $title;

    /**
     * doktype
     *
     * @var integer
     */
    protected $doktype = 1;

    /**
     * sysLanguageUid
     *
     * @var integer
     */
    protected $sysLanguageUid;

    /**
     * txRkwnewsletterTeaserHeading
     *
     * @var string
     */
    protected $txRkwnewsletterTeaserHeading;

    /**
     * txRkwnewsletterTeaserText
     *
     * @var string
     */
    protected $txRkwnewsletterTeaserText;

    /**
     * txRkwnewsletterTeaserLink
     *
     * @var string
     */
    protected $txRkwnewsletterTeaserLink;

    /**
     * txRkwbasicsTeaserText
     * set default value to avoid an error: "Field 'tx_rkwbasics_teaser_text' doesn't have a default value"
     *
     * @var string
     */
    protected $txRkwbasicsTeaserText = '';

    /**
     * txRkwbasicsInformation
     * set default value to avoid an error: "Field 'tx_rkwbasics_information' doesn't have a default value"
     *
     * @var string
     */
    protected $txRkwbasicsInformation = '';

    /**
     * Returns the title
     *
     * @return integer $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the title
     *
     * @param integer $title
     * @return void
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Returns the doktype
     *
     * @return integer $doktype
     */
    public function getDoktype()
    {
        return $this->doktype;
    }

    /**
     * Sets the doktype
     *
     * @param integer $doktype
     * @return void
     */
    public function setDoktype($doktype)
    {
        $this->doktype = $doktype;
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

}