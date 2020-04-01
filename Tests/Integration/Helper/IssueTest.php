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
use RKW\RkwBasics\Domain\Repository\FileReferenceRepository;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
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
     * @var \RKW\RkwBasics\Domain\Repository\FileReferenceRepository
     */
    protected $fileReferenceRepository;

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
                'EXT:rkw_newsletter/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_registration/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailer/Configuration/TypoScript/setup.txt',

                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_authors/Configuration/TypoScript/setup.txt',
                'EXT:rkw_newsletter/Tests/Integration/Helper/Fixtures/Frontend/Configuration/Rootpage.typoscript',


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
        $this->fileReferenceRepository = $this->objectManager->get(FileReferenceRepository::class);
    }



    /**
     * @test
     */
    public function CreateIssueWithGivenNewsletter()
    {

        /**
         * Scenario:
         *
         * Given Newsletter
         * When a issue is created and data set from newsletter configuriation
         * Then an instance of issue is created
         * Then the title is successfully copied
         * Then the issue is part of the newsletter (configuration)
         */

        $newsletter = $this->newsletterRepository->findByIdentifier(1);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
        /** @var \RKW\RkwNewsletter\Helper\Issue $issueHelper */
        $issueHelper = $objectManager->get('RKW\\RkwNewsletter\\Helper\\Issue');
        $issueHelper->setNewsletter($newsletter);
        $issueHelper->createIssue();

        static::assertInstanceOf('RKW\\RkwNewsletter\\Domain\\Model\\Issue', $issueHelper->getIssue());
        static::assertEquals($issueHelper->getIssue()->getTitle(), $newsletter->getIssueTitle());
        static::assertContains($issueHelper->getIssue(), $issueHelper->getNewsletter()->getIssue());
    }



    /**
     *  @test
     */
    public function CreateContainerPageWithRelationToCertainTopic()
    {
        /**
         * Scenario:
         *
         * Given Issue
         * Given Newsletter
         * Given Topic
         * When a containerPage is created
         * When a specific topic is set
         * Then an instance of pages (containerPage) is created
         * Then the topic is correctly assigned
         */

        $issue = $this->issueRepository->findByIdentifier(1);
        $newsletter = $this->newsletterRepository->findByIdentifier(1);
        $topic = $this->topicRepository->findByIdentifier(1);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
        /** @var \RKW\RkwNewsletter\Helper\Issue $issueHelper */
        $issueHelper = $objectManager->get('RKW\\RkwNewsletter\\Helper\\Issue');
        $issueHelper->setIssue($issue);
        $issueHelper->setNewsletter($newsletter);
        $issueHelper->createContainerPage($topic);

        // get the page
        $createdContainerPage = $this->pagesRepository->findByUid(4700);

        static::assertInstanceOf('RKW\\RkwNewsletter\\Domain\\Model\\Pages', $createdContainerPage);
        static::assertInstanceOf('RKW\\RkwNewsletter\\Domain\\Model\\Topic', $createdContainerPage->getTxRkwnewsletterTopic());
    }



    /**
     * @test
     */
    public function CreateContainerPageWithoutRelationToCertainTopic()
    {
        /**
         * Scenario:
         *
         * Given Issue
         * Given Newsletter
         * Given Topic
         * When a specific topic is set
         * Then an instance of pages (containerPage) is created
         * Then the topic is correctly assigned
         */

        $issue = $this->issueRepository->findByIdentifier(1);
        $newsletter = $this->newsletterRepository->findByIdentifier(1);
        $topic = $this->topicRepository->findByIdentifier(1);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
        /** @var \RKW\RkwNewsletter\Helper\Issue $issueHelper */
        $issueHelper = $objectManager->get('RKW\\RkwNewsletter\\Helper\\Issue');
        $issueHelper->setIssue($issue);
        $issueHelper->setNewsletter($newsletter);
        $issueHelper->createContainerPage($topic);

        // get the page
        $createdContainerPage = $this->pagesRepository->findByUid(4700);

        static::assertInstanceOf('RKW\\RkwNewsletter\\Domain\\Model\\Pages', $createdContainerPage);
        static::assertInstanceOf('RKW\\RkwNewsletter\\Domain\\Model\\Topic', $createdContainerPage->getTxRkwnewsletterTopic());
    }



    /**
     *
     */
    public function CreateContainerPageTranslationIfSysLanguageUidIsGreaterThanZero()
    {

        /**
         * Scenario:
         *
         * Given ContainerPage
         * Given Newsletter
         * When the sysLanguageUid is greater 0 and an additional containerPage (language overlay-table) is created
         * Then an instance of pagesLanguageOverlay is created
         * Then and the content of the standard containerPage is copied - check 'title'
         * Then and the content of the standard containerPage is copied - check 'pid'
         * Then and the content of the standard containerPage is copied - check 'sysLanguageOverlay'
         */

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $containerPage */
        $containerPage = $this->pagesRepository->findByIdentifier(4696);
        /** @var \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter */
        $newsletter = $this->newsletterRepository->findByIdentifier(1);

        // set another language uid than 0 (default)
        $newsletter->setSysLanguageUid(1);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
        /** @var \RKW\RkwNewsletter\Helper\Issue $issueHelper */
        $issueHelper = $objectManager->get('RKW\\RkwNewsletter\\Helper\\Issue');
        $issueHelper->setContainerPage($containerPage);
        $issueHelper->setNewsletter($newsletter);
        $issueHelper->createContainerPageTranslation();

        $containerPageLanguageOverlay = $this->pagesLanguageOverlayRepository->findAll()->getFirst();

        static::assertInstanceOf('RKW\\RkwNewsletter\\Domain\\Model\\PagesLanguageOverlay', $containerPageLanguageOverlay);
        static::assertEquals($containerPage->getTitle(), $containerPageLanguageOverlay->getTitle());
        // @toDo: By any reason the PID
        static::assertEquals($containerPage->getUid(), $containerPageLanguageOverlay->getPid());
        static::assertEquals($newsletter->getSysLanguageUid(), $containerPageLanguageOverlay->getSysLanguageUid());

    }



    /**
     * @test
     */
    public function CreateContainerPageApprovalAfterContainerPageIsCreated()
    {
        /**
         * Scenario:
         *
         * Given Issue
         * Given Topic
         * Given ContainerPage
         * When a new containerPage is created (show test before)
         * Then a new approval is instantiated and added to issue
         */

        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $this->issueRepository->findByIdentifier(1);
        $topic = $this->topicRepository->findByIdentifier(1);
        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $containerPage */
        $containerPage = $this->pagesRepository->findByIdentifier(4696);
        /** @var \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter */
        $newsletter = $this->newsletterRepository->findByIdentifier(1);

        // Issue got already 1 approval through database scheme
        static::assertCount(1, $issue->getApprovals());

        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
        /** @var \RKW\RkwNewsletter\Helper\Issue $issueHelper */
        $issueHelper = $objectManager->get('RKW\\RkwNewsletter\\Helper\\Issue');
        $issueHelper->setNewsletter($newsletter);
        $issueHelper->setIssue($issue);
        $issueHelper->setContainerPage($containerPage);
        $issueHelper->createContainerPageApproval($topic);

        static::assertCount(2, $issue->getApprovals());
    }



    /**
     * Actually not in use: Don't know how / what to test in this fragmented function now
     */
    public function CreateAndAddContentToContainerPageCreateContentElement()
    {

        /**
         * Scenario:
         *
         * Given Newsletter
         * Given ContainerPage
         * Given Page
         * When a container page is filled with page content which is not used for newsletter yet
         * Then a new content element is instantiated and added to the containerPage
         */

        $topic = $this->topicRepository->findByIdentifier(1);

        /** @var \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter */
        $newsletter = $this->newsletterRepository->findByIdentifier(1);

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $containerPage */
        $containerPage = $this->pagesRepository->findByIdentifier(4696);

        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        $page = $this->pagesRepository->findByIdentifier(500);
        $pageTranslated = $page;

        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
        /** @var \RKW\RkwNewsletter\Helper\Issue $issueHelper */
        $issueHelper = $objectManager->get('RKW\\RkwNewsletter\\Helper\\Issue');
        $issueHelper->createAndAddContentToContainerPage($topic);


        static::assertInstanceOf('RKW\\RkwNewsletter\\Domain\\Model\\TtContent', $ttContentElement);
        static::assertEquals($ttContentElement->getPid(), $containerPage->getUid());
    }



    /**
     * @test
     */
    public function CreateContentElementForContainerPage()
    {
        /**
         * Scenario:
         *
         * Given Page
         * Given ContainerElement
         * When a newsletter content page is given
         * Then a new content element is created
         * Then the new content element got the container page PID
         * Then the TeaserHeading of the newsletterPage is copied to the new content element
         * Then the TeaserText of the newsletterPage is copied to the new content element
         */

        /** @var \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter */
        $newsletter = $this->newsletterRepository->findByIdentifier(1);
        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $containerPage */
        $containerPage = $this->pagesRepository->findByIdentifier(4696);
        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        $newsletterTeaserPage = $this->pagesRepository->findByIdentifier(500);

        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
        /** @var \RKW\RkwNewsletter\Helper\Issue $issueHelper */
        $issueHelper = $objectManager->get('RKW\\RkwNewsletter\\Helper\\Issue');
        $issueHelper->setNewsletter($newsletter);
        $issueHelper->setContainerPage($containerPage);
        $issueHelper->createContentElement($newsletterTeaserPage);

        // we have to persist the functions work
        $this->persistenceManager->persistAll();

        /** @var \RKW\RkwNewsletter\Domain\Model\TtContent $ttContentElement */
        $ttContentElement = $this->ttContentRepository->findByPid($containerPage->getUid())->getFirst();

        static::assertInstanceOf('RKW\\RkwNewsletter\\Domain\\Model\\TtContent', $ttContentElement);
        static::assertEquals($ttContentElement->getPid(), $containerPage->getUid());
        static::assertEquals($ttContentElement->getHeader(), $newsletterTeaserPage->getTxRkwnewsletterTeaserHeading());
        static::assertEquals($ttContentElement->getBodytext(), $newsletterTeaserPage->getTxRkwnewsletterTeaserText());
    }



    /**
     * @test
     */
    public function CreateFileReferenceWithGivenImage()
    {
        /**
         * Scenario:
         *
         * Given Page
         * Given ContainerElement
         * When a content element is created and includes an image
         * Then a new file reference is instantiated
         * Then the new file has a relationship to the given content element (uid / foreignUid)
         * Then the new file has a relationship to the given content element (table)
         */

        /** @var \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter */
        $newsletter = $this->newsletterRepository->findByIdentifier(1);
        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        $page = $this->pagesRepository->findByIdentifier(500);

        /** @var \RKW\RkwNewsletter\Domain\Model\TtContent $ttContentElement */
        $ttContentElement = $this->ttContentRepository->findByIdentifier(1);

        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
        /** @var \RKW\RkwNewsletter\Helper\Issue $issueHelper */
        $issueHelper = $objectManager->get('RKW\\RkwNewsletter\\Helper\\Issue');
        $issueHelper->setNewsletter($newsletter);
        $issueHelper->createFileReference($page, $ttContentElement);

        // we have to persist the functions work
        $this->persistenceManager->persistAll();

        static::assertInstanceOf('TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage', $ttContentElement->getImage());
        foreach ($ttContentElement->getImage() as $singleImage) {
            static::assertInstanceOf('RKW\\RkwBasics\\Domain\\Model\\FileReference', $singleImage);
            static::assertEquals($ttContentElement->getUid(), $singleImage->getUidForeign());
            static::assertEquals('tt_content', $singleImage->getTablenames());
            static::assertCount(1, $ttContentElement->getImage());
        }
    }



    /**
     * @test
     */
    public function CreateFileReferenceWithoutImage()
    {
        /**
         * Scenario:
         *
         * Given Page
         * Given ContainerElement
         * When a content element is created without an image
         * Then no file reference is created for this content element
         * Then the count of the objectStorage in 0
         */

        /** @var \RKW\RkwNewsletter\Domain\Model\Newsletter $newsletter */
        $newsletter = $this->newsletterRepository->findByIdentifier(1);
        /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
        $page = $this->pagesRepository->findByIdentifier(501);

        /** @var \RKW\RkwNewsletter\Domain\Model\TtContent $ttContentElement */
        $ttContentElement = $this->ttContentRepository->findByIdentifier(1);

        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
        /** @var \RKW\RkwNewsletter\Helper\Issue $issueHelper */
        $issueHelper = $objectManager->get('RKW\\RkwNewsletter\\Helper\\Issue');
        $issueHelper->setNewsletter($newsletter);
        $issueHelper->createFileReference($page, $ttContentElement);

        // we have to persist the functions work
        $this->persistenceManager->persistAll();

        static::assertInstanceOf('TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage', $ttContentElement->getImage());
        static::assertCount(0, $ttContentElement->getImage());
    }


    /**
     * @test
     */
    public function CreateContainerPageDataCheck()
    {
        /**
         * Scenario:
         *
         * Given Issue
         * Given Newsletter
         * Given Topic
         * When a containerPage is created
         * When a specific topic is set
         * Then an instance of pages (containerPage) is created
         * Then TxRkwnewsletterExclude is true
         */

        $issue = $this->issueRepository->findByIdentifier(1);
        $newsletter = $this->newsletterRepository->findByIdentifier(1);
        $topic = $this->topicRepository->findByIdentifier(1);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
        /** @var \RKW\RkwNewsletter\Helper\Issue $issueHelper */
        $issueHelper = $objectManager->get('RKW\\RkwNewsletter\\Helper\\Issue');
        $issueHelper->setIssue($issue);
        $issueHelper->setNewsletter($newsletter);
        $issueHelper->createContainerPage($topic);

        // get the page
        $createdContainerPage = $this->pagesRepository->findByUid(4700);

        static::assertTrue($createdContainerPage->getTxRkwnewsletterExclude());
    }



    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}