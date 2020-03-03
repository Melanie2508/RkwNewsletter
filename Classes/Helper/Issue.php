<?php

namespace RKW\RkwNewsletter\Helper;

use \RKW\RkwBasics\Helper\Common;
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

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
 * Issue
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Issue implements \TYPO3\CMS\Core\SingletonInterface
{

    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\NewsletterRepository
     * @inject
     */
    protected $newsletterRepository;

    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\IssueRepository
     * @inject
     */
    protected $issueRepository;

    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\ApprovalRepository
     * @inject
     */
    protected $approvalRepository;

    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\PagesRepository
     * @inject
     */
    protected $pagesRepository;

    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\PagesLanguageOverlayRepository
     * @inject
     */
    protected $pagesLanguageOverlayRepository;

    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\TtContentRepository
     * @inject
     */
    protected $ttContentRepository;

    /**
     * @var \RKW\RkwBasics\Domain\Repository\FileReferenceRepository
     * @inject
     */
    protected $fileReferenceRepository;

    /**
     * PersistenceManager
     *
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     * @inject
     */
    protected $persistenceManager = null;

    /**
     * Approval Helper
     *
     * @var \RKW\RkwNewsletter\Helper\Approval
     * @inject
     */
    protected $approvalHelper;

    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;

    /**
     * @var \RKW\RkwNewsletter\Domain\Model\Newsletter
     */
    private $newsletter;

    /**
     * @var \RKW\RkwNewsletter\Domain\Model\Issue
     */
    private $issue;

    /**
     * @var \RKW\RkwNewsletter\Domain\Model\Pages
     */
    private $containerPage;

    /**
     * @var \RKW\RkwNewsletter\Domain\Model\PagesLanguageOverlay
     */
    private $containerPageLanguageOverlay;

    /**
     * prepareIssue
     * creates an issue of a newsletter to be sent according interval
     *
     * @param int $tolerance
     * @param int $dayOfMonth
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function buildIssue($tolerance = 0, $dayOfMonth = 15)
    {
        // get newsletter to issue - do not include newsletters with pending issues
        $newsletterList = $this->newsletterRepository->findAllToBuildIssue($tolerance, $dayOfMonth);

        if (count($newsletterList) > 0) {

            foreach ($newsletterList as $this->newsletter) {

                // 1. create issue
                $this->createIssue();

                try {

                    // 2. Build topic pages in container-pages
                    /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic */
                    if (count($this->newsletter->getTopic())) {
                        foreach ($this->newsletter->getTopic()->toArray() as $topic) {

                            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, $topic->getName());

                            // 2.1 creates a new container page for the topic
                            $this->createContainerPage($topic);

                            if ($topic->getContainerPage() instanceof \RKW\RkwNewsletter\Domain\Model\Pages) {

                                // 2.2 Check if the newsletter has another language than the default one
                                // Hint: Works with PagesLanguageOverlay. Deprecated since TYPO3 9.5
                                // @toDo: Add translation for TYPO3 >= 9.5
                                if (version_compare(TYPO3_version, '9.5.0', '<=')) {
                                    $this->createContainerPageTranslation();
                                }

                                // 2.3. add containerPage to issue
                                $this->issue->addPages($this->containerPage);

                                // 2.4. Create approval for ContainerPage
                                $this->createContainerPageApproval($topic);

                                // 3. Get all pages with same topic of newsletter which are not used yet
                                $this->createAndAddContentToContainerPage($topic);

                            } else {
                                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Container page for topic "%s" is of wrong type for newsletter with id=%s.', $topic->getName(), $this->newsletter->getUid()));
                            }
                        }
                    } else {
                        $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('No topic found for newsletter with id=%s.', $this->newsletter->getUid()));
                    }

                    // =============
                    // 4. update status (1 = approval)
                    $this->issue->setStatus(1);

                } catch (\Exception $e) {
                    $this->issue->setStatus(99);
                    $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, sprintf('Error while trying to create an issue for newsletter-configuration with id=%s: %s', $this->newsletter->getUid(), $e->getMessage()));
                }

                // =============
                // 5. update and persist
                $this->issueRepository->update($this->issue);

                $this->newsletter->setLastIssueTstamp(time());
                $this->newsletterRepository->update($this->newsletter);

                $this->persistenceManager->persistAll();
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Finished creating issue for newsletter-configuration with id=%s.', $this->newsletter->getUid()));
            }
        } else {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, 'No issues build for existing newsletter-configurations.');
        }
    }


    /**
     * generateIssueTitle
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Newsletter
     * @return string
     */
    protected function generateIssueTitle(\RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter)
    {
        $issueTitle = str_replace("{M}", date("m", time()), $newsletter->getIssueTitle());
        $issueTitle = str_replace("{Y}", date("Y", time()), $issueTitle);

        return $issueTitle;
        //===
    }


    /**
     * Returns TYPO3 settings
     *
     * @param string $which Which type of settings will be loaded
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    protected function getSettings($which = ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS)
    {
        return Common::getTyposcriptConfiguration('Rkwnewsletter', $which);
        //===
    }

    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger()
    {
        if (!$this->logger instanceof \TYPO3\CMS\Core\Log\Logger) {
            $this->logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Log\\LogManager')->getLogger(__CLASS__);
        }

        return $this->logger;
        //===
    }


    /**
     * Debugs a SQL query from a QueryResult
     *
     * @param \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult $queryResult
     * @param boolean $explainOutput
     * @return void
     */
    protected function debugQuery(\TYPO3\CMS\Extbase\Persistence\Generic\QueryResult $queryResult, $explainOutput = false)
    {
        $GLOBALS['TYPO3_DB']->debugOutput = 2;
        if ($explainOutput) {
            $GLOBALS['TYPO3_DB']->explainOutput = true;
        }
        $GLOBALS['TYPO3_DB']->store_lastBuiltQuery = true;
        $queryResult->toArray();
        \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($GLOBALS['TYPO3_DB']->debug_lastBuiltQuery);

        $GLOBALS['TYPO3_DB']->store_lastBuiltQuery = false;
        $GLOBALS['TYPO3_DB']->explainOutput = false;
        $GLOBALS['TYPO3_DB']->debugOutput = false;
    }



    /**
     * createAndPersistIssue
     *
     * @return void
     */
    public function createIssue()
    {
        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $this->issue = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwNewsletter\\Domain\\Model\\Issue');
        $this->issue->setTitle($this->generateIssueTitle($this->newsletter));
        $this->issue->setStatus(0);

        // persist in order to get uid
        $this->issueRepository->add($this->issue);
        $this->newsletter->addIssue($this->issue);
        $this->newsletterRepository->update($this->newsletter);

        $this->persistenceManager->persistAll();
        $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Creating issue for newsletter-configuration with id=%s.', $this->newsletter->getUid()));
    }



    /**
     * createContainerPage
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Topic $topic
     * @return void
     */
    public function createContainerPage(\RKW\RkwNewsletter\Domain\Model\Topic $topic)
    {
        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $containerPage */
        $this->containerPage = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwNewsletter\\Domain\\Model\\Pages');
        $this->containerPage->setTitle($this->issue->getTitle());
        $this->containerPage->setDokType(1);
        $this->containerPage->setPid($topic->getContainerPage()->getUid());
        $this->containerPage->setNoSearch(true);
        $this->containerPage->setTxRkwnewsletterExclude(true);

        $this->pagesRepository->add($this->containerPage);

        // persist in order to get uid
        $this->persistenceManager->persistAll();

        /** Do this after page has been saved! */
        $this->containerPage->setTxRkwnewsletterNewsletter($this->newsletter);
        $this->containerPage->setTxRkwnewsletterTopic($topic);

        $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Created container page with id=%s for topic "%s" in parent page with id=%s for newsletter with id=%s.', $this->containerPage->getUid(), $topic->getName(), $topic->getContainerPage()->getUid(), $this->newsletter->getUid()));
    }



    /**
     * createContainerPageTranslation
     *
     * @return \RKW\RkwNewsletter\Domain\Model\PagesLanguageOverlay
     */
    public function createContainerPageTranslation()
    {
        // Hint: Works with PagesLanguageOverlay. Deprecated since TYPO3 9.5
        if (version_compare(TYPO3_version, '9.5.0', '<=')) {

            if ($this->newsletter->getSysLanguageUid() > 0) {

                /** @var \RKW\RkwNewsletter\Domain\Model\PagesLanguageOverlay $containerPageLanguageOverlay */
                $this->containerPageLanguageOverlay = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwNewsletter\\Domain\\Model\\PagesLanguageOverlay');
                $this->containerPageLanguageOverlay->setTitle($this->containerPage->getTitle());
                $this->containerPageLanguageOverlay->setPid($this->containerPage->getUid());
                $this->containerPageLanguageOverlay->setSysLanguageUid($this->newsletter->getSysLanguageUid());

                $this->pagesLanguageOverlayRepository->add($this->containerPageLanguageOverlay);

                // persist in order to get an uid - only needed because of workaround for tt_content!
                // @toDo: ERROR: By any reason the PID is not created through the persistAll command (see Tests)
                $this->persistenceManager->persistAll();
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Created translation-page with uid=%s and SysLanguageUid=%s for container page with id=%s for newsletter with id=%s.', $this->containerPageLanguageOverlay->getUid(), $this->newsletter->getSysLanguageUid(), $this->containerPage->getUid(), $this->newsletter->getUid()));

                // just for testing - we don't want so punish the DB with senseless persist-queries
                return $this->containerPageLanguageOverlay;
                //===
            }
        }
    }



    /**
     * createContainerPageApproval
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Topic
     * @return void
     */
    public function createContainerPageApproval(\RKW\RkwNewsletter\Domain\Model\Topic $topic)
    {
        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwNewsletter\\Domain\\Model\\Approval');
        $approval->setTopic($topic);
        $approval->setPage($this->containerPage);

        $this->approvalRepository->add($approval);
        $this->issue->addApprovals($approval);
        $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Added an approval for topic "%s" for newsletter with id=%s.', $topic->getName(), $this->newsletter->getUid()));

    }



    /**
     * createAndAddContentToContainerPage
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Topic
     * @return void
     */
    public function createAndAddContentToContainerPage(\RKW\RkwNewsletter\Domain\Model\Topic $topic)
    {
        $pagesList = $this->pagesRepository->findByTopicNotIncluded($topic);
        if (count($pagesList) > 0) {

            /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
            foreach ($pagesList as $page) {

                // 3.1 create new content element for each page and put it into the container page
                $this->createContentElement($page);

                // 3.5 mark current page as already used
                $page->setTxRkwnewsletterIncludeTstamp(time());
                $page->setTxRkwnewsletterIssue($this->issue);
                $this->pagesRepository->update($page);
            }

        } else {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('No contents found for topic "%s" for newsletter with id=%s.', $topic->getName(), $this->newsletter->getUid()));
        }
    }



    /**
     * createContentElement
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Pages $page
     * @return \RKW\RkwNewsletter\Domain\Model\TtContent
     */
    public function createContentElement(\RKW\RkwNewsletter\Domain\Model\Pages $page)
    {
        // 3.2 Check if the newsletter has another language than the default one
        // and fetch corresponding translation if available
        $pageTranslated = $page;
        if ($this->newsletter->getSysLanguageUid() > 0) {
            // Hint: Works with PagesLanguageOverlay. Deprecated since TYPO3 9.5
            if (version_compare(TYPO3_version, '9.5.0', '<=')) {
                if ($tempPageTranslated = $this->pagesLanguageOverlayRepository->findByPid($page->getUid())) {
                    $pageTranslated = $tempPageTranslated;
                }
            }
        }

        /** @var \RKW\RkwNewsletter\Domain\Model\TtContent $ttContentElement */
        $ttContentElement = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwNewsletter\\Domain\\Model\\TtContent');
        $ttContentElement->setPid($this->containerPage->getUid());
        // @toDo: Add translation for TYPO3 >= 9.5
        if (version_compare(TYPO3_version, '9.5.0', '<=')) {
            if ($this->newsletter->getSysLanguageUid() > 0) {
                $ttContentElement->setPid($this->containerPageLanguageOverlay->getUid());
            }
        }
        $ttContentElement->setSysLanguageUid($this->newsletter->getSysLanguageUid());
        $ttContentElement->setContentType('textpic');
        $ttContentElement->setImageCols(1);

        // 3.3 set texts
        $ttContentElement->setHeader($pageTranslated->getTxRkwnewsletterTeaserHeading() ? $pageTranslated->getTxRkwnewsletterTeaserHeading() : $pageTranslated->getTitle());
        $ttContentElement->setBodytext($pageTranslated->getTxRkwnewsletterTeaserText() ? $pageTranslated->getTxRkwnewsletterTeaserText() : $pageTranslated->getTxRkwbasicsTeaserText());
        $ttContentElement->setHeaderLink($page->getTxRkwnewsletterTeaserLink() ? $page->getTxRkwnewsletterTeaserLink() : $page->getUid());

        // get authors from rkw_authors if installed and set
        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_authors')) {
            $ttContentElement->setTxRkwNewsletterAuthors($page->getTxRkwauthorsAuthorship());
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Added authors to tt_content-element for newsletter with id=%s.', $this->newsletter->getUid()));
        }

        // add object
        $this->ttContentRepository->add($ttContentElement);

        $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Added tt-content-element with id=%s and SysLanguageUid=%s to container-page with uid=%s for newsletter with id=%s.', $ttContentElement->getUid(), $ttContentElement->getSysLanguageUid(), $this->containerPage->getUid(), $this->newsletter->getUid()));

        // 3.4 set image
        $this->createFileReference($page, $ttContentElement);

        // just for testing
        // one the one hand, a persistAll does not have an effect. On the other hand, we don't want so punish the DB with senseless queries
        return $ttContentElement;
        //===
    }



    /**
     * createFileReference
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Pages $page
     * @param \RKW\RkwNewsletter\Domain\Model\TtContent $ttContentElement
     * @return \RKW\RkwBasics\Domain\Model\FileReference
     */
    public function createFileReference(\RKW\RkwNewsletter\Domain\Model\Pages $page, $ttContentElement)
    {
        /** @var \RKW\RkwBasics\Domain\Model\FileReference $image */
        $image = $page->getTxRkwnewsletterTeaserImage() ? $page->getTxRkwnewsletterTeaserImage() : ($page->getTxRkwbasicsTeaserImage() ? $page->getTxRkwbasicsTeaserImage() : null);
        $fileReference = null;
        if ($image) {
            // Needed for working with images
            // Without the backendUserAuth "$image->getOriginalResource()" throws an error:
            // Call to a member function isAdmin() on null in /var/www/rkw-website-composer/vendor/typo3/cms/typo3/sysext/core/Classes/Resource/Security/StoragePermissionsAspect.php on line 64
            $backendUserAuthentication = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Authentication\BackendUserAuthentication::class);
            $GLOBALS['BE_USER'] = $backendUserAuthentication;

            /** @var \RKW\RkwBasics\Domain\Model\FileReference $fileReference */
            $fileReference = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwBasics\\Domain\\Model\\FileReference');
            $fileReference->setOriginalResource($image->getOriginalResource());
            $fileReference->setTablenames('tt_content');
            $fileReference->setTableLocal('sys_file');
            $fileReference->setFile($image->getFile());
            $fileReference->setUidForeign($ttContentElement->getUid());

            $this->fileReferenceRepository->add($fileReference);

            // $ttContentElement->addImage($fileReference);
            // $ttContentRepository->update($ttContentElement);
            $this->ttContentRepository->updateImage($ttContentElement);
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Added a fileReference with uid=%s to tt-content-element with id=%s for newsletter with id=%s.', $fileReference->getUid(), $ttContentElement->getUid(), $this->newsletter->getUid()));
        }

        // just for testing
        return $fileReference;
        //===
    }



    /**
     * setIssue
     * !! USE THIS ONLY FOR TESTING PURPOSE !!
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue
     * @return void
     */
    public function setIssue(\RKW\RkwNewsletter\Domain\Model\Issue $issue)
    {
        $this->issue = $issue;
    }

    /**
     * getIssue
     * !! USE THIS ONLY FOR TESTING PURPOSE !!
     *
     * @return \RKW\RkwNewsletter\Domain\Model\Issue $issue
     */
    public function getIssue()
    {
        return $this->issue;
        //===
    }

    /**
     * setNewsletter
     * !! USE THIS ONLY FOR TESTING PURPOSE !!
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter
     * @return void
     */
    public function setNewsletter(\RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter)
    {
        $this->newsletter = $newsletter;
    }

    /**
     * getNewsletter
     * !! USE THIS ONLY FOR TESTING PURPOSE !!
     *
     * @return \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter
     */
    public function getNewsletter()
    {
        return $this->newsletter;
        //===
    }

    /**
     * setContainerPage
     * !! USE THIS ONLY FOR TESTING PURPOSE !!
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Pages $containerPage
     * @return void
     */
    public function setContainerPage(\RKW\RkwNewsletter\Domain\Model\Pages $containerPage)
    {
        $this->containerPage = $containerPage;
    }

    /**
     * getContainerPage
     * !! USE THIS ONLY FOR TESTING PURPOSE !!
     *
     * @return \RKW\RkwNewsletter\Domain\Model\Pages
     */
    public function getContainerPage()
    {
        return $this->containerPage;
        //===
    }

    /**
     * getContainerPageLanguageOverlay
     * !! USE THIS ONLY FOR TESTING PURPOSE !!
     *
     * @return \RKW\RkwNewsletter\Domain\Model\PagesLanguageOverlay
     */
    public function getContainerPageLanguageOverlay()
    {
        return $this->containerPageLanguageOverlay;
        //===
    }

}