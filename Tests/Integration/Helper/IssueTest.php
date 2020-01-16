<?php
namespace RKW\RkwNewsletter\Tests\Integration\Helper;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;

use RKW\RkwNewsletter\Domain\Repository\NewsletterRepository;
use RKW\RkwNewsletter\Domain\Repository\IssueRepository;
use RKW\RkwNewsletter\Domain\Repository\ApprovalRepository;
use RKW\RkwNewsletter\Domain\Repository\TopicRepository;
use RKW\RkwNewsletter\Domain\Repository\PagesRepository;
use RKW\RkwNewsletter\Domain\Repository\PagesLanguageOverlayRepository;
use RKW\RkwNewsletter\Domain\Repository\TtContentRepository;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
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
 * IssueTest
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class IssueTest extends FunctionalTestCase
{

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/rkw_basics',
        'typo3conf/ext/rkw_registration',
        'typo3conf/ext/rkw_mailer',
        'typo3conf/ext/rkw_newsletter',
        'typo3conf/ext/rkw_authors',
    ];

    /**
     * @var string[]
     */
    protected $coreExtensionsToLoad = [];

    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\NewsletterRepository
     */
    private $newsletterRepository;

    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\IssueRepository
     */
    private $issueRepository;

    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\ApprovalRepository
     */
    private $approvalRepository;

    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\TopicRepository
     */
    private $topicRepository;

    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\PagesRepository
     */
    private $pagesRepository;

    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\PagesLanguageOverlayRepository
     */
    private $pagesLanguageOverlayRepository;

    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\TtContentRepository
     */
    private $ttContentRepository;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     */
    private $persistenceManager = null;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    private $objectManager = null;


    /**
     * Setup
     * @throws \Exception
     */
    protected function setUp()
    {
        parent::setUp();

        $this->importDataSet(__DIR__ . '/Fixtures/Database/ProcessApprovalsCommand.xml');

        $this->setUpFrontendRootPage(
            1,
            [
                //'EXT:rkw_newsletter/Configuration/TypoScript/setup.txt',
                //'EXT:rkw_registration/Configuration/TypoScript/setup.txt',
                //'EXT:rkw_mailer/Configuration/TypoScript/setup.txt',

                // TYPO3\CMS\Extbase\Persistence\Generic\Storage\Exception\SqlErrorException : Table 'rkw_dev_komze_ft1bdbb4e.tx_rkwbasics_domain_model_filereference' doesn't exist
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_authors/Configuration/TypoScript/setup.txt',
                //'EXT:rkw_newsletter/Tests/Integration/Helper/Fixtures/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->approvalRepository = $this->objectManager->get(ApprovalRepository::class);
        $this->issueRepository = $this->objectManager->get(IssueRepository::class);
        $this->newsletterRepository = $this->objectManager->get(NewsletterRepository::class);
        $this->topicRepository = $this->objectManager->get(TopicRepository::class);
        $this->pagesRepository = $this->objectManager->get(PagesRepository::class);
        $this->pagesLanguageOverlayRepository = $this->objectManager->get(PagesLanguageOverlayRepository::class);
        $this->ttContentRepository = $this->objectManager->get(TtContentRepository::class);
    }


    /**
     * @test
     */
    public function buildIssue_FindAllToBuildIssue_GivesToleranceAndDayOfMonth_ReturnsTrue()
    {
        // ts data fetched from rkw-kompetenzzentrum.de cron
        $tolerance = 604800;
        $dayOfMonth = 15;

        $newsletterList = $this->newsletterRepository->findAllToBuildIssue($tolerance, $dayOfMonth);

    //    static::assertCount(1, $newsletterList);
    }



    /**
     * @test
     */
    public function buildIssue_CreateAndPersist_GivenNewsletterCreatesIssue_ReturnsTrue()
    {
        $newsletterList[] = $this->newsletterRepository->findByIdentifier(1);

        // =============
        // Get all relevant pages and create an issue
        /** @var \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter */
        foreach ($newsletterList as $newsletter) {

            static::assertInstanceOf('RKW\\RkwNewsletter\\Domain\\Model\\Newsletter', $newsletter);

            // 1. create issue
            /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
            $issue = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwNewsletter\\Domain\\Model\\Issue');
            $issue->setTitle('Some issue title for testing');
            $issue->setStatus(0);

            // persist in order to get uid
            $this->issueRepository->add($issue);
            $newsletter->addIssue($issue);
        //    $this->newsletterRepository->update($newsletter);
        //    $this->persistenceManager->persistAll();

            static::assertInstanceOf('RKW\\RkwNewsletter\\Domain\\Model\\Issue', $issue);
            static::assertObjectHasAttribute('title', $issue);
        }
    }



    /**
     * @test
     */
    public function buildIssue_IterateTopics_GivenNewsletterExpects4Topics_ReturnsTrue()
    {
        $newsletter = $this->newsletterRepository->findByIdentifier(1);

        // =============
        // 2. Build topic pages in container-pages
        /** @var \RKW\RkwNewsletter\Domain\Model\Topic $topic */
        if (count($newsletter->getTopic())) {

            static::assertCount(4, $newsletter->getTopic());

            foreach ($newsletter->getTopic()->toArray() as $topic) {
                static::assertObjectHasAttribute('name', $topic);
            }
        }
    }



    /**
     * @test
     */
    public function buildIssue_GenerateContainerPage_GivenTopicCreatePage_ReturnsTrue()
    {
        $issue = $this->issueRepository->findByIdentifier(1);
        $newsletter = $this->newsletterRepository->findByIdentifier(1);
        $topic = $this->topicRepository->findByIdentifier(1);

        if ($topic->getContainerPage() instanceof \RKW\RkwNewsletter\Domain\Model\Pages) {

            // 2.1 creates a new container page for the topic
            /** @var \RKW\RkwNewsletter\Domain\Model\Pages $containerPage */
            $containerPage = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwNewsletter\\Domain\\Model\\Pages');
            $containerPage->setTitle($issue->getTitle());
            $containerPage->setDokType(1);
            $containerPage->setPid($topic->getContainerPage()->getUid());
            $containerPage->setNoSearch(true);
            $containerPage->setTxRkwnewsletterExclude(true);

            $this->pagesRepository->add($containerPage);

            // persist in order to get uid
        //    $this->persistenceManager->persistAll();

            /** Do this after page has been saved! */
            $containerPage->setTxRkwnewsletterNewsletter($newsletter);
            $containerPage->setTxRkwnewsletterTopic($topic);

            static::assertInstanceOf('RKW\\RkwNewsletter\\Domain\\Model\\Pages', $containerPage);
            static::assertInstanceOf('RKW\\RkwNewsletter\\Domain\\Model\\Topic', $containerPage->getTxRkwnewsletterTopic());
        }
    }



    /**
     * @test
     */
    public function buildIssue_CreateLanguageOverlay_GivenNewsletterWithChangesLanguage_ReturnsTrue()
    {
        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $containerPage */
        $containerPage = $this->pagesRepository->findByIdentifier(4696);
        /** @var \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter */
        $newsletter = $this->newsletterRepository->findByIdentifier(1);

        // set another language uid than 0 (default)
        $newsletter->setSysLanguageUid(1);

        if ($newsletter->getSysLanguageUid() > 0) {

            /** @var \RKW\RkwNewsletter\Domain\Model\PagesLanguageOverlay $containerPageLanguageOverlay */
            $containerPageLanguageOverlay = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwNewsletter\\Domain\\Model\\PagesLanguageOverlay');
            $containerPageLanguageOverlay->setTitle($containerPage->getTitle());
            $containerPageLanguageOverlay->setPid($containerPage->getUid());
            $containerPageLanguageOverlay->setSysLanguageUid($newsletter->getSysLanguageUid());
            $this->pagesLanguageOverlayRepository->add($containerPageLanguageOverlay);

            // persist in order to get an uid - only needed because of workaround for tt_content!
        //    $this->persistenceManager->persistAll();

            static::assertInstanceOf('RKW\\RkwNewsletter\\Domain\\Model\\PagesLanguageOverlay', $containerPageLanguageOverlay);
            static::assertEquals($containerPage->getTitle(), $containerPageLanguageOverlay->getTitle());
            static::assertEquals($containerPage->getUid(), $containerPageLanguageOverlay->getPid());
            static::assertEquals($newsletter->getSysLanguageUid(), $containerPageLanguageOverlay->getSysLanguageUid());
        }
    }



    /**
     * @test
     */
    public function buildIssue_CreateApproval_GivenIssueAndTopicAndContainerPage_ReturnsTrue()
    {
        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByIdentifier(1);
        $topic = $this->topicRepository->findByIdentifier(1);
        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $containerPage */
        $containerPage = $this->pagesRepository->findByIdentifier(4696);

        // Issue got already 1 approval through database scheme
        static::assertCount(1, $issue->getApprovals());

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwNewsletter\\Domain\\Model\\Approval');
        $approval->setTopic($topic);
        $approval->setPage($containerPage);

        $this->approvalRepository->add($approval);
        $issue->addApprovals($approval);

        static::assertCount(2, $issue->getApprovals());
    }


    /**
     * @test
     */
    public function buildIssue_FetchPagesWithCertainTopicWhichAreNotUsedYet_GivenTopic_ReturnsTrue()
    {
        $topic = $this->topicRepository->findByIdentifier(1);

        // =============
        // 3. Get all pages with same topic of newsletter which are not used yet
        // find pages with newsletter-content

        $pagesList = $this->pagesRepository->findByTopicNotIncluded($topic);
        static::assertCount(1, $pagesList);
        if (count($pagesList) > 0) {

            /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
            foreach ($pagesList as $page) {
                static::assertInstanceOf('RKW\\RkwNewsletter\\Domain\\Model\\Pages', $page);
            }
        }
    }


    /**
     * @test
     */
    public function buildIssue_CreateContentElement_GivenPage_ReturnsTrue()
    {
        /** @var \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter */
        $newsletter = $this->newsletterRepository->findByIdentifier(1);

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $containerPage */
        $containerPage = $this->pagesRepository->findByIdentifier(4696);

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        $page = $this->pagesRepository->findByIdentifier(500);
        $pageTranslated = $page;

        /** @var \RKW\RkwNewsletter\Domain\Model\TtContent $ttContentElement */
        $ttContentElement = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwNewsletter\\Domain\\Model\\TtContent');
        $ttContentElement->setPid($containerPage->getUid());
        $ttContentElement->setSysLanguageUid($newsletter->getSysLanguageUid());
        $ttContentElement->setContentType('textpic');
        $ttContentElement->setImageCols(1);

        // 3.3 set texts
        $ttContentElement->setHeader($pageTranslated->getTxRkwnewsletterTeaserHeading() ? $pageTranslated->getTxRkwnewsletterTeaserHeading() : $pageTranslated->getTitle());
        $ttContentElement->setBodytext($pageTranslated->getTxRkwnewsletterTeaserText() ? $pageTranslated->getTxRkwnewsletterTeaserText() : $pageTranslated->getTxRkwbasicsTeaserText());
        $ttContentElement->setHeaderLink($page->getTxRkwnewsletterTeaserLink() ? $page->getTxRkwnewsletterTeaserLink() : $page->getUid());

        // get authors from rkw_authors if installed and set
        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_authors')) {
            static::assertInstanceOf('TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage', $page->getTxRkwauthorsAuthorship());

            $ttContentElement->setTxRkwNewsletterAuthors($page->getTxRkwauthorsAuthorship());

            foreach ($ttContentElement->getTxRkwnewsletterAuthors() as $author) {
                static::assertInstanceOf('RKW\\RkwAuthors\\Domain\\Model\\Authors', $author);
            }
        }

        // add object
        $this->ttContentRepository->add($ttContentElement);

        static::assertInstanceOf('RKW\\RkwNewsletter\\Domain\\Model\\TtContent', $ttContentElement);
    }



    /**
     * @test
     */
    public function buildIssue_SetImage_GivenPage_ReturnsTrue()
    {
        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        $page = $this->pagesRepository->findByIdentifier(500);

        /** @var \RKW\RkwNewsletter\Domain\Model\TtContent $ttContentElement */
        $ttContentElement = $this->ttContentRepository->findByIdentifier(1);

        // 3.4 set image
        /** @var \RKW\RkwBasics\Domain\Model\FileReference $image */
        $image = $page->getTxRkwnewsletterTeaserImage() ? $page->getTxRkwnewsletterTeaserImage() : ($page->getTxRkwbasicsTeaserImage() ? $page->getTxRkwbasicsTeaserImage() : null);

        static::assertInstanceOf('RKW\\RkwBasics\\Domain\\Model\\FileReference', $image);


        $fileReference = null;
        if ($image) {
            /** @var \RKW\RkwBasics\Domain\Model\FileReference $fileReference */
            $fileReference = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwBasics\\Domain\\Model\\FileReference');

            // "$image->getOriginalResource()" wirft Fehler:
            // Call to a member function isAdmin() on null in /var/www/rkw-website-composer/vendor/typo3/cms/typo3/sysext/core/Classes/Resource/Security/StoragePermissionsAspect.php on line 64
            // --> Funktion "->getOriginalResource()" benötigt eingeloggten backend user
            $fileReference->setOriginalResource($image->getOriginalResource());

            $fileReference->setTablenames('tt_content');
            $fileReference->setTableLocal('sys_file');
            $fileReference->setFile($image->getFile());
            $fileReference->setUidForeign($ttContentElement->getUid());

         //   $this->fileReferenceRepository->add($fileReference);

            // $ttContentElement->addImage($fileReference);
            // $ttContentRepository->update($ttContentElement);
            $this->ttContentRepository->updateImage($ttContentElement);

        }
    }



    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}