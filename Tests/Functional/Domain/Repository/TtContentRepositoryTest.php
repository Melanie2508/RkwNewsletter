<?php
namespace RKW\RkwNewsletter\Tests\Functional\Domain\Repository;


use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use RKW\RkwNewsletter\Domain\Repository\TtContentRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use RKW\RkwNewsletter\Domain\Model\Authors;
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
 * QueueMailRepositoryTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class TtContentRepositoryTest extends FunctionalTestCase
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
     * @var \RKW\RkwNewsletter\Domain\Repository\TtcontentRepository
     */
    private $subject = null;
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
        $this->importDataSet(__DIR__ . '/Fixtures/Database/TtContentRepository/Pages.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/Database/TtContentRepository/TtContent.xml');


        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_registration/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailer/Configuration/TypoScript/setup.txt',
                'EXT:rkw_authors/Configuration/TypoScript/setup.txt',
                'EXT:rkw_newsletter/Configuration/TypoScript/setup.txt',
                'EXT:rkw_newsletter/Tests/Functional/Utility/Fixtures/Frontend/Configuration/Rootpage.typoscript',
            ]
        );
        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->subject = $this->objectManager->get(TtContentRepository::class);
    }


    /**
     * @test
     */
    public function findFirstWithHeaderByPidGivenPidReturnsOneContentOfGivenPageWithDefaultLanguageUid()
    {

        /** @var \RKW\RkwNewsletter\Domain\Model\TtContent $result */
        $result = $this->subject->findFirstWithHeaderByPid(1);

        self::assertInstanceOf('\RKW\RkwNewsletter\Domain\Model\TtContent', $result);
        self::assertEquals(1, $result->getPid());
        self::assertEquals(0, $result->getSysLanguageUid());

    }

    /**
     * @test
     */
    public function findFirstWithHeaderByPidGivenPidWithNonMatchingLanguageUidReturnsNull()
    {

        /** @var \RKW\RkwNewsletter\Domain\Model\TtContent $result */
        $result = $this->subject->findFirstWithHeaderByPid(1, 1);

        self::assertNull($result);

    }

    /**
     * @test
     */
    public function findFirstWithHeaderByPidGivenPidWithMatchingLanguageUidReturnsOneContentOfGivenPageWithMatchingLanguageUid()
    {

        /** @var \RKW\RkwNewsletter\Domain\Model\TtContent $result */
        $result = $this->subject->findFirstWithHeaderByPid(2, 1);

        self::assertInstanceOf('\RKW\RkwNewsletter\Domain\Model\TtContent', $result);
        self::assertEquals(2, $result->getPid());
        self::assertEquals(1, $result->getSysLanguageUid());
    }

    /**
     * @test
     */
    public function findFirstWithHeaderByPidGivenPidReturnsOneContentWhichIsNotAnEditorialByDefault()
    {

        /** @var \RKW\RkwNewsletter\Domain\Model\TtContent $result */
        $result = $this->subject->findFirstWithHeaderByPid(3);

        self::assertInstanceOf('\RKW\RkwNewsletter\Domain\Model\TtContent', $result);
        self::assertEquals(5, $result->getUid());
        self::assertEquals(3, $result->getPid());
    }

    /**
     * @test
     */
    public function findFirstWithHeaderByPidGivenPidAndIncludeEditorialTrueReturnsOneContentWhichAnEditorial()
    {

        /** @var \RKW\RkwNewsletter\Domain\Model\TtContent $result */
        $result = $this->subject->findFirstWithHeaderByPid(3, 0, true);

        self::assertInstanceOf('\RKW\RkwNewsletter\Domain\Model\TtContent', $result);
        self::assertEquals(4, $result->getUid());
        self::assertEquals(3, $result->getPid());
    }

    /**
     * @test
     */
    public function findFirstWithHeaderByPidReturnsFirstContentWhichHasAnHeader()
    {

        /** @var \RKW\RkwNewsletter\Domain\Model\TtContent $result */
        $result = $this->subject->findFirstWithHeaderByPid(4);

        self::assertInstanceOf('\RKW\RkwNewsletter\Domain\Model\TtContent', $result);
        self::assertEquals(7, $result->getUid());
        self::assertNotEmpty($result->getHeader());
    }

    /**
     * @test
     */
    public function findFirstWithHeaderByPidReturnsOnlyContentWhichHasAnHeader()
    {

        /** @var \RKW\RkwNewsletter\Domain\Model\TtContent $result */
        $result = $this->subject->findFirstWithHeaderByPid(5);

        self::assertNull($result);
    }


    /**
     * @test
     */
    public function findFirstWithHeaderByPidReturnsFirstContentSortedByOrdering()
    {

        /** @var \RKW\RkwNewsletter\Domain\Model\TtContent $result */
        $result = $this->subject->findFirstWithHeaderByPid(6);

        self::assertInstanceOf('\RKW\RkwNewsletter\Domain\Model\TtContent', $result);
        self::assertEquals(11, $result->getUid());

    }


    /**
     * @test
     */
    public function addTextPicElement()
    {
        /**
         * Scenario:
         *
         * When a tt_content element is set with several data (matching the deprecated "add"-Function)
         * Then an instance of TtContent is created
         * Then the uid is set after persisting
         * Then the PID is still the same
         * Then a crdate is created and set by the ttContent Model constructor
         * Then the content_type is still "textpic"
         * Then the field ImageCols is still 1
         * Then the image returns count of 0
         * Then the sysLanguageUid is 0 (default)
         * Then the header is still the same string
         * Then the bodytext is still the same string
         * Then the header link is still the same string
         */

        /** @var \RKW\RkwNewsletter\Domain\Model\TtContent $ttContentElement */
        $headerText = 'My test header';
        $bodytext = 'My test bodytext';
        $headerLink = 'www.myheaderlink.de';
        $ttContentElement = GeneralUtility::makeInstance('RKW\\RkwNewsletter\\Domain\\Model\\TtContent');
        $ttContentElement->setPid(3456);
        $ttContentElement->setSysLanguageUid(0);
        $ttContentElement->setContentType('textpic');
        $ttContentElement->setImageCols(1);
        $ttContentElement->setHeader($headerText);
        $ttContentElement->setBodytext($bodytext);
        $ttContentElement->setHeaderLink($headerLink);

        /** @var \RKW\RkwNewsletter\Domain\Model\TtContent $result */
        $this->subject->add($ttContentElement);

        $this->persistenceManager->persistAll();

        self::assertInstanceOf('\RKW\RkwNewsletter\Domain\Model\TtContent', $ttContentElement);
        // means: It's successful persisted. If not, we would have logically no uid
        // (a not created uid is the main reason to write this test - a manually written add-function leads to persistent problems)
        self::assertNotEquals(0, $ttContentElement->getUid());
        self::assertEquals(3456, $ttContentElement->getPid());
        self::assertGreaterThan(0, $ttContentElement->getCrdate());
        self::assertEquals('textpic', $ttContentElement->getContentType());
        self::assertEquals(1, $ttContentElement->getImageCols());
        self::assertCount(0, $ttContentElement->getImage());
        self::assertEquals(0, $ttContentElement->getSysLanguageUid());
        self::assertEquals($headerText, $ttContentElement->getHeader());
        self::assertEquals($bodytext, $ttContentElement->getBodytext());
        self::assertEquals($headerLink, $ttContentElement->getHeaderLink());
    }


    /**
     * @test
     */
    public function addTextPicElementAddSingleAuthor()
    {
        /**
         * Scenario:
         *
         * When a tt_content element is created
         * When an author element is created
         * When this author is added to the tt_content element
         * Than a tt_content element is created
         * Than one author is set in this tt_content element
         */

        /** @var \RKW\RkwNewsletter\Domain\Model\TtContent $ttContentElement */
        $ttContentElement = GeneralUtility::makeInstance('RKW\\RkwNewsletter\\Domain\\Model\\TtContent');
        $ttContentElement->setContentType('textpic');
        $ttContentElement->setImageCols(1);

        /** @var \RKW\RkwNewsletter\Domain\Model\Authors $authorElement */
        $authorElement = GeneralUtility::makeInstance(Authors::class);
        $authorElement->setFirstName('John');
        $authorElement->setLastName('Doe');
        $ttContentElement->addTxRkwNewsletterAuthors($authorElement);

        /** @var \RKW\RkwNewsletter\Domain\Model\TtContent $result */
        $this->subject->add($ttContentElement);

        $this->persistenceManager->persistAll();

        self::assertInstanceOf('\RKW\RkwNewsletter\Domain\Model\TtContent', $ttContentElement);
        self::assertCount(1, $ttContentElement->getTxRkwNewsletterAuthors());
    }



    /**
     * @test
     */
    public function addTextPicElementSetAuthorWithinObjectStorage()
    {
        /**
         * Scenario:
         *
         * When a tt_content element is created
         * When an author element 1 is created
         * When an author element 2 is created
         * When an object storage is created
         * When both authors are added to this object storage
         * Than a tt_content element is created
         * Than both authors are set in this tt_content element
         */

        /** @var \RKW\RkwNewsletter\Domain\Model\TtContent $ttContentElement */
        $ttContentElement = GeneralUtility::makeInstance('RKW\\RkwNewsletter\\Domain\\Model\\TtContent');
        $ttContentElement->setContentType('textpic');
        $ttContentElement->setImageCols(1);

         /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
        $objectStorage = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage');

        /** @var \RKW\RkwNewsletter\Domain\Model\Authors $authorElement1 */
        $authorElement1 = GeneralUtility::makeInstance('RKW\\RkwNewsletter\\Domain\\Model\\Authors');
        $authorElement1->setFirstName('John');
        $authorElement1->setLastName('Doe');
        $objectStorage->attach($authorElement1);

        /** @var \RKW\RkwNewsletter\Domain\Model\Authors $authorElement2 */
        $authorElement2 = GeneralUtility::makeInstance('RKW\\RkwNewsletter\\Domain\\Model\\Authors');
        $authorElement2->setFirstName('Jane');
        $authorElement2->setLastName('Doe');
        $objectStorage->attach($authorElement2);

        $ttContentElement->setTxRkwNewsletterAuthors($objectStorage);

        /** @var \RKW\RkwNewsletter\Domain\Model\TtContent $result */
        $this->subject->add($ttContentElement);

        $this->persistenceManager->persistAll();

        self::assertInstanceOf('\RKW\RkwNewsletter\Domain\Model\TtContent', $ttContentElement);
        self::assertCount(2, $ttContentElement->getTxRkwNewsletterAuthors());
    }


    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}